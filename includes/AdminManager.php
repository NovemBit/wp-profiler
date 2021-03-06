<?php

namespace WPPF;

use WPPF\views\AdminProfilerListPage;
use WPPF\views\AdminSettingsPage;

class AdminManager {
	// region Singleton

	/**
	 * @var bool
	 */
	private static $created = false;

	/**
	 * @throws \Exception
	 *      in the case this method called more than once
	 */
	public static function run() {
		if ( false === self::$created ) {
			new self();
		} else {
			throw new \Exception( 'WPPF_Admin_Manager should only run once inside this plugin' );
		}
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'on_hook_admin_menu_setup' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'on_hook_admin_enqueue_scripts' ) );
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	// endregion

	/**
	 * Should only be called from hook admin_enqueue_scripts
	 * @see https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 */
	function on_hook_admin_enqueue_scripts() {
		$assets_version = WPPF::getOption( 'assets_version', '1.0.0' );
	}

	// region Admin Menu

	/**
	 * Setup plugin admin menus
	 *
	 * Should only be called from hook admin_menu
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function on_hook_admin_menu_setup() {


		// Admin menu WP Profiler page init
		add_menu_page(
			__( 'WP Profiler', 'wppf' ),
			__( 'WP Profiler', 'wppf' ),
			'manage_options',
			WPPF::SLUG
		);

		new AdminSettingsPage();
		new AdminProfilerListPage();

	}

	// endregion
}