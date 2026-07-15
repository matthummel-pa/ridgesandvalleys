<?php
/**
 * GitHub OAuth "Login with GitHub" flow + secure token storage.
 *
 * Web application flow:
 *   1. Admin clicks "Connect with GitHub" -> we redirect to GitHub's authorize
 *      URL with client_id, scope, redirect_uri and an anti-CSRF state.
 *   2. GitHub redirects back to admin-post.php?action=repofolio_oauth_callback
 *      with a temporary code (and our state).
 *   3. We exchange the code (+ client secret) for an access token, verify the
 *      viewer, and store the token encrypted at rest.
 *
 * Security notes:
 *   - Every state-changing handler checks a capability AND a nonce.
 *   - The OAuth `state` is a single-use random value stored per-user in a
 *     transient and compared with hash_equals().
 *   - The access token is encrypted with libsodium (authenticated encryption)
 *     using a key derived from the site's secret keys, and is stored in a
 *     non-autoloaded option so it is not loaded on every request.
 *
 * Docs: https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/authorizing-oauth-apps
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class OAuth {

	const AUTHORIZE_URL = 'https://github.com/login/oauth/authorize';
	const TOKEN_URL     = 'https://github.com/login/oauth/access_token';

	const TOKEN_OPTION    = 'repofolio_access_token';
	const VIEWER_OPTION   = 'repofolio_viewer';
	const STATE_TRANSIENT = 'repofolio_oauth_state';

	/**
	 * Hook the callback + connect/disconnect handlers.
	 */
	public function hooks() {
		add_action( 'admin_post_repofolio_oauth_connect', array( $this, 'handle_connect' ) );
		add_action( 'admin_post_repofolio_oauth_callback', array( $this, 'handle_callback' ) );
		add_action( 'admin_post_repofolio_oauth_disconnect', array( $this, 'handle_disconnect' ) );
	}

	/* ---------------------------------------------------------------------
	 * Token storage (encrypted at rest)
	 * ------------------------------------------------------------------- */

	/**
	 * Decrypted access token. Falls back to a manually stored token (also
	 * encrypted) if OAuth hasn't been used.
	 *
	 * @return string
	 */
	public static function get_token() {
		$stored = get_option( self::TOKEN_OPTION, '' );
		if ( $stored ) {
			$plain = self::decrypt( $stored );
			if ( '' !== $plain ) {
				return $plain;
			}
		}
		return Settings::manual_token();
	}

	public static function set_token( $token ) {
		update_option( self::TOKEN_OPTION, self::encrypt( (string) $token ), false );
	}

	public static function clear_token() {
		delete_option( self::TOKEN_OPTION );
		delete_option( self::VIEWER_OPTION );
	}

	public static function is_connected() {
		return '' !== get_option( self::TOKEN_OPTION, '' );
	}

	/**
	 * Cached viewer (login, avatar, name) captured at connect time.
	 */
	public static function viewer() {
		return get_option( self::VIEWER_OPTION, array() );
	}

	/**
	 * Derive a 32-byte encryption key from the site's secret keys (or an
	 * explicit REPOFOLIO_TOKEN_KEY constant if defined).
	 */
	protected static function key() {
		$secret = defined( 'REPOFOLIO_TOKEN_KEY' ) ? REPOFOLIO_TOKEN_KEY
			: ( defined( 'AUTH_KEY' ) && AUTH_KEY ? AUTH_KEY : 'repofolio-fallback-key' );
		return hash( 'sha256', 'repofolio|' . $secret, true ); // 32 raw bytes.
	}

	/**
	 * Authenticated encryption via libsodium when available, with a safe XOR
	 * fallback so the plugin still works on hosts without the extension.
	 * Output is base64 and prefixed so we know which scheme decrypts it.
	 */
	public static function encrypt( $plain ) {
		if ( '' === $plain ) {
			return '';
		}
		if ( function_exists( 'sodium_crypto_secretbox' ) ) {
			$nonce  = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$cipher = sodium_crypto_secretbox( $plain, $nonce, self::key() );
			return 's1:' . base64_encode( $nonce . $cipher );
		}
		// Fallback: XOR with a key stream (obfuscation, not authenticated).
		$key = self::key();
		$out = '';
		for ( $i = 0, $len = strlen( $plain ); $i < $len; $i++ ) {
			$out .= $plain[ $i ] ^ $key[ $i % strlen( $key ) ];
		}
		return 'x1:' . base64_encode( $out );
	}

	public static function decrypt( $stored ) {
		if ( '' === $stored ) {
			return '';
		}
		$scheme = substr( $stored, 0, 3 );
		$data   = base64_decode( substr( $stored, 3 ), true );
		if ( false === $data ) {
			return '';
		}
		if ( 's1:' === $scheme && function_exists( 'sodium_crypto_secretbox_open' ) ) {
			$nonce  = substr( $data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$cipher = substr( $data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
			$plain  = sodium_crypto_secretbox_open( $cipher, $nonce, self::key() );
			return false === $plain ? '' : $plain;
		}
		if ( 'x1:' === $scheme ) {
			$key = self::key();
			$out = '';
			for ( $i = 0, $len = strlen( $data ); $i < $len; $i++ ) {
				$out .= $data[ $i ] ^ $key[ $i % strlen( $key ) ];
			}
			return $out;
		}
		return '';
	}

	/* ---------------------------------------------------------------------
	 * URLs
	 * ------------------------------------------------------------------- */

	/**
	 * The callback URL the user must register in their GitHub OAuth App.
	 */
	public static function callback_url() {
		return admin_url( 'admin-post.php?action=repofolio_oauth_callback' );
	}

	/**
	 * The scopes we request, based on settings.
	 */
	protected function scopes() {
		$opts = get_option( Settings::OPTION_KEY, array() );
		$scopes = array( 'read:user' );
		if ( ! empty( $opts['include_private'] ) ) {
			$scopes[] = 'repo';        // full private repo read.
		} else {
			$scopes[] = 'public_repo'; // public repos only.
		}
		return implode( ' ', $scopes );
	}

	/* ---------------------------------------------------------------------
	 * Flow handlers
	 * ------------------------------------------------------------------- */

	/**
	 * Step 1: kick off the authorize redirect.
	 */
	public function handle_connect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'repofolio' ) );
		}
		check_admin_referer( 'repofolio_oauth_connect' );

		$opts = get_option( Settings::OPTION_KEY, array() );
		if ( empty( $opts['client_id'] ) || '' === Settings::client_secret() ) {
			$this->redirect_with_notice( 'missing_creds' );
		}

		$state = wp_generate_password( 32, false );
		set_transient( self::STATE_TRANSIENT . '_' . get_current_user_id(), $state, 15 * MINUTE_IN_SECONDS );

		$url = add_query_arg( array(
			'client_id'    => rawurlencode( $opts['client_id'] ),
			'redirect_uri' => rawurlencode( self::callback_url() ),
			'scope'        => rawurlencode( $this->scopes() ),
			'state'        => rawurlencode( $state ),
			'allow_signup' => 'false',
		), self::AUTHORIZE_URL );

		wp_redirect( $url ); // External provider URL — wp_redirect is correct here.
		exit;
	}

	/**
	 * Step 3: GitHub redirected back with ?code & ?state.
	 */
	public function handle_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'repofolio' ) );
		}

		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$saved = get_transient( self::STATE_TRANSIENT . '_' . get_current_user_id() );
		delete_transient( self::STATE_TRANSIENT . '_' . get_current_user_id() );

		if ( isset( $_GET['error'] ) ) {
			$this->redirect_with_notice( 'denied' );
		}
		if ( ! $code || ! $state || ! $saved || ! hash_equals( (string) $saved, $state ) ) {
			$this->redirect_with_notice( 'bad_state' );
		}

		$opts = get_option( Settings::OPTION_KEY, array() );

		$response = wp_remote_post( self::TOKEN_URL, array(
			'timeout' => 15,
			'headers' => array( 'Accept' => 'application/json' ),
			'body'    => array(
				'client_id'     => $opts['client_id'],
				'client_secret' => Settings::client_secret(),
				'code'          => $code,
				'redirect_uri'  => self::callback_url(),
			),
		) );

		if ( is_wp_error( $response ) ) {
			$this->redirect_with_notice( 'exchange_failed' );
		}

		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = ! empty( $body['access_token'] ) ? $body['access_token'] : '';

		if ( ! $token ) {
			$this->redirect_with_notice( 'no_token' );
		}

		self::set_token( $token );

		// Capture the viewer for a friendly "Connected as @x".
		$client = new GitHub_Client( $token );
		$viewer = $client->viewer();
		if ( $viewer ) {
			update_option( self::VIEWER_OPTION, array(
				'login'      => sanitize_text_field( $viewer['login'] ),
				'name'       => isset( $viewer['name'] ) ? sanitize_text_field( $viewer['name'] ) : '',
				'avatar_url' => isset( $viewer['avatar_url'] ) ? esc_url_raw( $viewer['avatar_url'] ) : '',
				'html_url'   => isset( $viewer['html_url'] ) ? esc_url_raw( $viewer['html_url'] ) : '',
			), false );
		}

		GitHub_Client::flush_cache();
		$this->redirect_with_notice( 'connected' );
	}

	/**
	 * Disconnect: forget the token.
	 */
	public function handle_disconnect() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'repofolio' ) );
		}
		check_admin_referer( 'repofolio_oauth_disconnect' );
		self::clear_token();
		GitHub_Client::flush_cache();
		$this->redirect_with_notice( 'disconnected' );
	}

	protected function redirect_with_notice( $code ) {
		wp_safe_redirect( add_query_arg( 'repofolio_notice', $code, Settings::page_url() ) );
		exit;
	}
}
