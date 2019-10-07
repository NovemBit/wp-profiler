<?php
namespace WPPF\views;

use WPPF\profilers\Hook;
use WPPF\WPPF;

if ( ! defined( 'WPPF_PLUGIN_ACTIVE' ) ) {
	exit;
}

/**
 * Class WPPF_Admin_Bar
 */
class AdminBar {

	/**
	 * WPPF_Admin_Bar constructor.
	 *
	 */
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', array( $this, 'beforeAdminBarRender' ) );
	}

	public function beforeAdminBarRender(){

		global $wp_admin_bar;

		$wp_admin_bar->add_node( array(
			'id' => 'wppf_admin_bar',
			'title' => __('Profiler')
		) );

		if( WPPF::isActiveProfiler( Hook::className() ) ){
			do_action( 'wppf_admin_bar' );
		}

	}

}