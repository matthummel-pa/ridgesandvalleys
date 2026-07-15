<?php
/**
 * Settings screen + option schema.
 *
 * One options array, one admin page (Settings -> Repofolio). Handles the
 * OAuth credentials, the connect/disconnect UI, the default data source, and the
 * per-feature display toggles that both the block and the renderer read.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class Settings {

	const OPTION_KEY = 'repofolio_options';
	const MENU_SLUG  = 'repofolio-settings';

	/**
	 * Default option values. Every display feature defaults to on so a fresh
	 * install shows a rich card; users trim from there.
	 */
	public static function default_options() {
		$features = array();
		foreach ( array_keys( repofolio_features() ) as $key ) {
			$features[ $key ] = in_array( $key, array( 'size', 'created', 'default_branch', 'watchers' ), true ) ? 0 : 1;
		}

		return array(
			'client_id'       => '',
			'client_secret'   => '',
			'include_private' => 0,
			'manual_token'    => '',
			// Default data source for blocks that don't override it.
			'source'          => 'authenticated',
			'source_login'    => '',
			'sort'            => 'updated',
			'direction'       => 'desc',
			'per_page'        => 9,
			'columns'         => 3,
			'exclude_forks'   => 1,
			'exclude_archived'=> 1,
			'features'        => $features,
		);
	}

	public static function get() {
		return wp_parse_args( get_option( self::OPTION_KEY, array() ), self::default_options() );
	}

	public static function page_url() {
		// Theme-addon mode: settings live on the consolidated Pressroot
		// settings page (Appearance -> Pressroot -> GitHub tab) instead of a
		// standalone options page. All OAuth/flush redirects follow this URL.
		if ( defined( 'REPOFOLIO_THEME_MODE' ) && REPOFOLIO_THEME_MODE && function_exists( '\\App\\prt_settings_tab_url' ) ) {
			return \App\prt_settings_tab_url( 'github' );
		}
		return admin_url( 'options-general.php?page=' . self::MENU_SLUG );
	}

	/**
	 * Decrypted OAuth App client secret (stored encrypted at rest).
	 */
	public static function client_secret() {
		$o = self::get();
		return ! empty( $o['client_secret'] ) ? OAuth::decrypt( $o['client_secret'] ) : '';
	}

	/**
	 * Decrypted manual fallback token (stored encrypted at rest).
	 */
	public static function manual_token() {
		$o = self::get();
		return ! empty( $o['manual_token'] ) ? OAuth::decrypt( $o['manual_token'] ) : '';
	}

	public static function has_client_secret() {
		$o = self::get();
		return ! empty( $o['client_secret'] );
	}

	public static function has_manual_token() {
		$o = self::get();
		return ! empty( $o['manual_token'] );
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_post_repofolio_flush_cache', array( $this, 'handle_flush' ) );
		if ( ! ( defined( 'REPOFOLIO_THEME_MODE' ) && REPOFOLIO_THEME_MODE ) ) {
			// Standalone plugin only: its own Settings page + plugins-list link.
			add_action( 'admin_menu', array( $this, 'menu' ) );
			add_filter( 'plugin_action_links_' . REPOFOLIO_BASENAME, array( $this, 'action_link' ) );
		}
	}

	public function action_link( $links ) {
		$links[] = '<a href="' . esc_url( self::page_url() ) . '">' . esc_html__( 'Settings', 'repofolio' ) . '</a>';
		return $links;
	}

	public function menu() {
		add_options_page(
			__( 'Repofolio', 'repofolio' ),
			__( 'Repofolio', 'repofolio' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render' )
		);
	}

	public function register() {
		register_setting( 'repofolio_group', self::OPTION_KEY, array( $this, 'sanitize' ) );
	}

	/**
	 * Sanitize the whole option array.
	 */
	public function sanitize( $input ) {
		$out = self::default_options();
		$input = is_array( $input ) ? $input : array();
		$existing = get_option( self::OPTION_KEY, array() );

		$out['client_id'] = isset( $input['client_id'] ) ? sanitize_text_field( $input['client_id'] ) : '';

		// Secrets are encrypted at rest and never re-displayed. A blank submit
		// preserves the stored value (so saving the form doesn't wipe them).
		$secret_in = isset( $input['client_secret'] ) ? trim( (string) $input['client_secret'] ) : '';
		if ( '' !== $secret_in ) {
			$out['client_secret'] = OAuth::encrypt( sanitize_text_field( $secret_in ) );
		} else {
			$out['client_secret'] = isset( $existing['client_secret'] ) ? $existing['client_secret'] : '';
		}

		$token_in = isset( $input['manual_token'] ) ? trim( (string) $input['manual_token'] ) : '';
		if ( '' !== $token_in ) {
			$out['manual_token'] = OAuth::encrypt( sanitize_text_field( $token_in ) );
		} else {
			$out['manual_token'] = isset( $existing['manual_token'] ) ? $existing['manual_token'] : '';
		}

		$out['include_private'] = empty( $input['include_private'] ) ? 0 : 1;

		$out['source']       = in_array( $input['source'] ?? '', array( 'authenticated', 'user', 'org' ), true ) ? $input['source'] : 'authenticated';
		$out['source_login'] = isset( $input['source_login'] ) ? sanitize_text_field( $input['source_login'] ) : '';
		$out['sort']         = in_array( $input['sort'] ?? '', array( 'updated', 'created', 'pushed', 'full_name', 'stars' ), true ) ? $input['sort'] : 'updated';
		$out['direction']    = 'asc' === ( $input['direction'] ?? '' ) ? 'asc' : 'desc';
		$out['per_page']     = min( 100, max( 1, (int) ( $input['per_page'] ?? 9 ) ) );
		$out['columns']      = min( 4, max( 1, (int) ( $input['columns'] ?? 3 ) ) );
		$out['exclude_forks']    = empty( $input['exclude_forks'] ) ? 0 : 1;
		$out['exclude_archived'] = empty( $input['exclude_archived'] ) ? 0 : 1;

		$out['features'] = array();
		foreach ( array_keys( repofolio_features() ) as $key ) {
			$out['features'][ $key ] = empty( $input['features'][ $key ] ) ? 0 : 1;
		}

		return $out;
	}

	public function handle_flush() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Nope.', 'repofolio' ) );
		}
		check_admin_referer( 'repofolio_flush_cache' );
		GitHub_Client::flush_cache();
		wp_safe_redirect( add_query_arg( 'repofolio_notice', 'flushed', self::page_url() ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Render
	 * ------------------------------------------------------------------- */

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o         = self::get();
		$connected = OAuth::is_connected();
		$viewer    = OAuth::viewer();

		$this->notice();
		$theme_mode = defined( 'REPOFOLIO_THEME_MODE' ) && REPOFOLIO_THEME_MODE;
		?>
		<div class="<?php echo $theme_mode ? 'repofolio-settings' : 'wrap repofolio-settings'; ?>">
			<?php if ( $theme_mode ) : ?>
				<h2 style="margin-top:0"><?php esc_html_e( 'GitHub', 'repofolio' ); ?></h2>
			<?php else : ?>
				<h1><?php esc_html_e( 'Repofolio', 'repofolio' ); ?></h1>
			<?php endif; ?>
			<p class="description"><?php esc_html_e( 'Connect your GitHub account, then choose what repo data appears on your site. Drop the "GitHub Repo Grid" block on any page to display live repositories.', 'repofolio' ); ?></p>

			<div class="repofolio-card">
				<h2><?php esc_html_e( '1. Connect with GitHub', 'repofolio' ); ?></h2>
				<?php if ( $connected ) : ?>
					<div class="repofolio-connected">
						<?php if ( ! empty( $viewer['avatar_url'] ) ) : ?>
							<img src="<?php echo esc_url( $viewer['avatar_url'] ); ?>" alt="" width="48" height="48" />
						<?php endif; ?>
						<div>
							<strong><?php esc_html_e( 'Connected', 'repofolio' ); ?></strong>
							<?php if ( ! empty( $viewer['login'] ) ) : ?>
								<span>as <a href="<?php echo esc_url( $viewer['html_url'] ); ?>" target="_blank" rel="noopener">@<?php echo esc_html( $viewer['login'] ); ?></a></span>
							<?php endif; ?>
							<?php $this->rate_line(); ?>
						</div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="repofolio_oauth_disconnect" />
							<?php wp_nonce_field( 'repofolio_oauth_disconnect' ); ?>
							<button type="submit" class="button"><?php esc_html_e( 'Disconnect', 'repofolio' ); ?></button>
						</form>
					</div>
				<?php else : ?>
					<p><?php
						printf(
							/* translators: %s: GitHub developer settings URL. */
							wp_kses_post( __( 'Create an <a href="%s" target="_blank" rel="noopener">OAuth App</a> on GitHub, then paste its Client ID and Secret below. Set the app\'s <strong>Authorization callback URL</strong> to the value shown.', 'repofolio' ) ),
							'https://github.com/settings/developers'
						);
					?></p>
					<p class="repofolio-callback">
						<label><?php esc_html_e( 'Authorization callback URL (copy into your GitHub OAuth App):', 'repofolio' ); ?></label>
						<code><?php echo esc_html( OAuth::callback_url() ); ?></code>
					</p>
				<?php endif; ?>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'repofolio_group' ); ?>

				<div class="repofolio-card">
					<h2><?php esc_html_e( 'OAuth App credentials', 'repofolio' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="repofolio_client_id"><?php esc_html_e( 'Client ID', 'repofolio' ); ?></label></th>
							<td><input type="text" id="repofolio_client_id" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[client_id]" value="<?php echo esc_attr( $o['client_id'] ); ?>" autocomplete="off" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="repofolio_client_secret"><?php esc_html_e( 'Client Secret', 'repofolio' ); ?></label></th>
							<td>
								<input type="password" id="repofolio_client_secret" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[client_secret]" value="" autocomplete="new-password" placeholder="<?php echo self::has_client_secret() ? esc_attr( str_repeat( "\xE2\x80\xA2", 12 ) ) : ''; ?>" />
								<p class="description">
									<?php esc_html_e( 'Encrypted in your database and never shown again.', 'repofolio' ); ?>
									<?php if ( self::has_client_secret() ) { echo ' ' . esc_html__( 'Leave blank to keep the saved secret.', 'repofolio' ); } ?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Private repos', 'repofolio' ); ?></th>
							<td><label><input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[include_private]" value="1" <?php checked( $o['include_private'] ); ?> /> <?php esc_html_e( 'Request access to private repositories (adds the "repo" scope at connect time)', 'repofolio' ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><label for="repofolio_manual_token"><?php esc_html_e( 'Manual token (optional)', 'repofolio' ); ?></label></th>
							<td>
								<input type="password" id="repofolio_manual_token" class="regular-text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[manual_token]" value="" autocomplete="new-password" placeholder="<?php echo self::has_manual_token() ? esc_attr( str_repeat( "\xE2\x80\xA2", 12 ) ) : ''; ?>" />
								<p class="description">
									<?php esc_html_e( 'Fallback Personal Access Token, encrypted at rest and used only if you have not connected via OAuth above.', 'repofolio' ); ?>
									<?php if ( self::has_manual_token() ) { echo ' ' . esc_html__( 'Leave blank to keep the saved token.', 'repofolio' ); } ?>
								</p>
							</td>
						</tr>
					</table>
					<?php if ( ! $connected && $o['client_id'] && self::has_client_secret() ) : ?>
						<p>
							<a class="button button-primary button-hero repofolio-connect" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=repofolio_oauth_connect' ), 'repofolio_oauth_connect' ) ); ?>">
								<span class="dashicons dashicons-admin-network"></span> <?php esc_html_e( 'Connect with GitHub', 'repofolio' ); ?>
							</a>
							<span class="description"><?php esc_html_e( 'Save credentials first, then connect.', 'repofolio' ); ?></span>
						</p>
					<?php endif; ?>
				</div>

				<div class="repofolio-card">
					<h2><?php esc_html_e( '2. Default data source', 'repofolio' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Which repositories', 'repofolio' ); ?></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[source]">
									<option value="authenticated" <?php selected( $o['source'], 'authenticated' ); ?>><?php esc_html_e( 'My connected account', 'repofolio' ); ?></option>
									<option value="user" <?php selected( $o['source'], 'user' ); ?>><?php esc_html_e( 'A specific user', 'repofolio' ); ?></option>
									<option value="org" <?php selected( $o['source'], 'org' ); ?>><?php esc_html_e( 'An organization', 'repofolio' ); ?></option>
								</select>
								<input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[source_login]" value="<?php echo esc_attr( $o['source_login'] ); ?>" placeholder="<?php esc_attr_e( 'user or org login', 'repofolio' ); ?>" class="regular-text" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Sort by', 'repofolio' ); ?></th>
							<td>
								<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[sort]">
									<?php
									$sorts = array( 'updated' => __( 'Recently updated', 'repofolio' ), 'pushed' => __( 'Recently pushed', 'repofolio' ), 'created' => __( 'Newest', 'repofolio' ), 'full_name' => __( 'Name', 'repofolio' ), 'stars' => __( 'Most stars', 'repofolio' ) );
									foreach ( $sorts as $val => $label ) {
										printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $o['sort'], $val, false ), esc_html( $label ) );
									}
									?>
								</select>
								<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[direction]">
									<option value="desc" <?php selected( $o['direction'], 'desc' ); ?>><?php esc_html_e( 'Descending', 'repofolio' ); ?></option>
									<option value="asc" <?php selected( $o['direction'], 'asc' ); ?>><?php esc_html_e( 'Ascending', 'repofolio' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'How many / columns', 'repofolio' ); ?></th>
							<td>
								<input type="number" min="1" max="100" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[per_page]" value="<?php echo esc_attr( $o['per_page'] ); ?>" class="small-text" /> <?php esc_html_e( 'repos', 'repofolio' ); ?>
								&nbsp; <input type="number" min="1" max="4" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[columns]" value="<?php echo esc_attr( $o['columns'] ); ?>" class="small-text" /> <?php esc_html_e( 'columns', 'repofolio' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Filters', 'repofolio' ); ?></th>
							<td>
								<label><input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[exclude_forks]" value="1" <?php checked( $o['exclude_forks'] ); ?> /> <?php esc_html_e( 'Hide forks', 'repofolio' ); ?></label><br />
								<label><input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[exclude_archived]" value="1" <?php checked( $o['exclude_archived'] ); ?> /> <?php esc_html_e( 'Hide archived repos', 'repofolio' ); ?></label>
							</td>
						</tr>
					</table>
				</div>

				<div class="repofolio-card">
					<h2><?php esc_html_e( '3. What to display', 'repofolio' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Toggle each piece of GitHub data. These are the defaults; individual blocks can override them.', 'repofolio' ); ?></p>
					<div class="repofolio-features">
						<?php foreach ( repofolio_features() as $key => $label ) : ?>
							<label class="repofolio-feature">
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[features][<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $o['features'][ $key ] ) ); ?> />
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<?php submit_button( __( 'Save settings', 'repofolio' ) ); ?>
			</form>

			<div class="repofolio-card">
				<h2><?php esc_html_e( 'Maintenance', 'repofolio' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="repofolio_flush_cache" />
					<?php wp_nonce_field( 'repofolio_flush_cache' ); ?>
					<button type="submit" class="button"><?php esc_html_e( 'Clear cached GitHub data', 'repofolio' ); ?></button>
					<span class="description"><?php esc_html_e( 'GitHub responses are cached for 6 hours. Clear to fetch fresh data now.', 'repofolio' ); ?></span>
				</form>
			</div>
		</div>
		<?php
	}

	protected function rate_line() {
		$client = GitHub_Client::instance();
		$rl     = $client->rate_limit();
		if ( ! empty( $rl['resources']['core'] ) ) {
			$core = $rl['resources']['core'];
			echo '<br /><span class="description">' . sprintf(
				/* translators: 1: remaining, 2: limit. */
				esc_html__( 'API rate limit: %1$s / %2$s remaining this hour.', 'repofolio' ),
				esc_html( $core['remaining'] ),
				esc_html( $core['limit'] )
			) . '</span>';
		}
	}

	protected function notice() {
		if ( empty( $_GET['repofolio_notice'] ) ) {
			return;
		}
		$code = sanitize_key( wp_unslash( $_GET['repofolio_notice'] ) );
		$map  = array(
			'connected'      => array( 'success', __( 'Connected to GitHub.', 'repofolio' ) ),
			'disconnected'   => array( 'info', __( 'Disconnected from GitHub.', 'repofolio' ) ),
			'flushed'        => array( 'success', __( 'Cached GitHub data cleared.', 'repofolio' ) ),
			'missing_creds'  => array( 'error', __( 'Add and save your Client ID and Secret first.', 'repofolio' ) ),
			'denied'         => array( 'error', __( 'Authorization was denied on GitHub.', 'repofolio' ) ),
			'bad_state'      => array( 'error', __( 'Security check failed. Please try connecting again.', 'repofolio' ) ),
			'exchange_failed'=> array( 'error', __( 'Could not reach GitHub to exchange the code.', 'repofolio' ) ),
			'no_token'       => array( 'error', __( 'GitHub did not return an access token. Check your Client Secret and callback URL.', 'repofolio' ) ),
		);
		if ( ! isset( $map[ $code ] ) ) {
			return;
		}
		printf(
			'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
			esc_attr( $map[ $code ][0] ),
			esc_html( $map[ $code ][1] )
		);
	}
}
