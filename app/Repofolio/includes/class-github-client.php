<?php
/**
 * GitHub REST API client.
 *
 * Wraps the endpoints needed to render a rich repo profile, with transient
 * caching and Bearer-token auth (token comes from the OAuth flow, or a manual
 * fallback token). Every method degrades gracefully to an empty result on error
 * so the front end never fatals because GitHub is down or rate-limited.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class GitHub_Client {

	const API_BASE   = 'https://api.github.com';
	const CACHE_TTL  = 6 * HOUR_IN_SECONDS;
	const CACHE_PREFIX = 'repofolio_';

	/**
	 * Bearer token, or empty for anonymous (rate-limited) requests.
	 *
	 * @var string
	 */
	protected $token = '';

	public function __construct( $token = '' ) {
		$this->token = (string) $token;
	}

	/**
	 * Factory using the stored OAuth/manual token.
	 */
	public static function instance() {
		return new self( OAuth::get_token() );
	}

	/* ---------------------------------------------------------------------
	 * Low-level request
	 * ------------------------------------------------------------------- */

	/**
	 * GET a GitHub API path (or absolute URL) as decoded JSON, cached.
	 *
	 * @param string $path   Path beginning with "/" or an absolute URL.
	 * @param array  $args   Query args.
	 * @param bool   $cache  Whether to cache.
	 * @param string $accept Accept header (for raw README, etc.).
	 * @return array{ok:bool,data:mixed,headers:array,status:int}
	 */
	public function get( $path, $args = array(), $cache = true, $accept = 'application/vnd.github+json' ) {
		$url = 0 === strpos( $path, 'http' ) ? $path : self::API_BASE . $path;
		if ( ! empty( $args ) ) {
			$url = add_query_arg( array_map( 'rawurlencode', $args ), $url );
		}

		$cache_key = self::CACHE_PREFIX . md5( $url . '|' . $accept . '|' . ( $this->token ? 'auth' : 'anon' ) );
		if ( $cache ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$headers = array(
			'Accept'               => $accept,
			'X-GitHub-Api-Version' => '2022-11-28',
			'User-Agent'           => 'MH-GitHub-Showcase/' . REPOFOLIO_VERSION,
		);
		if ( $this->token ) {
			$headers['Authorization'] = 'Bearer ' . $this->token;
		}

		$response = wp_remote_get( $url, array(
			'timeout' => 15,
			'headers' => $headers,
		) );

		if ( is_wp_error( $response ) ) {
			$result = array( 'ok' => false, 'data' => null, 'headers' => array(), 'status' => 0 );
			if ( $cache ) {
				set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );
			}
			return $result;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = wp_remote_retrieve_body( $response );
		$is_raw = false !== strpos( $accept, 'raw' ) || false !== strpos( $accept, 'html' );
		$data   = $is_raw ? $body : json_decode( $body, true );

		$result = array(
			'ok'      => ( $status >= 200 && $status < 300 ),
			'data'    => $data,
			'headers' => array(
				'rate_remaining' => wp_remote_retrieve_header( $response, 'x-ratelimit-remaining' ),
				'rate_limit'     => wp_remote_retrieve_header( $response, 'x-ratelimit-limit' ),
			),
			'status'  => $status,
		);

		if ( $cache ) {
			// Cache failures briefly, successes for the full TTL.
			set_transient( $cache_key, $result, $result['ok'] ? self::CACHE_TTL : 5 * MINUTE_IN_SECONDS );
		}
		return $result;
	}

	/**
	 * Clear all of this plugin's cached API responses.
	 */
	public static function flush_cache() {
		global $wpdb;
		$like = $wpdb->esc_like( '_transient_' . self::CACHE_PREFIX ) . '%';
		$keys = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
		foreach ( (array) $keys as $key ) {
			delete_option( $key );
			delete_option( str_replace( '_transient_', '_transient_timeout_', $key ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * High-level data
	 * ------------------------------------------------------------------- */

	/**
	 * The authenticated user (or false).
	 */
	public function viewer() {
		if ( ! $this->token ) {
			return false;
		}
		$r = $this->get( '/user', array(), false );
		return $r['ok'] ? $r['data'] : false;
	}

	/**
	 * Current rate-limit status.
	 */
	public function rate_limit() {
		$r = $this->get( '/rate_limit', array(), false );
		return $r['ok'] ? $r['data'] : null;
	}

	/**
	 * List repositories for a source.
	 *
	 * @param array $opts {
	 *     @type string $source    'authenticated' | 'user' | 'org'.
	 *     @type string $login     User or org login (for user/org sources).
	 *     @type string $sort      updated|created|pushed|full_name|stars.
	 *     @type string $direction asc|desc.
	 *     @type int    $per_page  Max repos.
	 *     @type string $visibility all|public|private (authenticated only).
	 *     @type string $type      owner|member|all (authenticated only).
	 * }
	 * @return array List of repo arrays.
	 */
	public function repos( $opts = array() ) {
		$opts = wp_parse_args( $opts, array(
			'source'     => 'authenticated',
			'login'      => '',
			'sort'       => 'updated',
			'direction'  => 'desc',
			'per_page'   => 12,
			'visibility' => 'public',
			'type'       => 'owner',
		) );

		$per_page   = min( 100, max( 1, (int) $opts['per_page'] ) );
		$sort_field = 'stars' === $opts['sort'] ? 'updated' : $opts['sort']; // list endpoints don't sort by stars; we re-sort below.

		if ( 'authenticated' === $opts['source'] && $this->token ) {
			$r = $this->get( '/user/repos', array(
				'per_page'   => $per_page,
				'sort'       => $sort_field,
				'direction'  => $opts['direction'],
				'visibility' => $opts['visibility'],
				'type'       => $opts['type'],
			) );
		} elseif ( 'org' === $opts['source'] && $opts['login'] ) {
			$r = $this->get( '/orgs/' . rawurlencode( $opts['login'] ) . '/repos', array(
				'per_page'  => $per_page,
				'sort'      => $sort_field,
				'direction' => $opts['direction'],
				'type'      => 'public',
			) );
		} else { // user
			$login = $opts['login'] ?: '';
			if ( ! $login && $this->token ) {
				$viewer = $this->viewer();
				$login  = $viewer ? $viewer['login'] : '';
			}
			if ( ! $login ) {
				return array();
			}
			$r = $this->get( '/users/' . rawurlencode( $login ) . '/repos', array(
				'per_page'  => $per_page,
				'sort'      => $sort_field,
				'direction' => $opts['direction'],
				'type'      => 'owner',
			) );
		}

		$repos = ( $r['ok'] && is_array( $r['data'] ) ) ? $r['data'] : array();

		if ( 'stars' === $opts['sort'] ) {
			usort( $repos, function ( $a, $b ) {
				return (int) $b['stargazers_count'] <=> (int) $a['stargazers_count'];
			} );
		}
		return array_slice( $repos, 0, $per_page );
	}

	/**
	 * A single repository's core object.
	 */
	public function repo( $owner, $repo ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) );
		return $r['ok'] ? $r['data'] : null;
	}

	/**
	 * Language byte breakdown => [ 'PHP' => 12345, ... ].
	 */
	public function languages( $owner, $repo ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/languages' );
		return ( $r['ok'] && is_array( $r['data'] ) ) ? $r['data'] : array();
	}

	/**
	 * Topics list.
	 */
	public function topics( $owner, $repo ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/topics' );
		return ( $r['ok'] && ! empty( $r['data']['names'] ) ) ? $r['data']['names'] : array();
	}

	/**
	 * Open issues (most recent first), pull requests excluded.
	 * Added for the theme-addon build: powers the Support tab's issue list
	 * (App\Github::fetchIssues in the Pressroot theme).
	 */
	public function issues( $owner, $repo, $count = 5 ) {
		$r = $this->get(
			'/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/issues',
			array( 'state' => 'open', 'per_page' => min( 50, max( 1, (int) $count * 2 ) ) )
		);
		if ( ! $r['ok'] || ! is_array( $r['data'] ) ) {
			return array();
		}
		$out = array();
		foreach ( $r['data'] as $issue ) {
			if ( isset( $issue['pull_request'] ) ) {
				continue; // The issues endpoint also returns PRs — skip them.
			}
			$out[] = $issue;
			if ( count( $out ) >= (int) $count ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * Releases (most recent first).
	 */
	public function releases( $owner, $repo, $count = 5 ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/releases', array( 'per_page' => (int) $count ) );
		return ( $r['ok'] && is_array( $r['data'] ) ) ? $r['data'] : array();
	}

	/**
	 * Latest release, falling back to the newest tag if none is published.
	 */
	public function latest_release( $owner, $repo ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/releases/latest' );
		if ( $r['ok'] && ! empty( $r['data']['tag_name'] ) ) {
			return $r['data'];
		}
		$tags = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/tags', array( 'per_page' => 1 ) );
		if ( $tags['ok'] && ! empty( $tags['data'][0]['name'] ) ) {
			return array( 'tag_name' => $tags['data'][0]['name'], 'html_url' => '', 'body' => '', 'published_at' => '' );
		}
		return null;
	}

	/**
	 * README rendered to HTML (GitHub's own renderer).
	 */
	public function readme_html( $owner, $repo ) {
		$r = $this->get(
			'/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/readme',
			array(),
			true,
			'application/vnd.github.html+json'
		);
		return ( $r['ok'] && is_string( $r['data'] ) ) ? $r['data'] : '';
	}

	/**
	 * Contributors (login + avatar + contributions).
	 */
	public function contributors( $owner, $repo, $count = 12 ) {
		$r = $this->get( '/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/contributors', array( 'per_page' => (int) $count ) );
		return ( $r['ok'] && is_array( $r['data'] ) ) ? $r['data'] : array();
	}

	/**
	 * Assemble a full profile for one repo, pulling only the pieces enabled in
	 * $features (so we don't spend rate limit on data nobody will see).
	 *
	 * @param string $owner    Owner login.
	 * @param string $repo     Repo name.
	 * @param array  $features Map of feature => bool.
	 * @return array|null
	 */
	public function profile( $owner, $repo, $features = array() ) {
		$core = $this->repo( $owner, $repo );
		if ( ! $core ) {
			return null;
		}
		$profile = array( 'repo' => $core );

		if ( ! empty( $features['languages'] ) ) {
			$profile['languages'] = $this->languages( $owner, $repo );
		}
		if ( ! empty( $features['topics'] ) && empty( $core['topics'] ) ) {
			$profile['topics'] = $this->topics( $owner, $repo );
		}
		if ( ! empty( $features['release'] ) ) {
			$profile['release'] = $this->latest_release( $owner, $repo );
		}
		if ( ! empty( $features['readme'] ) ) {
			$profile['readme'] = $this->readme_html( $owner, $repo );
		}
		if ( ! empty( $features['contributors'] ) ) {
			$profile['contributors'] = $this->contributors( $owner, $repo );
		}
		return $profile;
	}
}
