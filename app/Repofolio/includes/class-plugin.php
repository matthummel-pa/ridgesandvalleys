<?php
/**
 * Plugin bootstrap: wires the pieces together.
 *
 * @package Repofolio
 */

namespace Repofolio;

defined( 'ABSPATH' ) || exit;

class Plugin {

	/** @var Settings */
	public $settings;

	/** @var OAuth */
	public $oauth;

	/** @var Block */
	public $block;

	/** @var Projects */
	public $projects;

	public function boot() {
		$this->settings = new Settings();
		$this->oauth    = new OAuth();
		$this->block    = new Block();
		$this->projects = new Projects();

		$this->settings->hooks();
		$this->oauth->hooks();
		$this->block->hooks();
		$this->projects->hooks();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	public function load_textdomain() {
		if ( defined( 'REPOFOLIO_THEME_MODE' ) && REPOFOLIO_THEME_MODE ) {
			return; // Theme mode: the theme handles textdomain loading.
		}
		load_plugin_textdomain( 'repofolio', false, dirname( REPOFOLIO_BASENAME ) . '/languages' );
	}

	public function admin_assets( $hook ) {
		// Standalone: Settings -> Repofolio. Theme mode: Appearance -> Pressroot.
		$screens = array( 'settings_page_' . Settings::MENU_SLUG, 'appearance_page_prt-settings' );
		if ( ! in_array( $hook, $screens, true ) ) {
			return;
		}
		wp_enqueue_style(
			'repofolio-admin',
			REPOFOLIO_URL . 'assets/css/admin.css',
			array(),
			REPOFOLIO_VERSION
		);
	}
}
