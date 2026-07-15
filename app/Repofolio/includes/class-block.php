<?php
/**
 * The "Repofolio Repo Grid" block: a live, server-rendered grid of GitHub repos.
 *
 * Server-rendered so it can use the stored OAuth token and the 6-hour cache
 * without leaking anything to the browser. The editor preview uses
 * @wordpress/server-side-render, so the editor shows exactly what the front end
 * will render.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class Block {

	const NAME = 'repofolio/repo-grid';

	public function hooks() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ) );
	}

	/**
	 * Register the block type + its editor script.
	 */
	public function register() {
		wp_register_style(
			'repofolio-front',
			REPOFOLIO_URL . 'assets/css/repofolio.css',
			array(),
			REPOFOLIO_VERSION
		);

		wp_register_script(
			'repofolio-block-editor',
			REPOFOLIO_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			REPOFOLIO_VERSION,
			true
		);

		// Expose the feature list + defaults to the editor UI.
		wp_localize_script( 'repofolio-block-editor', 'REPOFOLIO_BLOCK', array(
			'features' => repofolio_features(),
			'defaults' => Settings::get(),
		) );

		register_block_type( self::NAME, array(
			'api_version'     => 3,
			'title'           => __( 'Repofolio Repo Grid', 'repofolio' ),
			'description'     => __( 'A live grid of GitHub repositories with the details you choose.', 'repofolio' ),
			'category'        => 'widgets',
			'icon'            => 'grid-view',
			'keywords'        => array( 'github', 'repo', 'repository', 'portfolio', 'repofolio' ),
			'editor_script'   => 'repofolio-block-editor',
			'style'           => 'repofolio-front',
			'render_callback' => array( $this, 'render' ),
			'attributes'      => $this->attributes(),
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
				'html'  => false,
			),
		) );
	}

	public function front_styles() {
		// Ensures the style is available even when the block is rendered late.
		wp_enqueue_style( 'repofolio-front' );
	}

	/**
	 * Block attributes. Empty string / -1 means "inherit from settings".
	 */
	protected function attributes() {
		$attrs = array(
			'source'       => array( 'type' => 'string', 'default' => '' ),
			'sourceLogin'  => array( 'type' => 'string', 'default' => '' ),
			'sort'         => array( 'type' => 'string', 'default' => '' ),
			'direction'    => array( 'type' => 'string', 'default' => '' ),
			'perPage'      => array( 'type' => 'number', 'default' => 0 ),
			'columns'      => array( 'type' => 'number', 'default' => 0 ),
			'excludeForks'    => array( 'type' => 'string', 'default' => 'inherit' ),
			'excludeArchived' => array( 'type' => 'string', 'default' => 'inherit' ),
			'overrideFeatures' => array( 'type' => 'boolean', 'default' => false ),
		);
		foreach ( array_keys( repofolio_features() ) as $key ) {
			$attrs[ 'feature_' . $key ] = array( 'type' => 'boolean', 'default' => true );
		}
		return $attrs;
	}

	/**
	 * Resolve the effective options for a given block instance (block overrides
	 * fall back to the saved settings).
	 */
	protected function resolve( $atts ) {
		$s = Settings::get();

		$source    = $atts['source'] ?: $s['source'];
		$login     = $atts['sourceLogin'] ?: $s['source_login'];
		$sort      = $atts['sort'] ?: $s['sort'];
		$direction = $atts['direction'] ?: $s['direction'];
		$per_page  = ! empty( $atts['perPage'] ) ? (int) $atts['perPage'] : (int) $s['per_page'];
		$columns   = ! empty( $atts['columns'] ) ? (int) $atts['columns'] : (int) $s['columns'];

		$exclude_forks = 'inherit' === $atts['excludeForks'] ? (bool) $s['exclude_forks'] : ( 'yes' === $atts['excludeForks'] );
		$exclude_arch  = 'inherit' === $atts['excludeArchived'] ? (bool) $s['exclude_archived'] : ( 'yes' === $atts['excludeArchived'] );

		if ( ! empty( $atts['overrideFeatures'] ) ) {
			$features = array();
			foreach ( array_keys( repofolio_features() ) as $key ) {
				$features[ $key ] = ! empty( $atts[ 'feature_' . $key ] );
			}
		} else {
			$features = $s['features'];
		}

		return compact( 'source', 'login', 'sort', 'direction', 'per_page', 'columns', 'exclude_forks', 'exclude_arch', 'features' );
	}

	/**
	 * Server render.
	 */
	public function render( $atts, $content = '', $block = null ) {
		$atts = wp_parse_args( $atts, wp_list_pluck( $this->attributes(), 'default' ) );
		$cfg  = $this->resolve( $atts );

		$client = GitHub_Client::instance();
		$repos  = $client->repos( array(
			'source'    => $cfg['source'],
			'login'     => $cfg['login'],
			'sort'      => $cfg['sort'],
			'direction' => $cfg['direction'],
			'per_page'  => $cfg['per_page'] + 20, // over-fetch so filtering still fills the grid.
		) );

		// Filter forks / archived.
		$repos = array_filter( $repos, function ( $r ) use ( $cfg ) {
			if ( $cfg['exclude_forks'] && ! empty( $r['fork'] ) ) {
				return false;
			}
			if ( $cfg['exclude_arch'] && ! empty( $r['archived'] ) ) {
				return false;
			}
			return true;
		} );
		$repos = array_slice( array_values( $repos ), 0, $cfg['per_page'] );

		$wrapper = function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes( array( 'class' => 'repofolio-grid cols-' . (int) $cfg['columns'] ) )
			: 'class="repofolio-grid cols-' . (int) $cfg['columns'] . '"';

		if ( empty( $repos ) ) {
			return '<div ' . $wrapper . '><p class="repofolio-empty">' . esc_html__( 'No repositories to show yet. Connect GitHub in Settings → Repofolio.', 'repofolio' ) . '</p></div>';
		}

		ob_start();
		echo '<div ' . $wrapper . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
		foreach ( $repos as $repo ) {
			echo $this->card( $repo, $cfg['features'], $client ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Render one repo card honoring the feature toggles.
	 */
	protected function card( $repo, $features, $client ) {
		$owner_login = isset( $repo['owner']['login'] ) ? $repo['owner']['login'] : '';
		$name        = isset( $repo['name'] ) ? $repo['name'] : '';
		$url         = isset( $repo['html_url'] ) ? $repo['html_url'] : '#';

		ob_start();
		?>
		<article class="repofolio-card">
			<header class="repofolio-card__head">
				<?php if ( ! empty( $features['owner'] ) && ! empty( $repo['owner']['avatar_url'] ) ) : ?>
					<img class="repofolio-avatar" src="<?php echo esc_url( $repo['owner']['avatar_url'] ); ?>" alt="" width="28" height="28" loading="lazy" />
				<?php endif; ?>
				<h3 class="repofolio-card__title">
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $name ); ?>
					</a>
				</h3>
				<?php if ( ! empty( $repo['fork'] ) ) : ?>
					<span class="repofolio-badge">fork</span>
				<?php endif; ?>
			</header>

			<?php if ( ! empty( $features['description'] ) && ! empty( $repo['description'] ) ) : ?>
				<p class="repofolio-card__desc"><?php echo esc_html( $repo['description'] ); ?></p>
			<?php endif; ?>

			<?php
			// Topics.
			$topics = ! empty( $repo['topics'] ) ? $repo['topics'] : array();
			if ( ! empty( $features['topics'] ) && $topics ) :
				?>
				<div class="repofolio-topics">
					<?php foreach ( array_slice( $topics, 0, 8 ) as $topic ) : ?>
						<span class="repofolio-topic"><?php echo esc_html( $topic ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// Language breakdown bar (needs an extra API call; only when enabled).
			if ( ! empty( $features['languages'] ) && $owner_login && $name ) :
				$langs = $client->languages( $owner_login, $name );
				$total = array_sum( $langs );
				if ( $total > 0 ) :
					?>
					<div class="repofolio-langbar" role="img" aria-label="<?php esc_attr_e( 'Language breakdown', 'repofolio' ); ?>">
						<?php foreach ( $langs as $lang => $bytes ) :
							$pct = round( ( $bytes / $total ) * 100, 1 );
							if ( $pct < 1 ) { continue; }
							?>
							<span class="repofolio-langseg" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( repofolio_lang_color( $lang ) ); ?>" title="<?php echo esc_attr( $lang . ' ' . $pct . '%' ); ?>"></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<footer class="repofolio-meta">
				<?php if ( ! empty( $features['language'] ) && ! empty( $repo['language'] ) ) : ?>
					<span class="repofolio-metaitem"><span class="repofolio-dot" style="background:<?php echo esc_attr( repofolio_lang_color( $repo['language'] ) ); ?>"></span><?php echo esc_html( $repo['language'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['stars'] ) ) : ?>
					<span class="repofolio-metaitem" title="<?php esc_attr_e( 'Stars', 'repofolio' ); ?>">&#9733; <?php echo esc_html( repofolio_compact( $repo['stargazers_count'] ?? 0 ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['forks'] ) ) : ?>
					<span class="repofolio-metaitem" title="<?php esc_attr_e( 'Forks', 'repofolio' ); ?>">&#8916; <?php echo esc_html( repofolio_compact( $repo['forks_count'] ?? 0 ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['watchers'] ) ) : ?>
					<span class="repofolio-metaitem" title="<?php esc_attr_e( 'Watchers', 'repofolio' ); ?>">&#128065; <?php echo esc_html( repofolio_compact( $repo['subscribers_count'] ?? $repo['watchers_count'] ?? 0 ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['issues'] ) ) : ?>
					<span class="repofolio-metaitem" title="<?php esc_attr_e( 'Open issues', 'repofolio' ); ?>">&#9737; <?php echo esc_html( repofolio_compact( $repo['open_issues_count'] ?? 0 ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['size'] ) && isset( $repo['size'] ) ) : ?>
					<span class="repofolio-metaitem"><?php echo esc_html( repofolio_format_size( $repo['size'] ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['default_branch'] ) && ! empty( $repo['default_branch'] ) ) : ?>
					<span class="repofolio-metaitem repofolio-branch"><?php echo esc_html( $repo['default_branch'] ); ?></span>
				<?php endif; ?>
			</footer>

			<div class="repofolio-sub">
				<?php if ( ! empty( $features['release'] ) && $owner_login && $name ) :
					$rel = $client->latest_release( $owner_login, $name );
					if ( $rel && ! empty( $rel['tag_name'] ) ) : ?>
						<span class="repofolio-tag"><?php echo esc_html( $rel['tag_name'] ); ?></span>
					<?php endif;
				endif; ?>
				<?php if ( ! empty( $features['license'] ) && ! empty( $repo['license']['spdx_id'] ) && 'NOASSERTION' !== $repo['license']['spdx_id'] ) : ?>
					<span class="repofolio-license"><?php echo esc_html( $repo['license']['spdx_id'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['created'] ) && ! empty( $repo['created_at'] ) ) : ?>
					<span class="repofolio-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $repo['created_at'] ) ) ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $features['updated'] ) && ! empty( $repo['pushed_at'] ) ) : ?>
					<span class="repofolio-updated"><?php echo esc_html( repofolio_time_ago( $repo['pushed_at'] ) ); ?></span>
				<?php endif; ?>
			</div>

			<div class="repofolio-links">
				<a class="repofolio-link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View repo', 'repofolio' ); ?> &#8599;</a>
				<?php if ( ! empty( $features['homepage'] ) && ! empty( $repo['homepage'] ) ) : ?>
					<a class="repofolio-link repofolio-link--demo" href="<?php echo esc_url( $repo['homepage'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live', 'repofolio' ); ?> &#8599;</a>
				<?php endif; ?>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}
}
