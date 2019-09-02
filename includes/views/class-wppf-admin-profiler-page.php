<?php

if ( ! defined( 'WPPF_PLUGIN_ACTIVE' ) ) {
	exit;
}

/**
 * Class WPPF_Admin_Profiler_Page
 */
class WPPF_Admin_Profiler_Page{

	/**
	 * WPPF_Admin_Profiler_Page constructor.
	 *
	 * @param string $slug
	 */
    public function __construct( $slug ){
        $this->add_submenu_page( $slug );
    }

	// endregion

	// region Page

	/**
	 * @param string $slug
	 */
	protected function add_submenu_page( $slug ){
		add_submenu_page(
			$slug,
			__( 'Profiler', 'wppf' ),
			__( 'Profiler', 'wppf' ),
			'manage_options',
			$slug,
			array(
				$this,
				'on_hook_page_profiler'
			)
		);
	}

	/**
	 * Show Task submenu page
	 *
	 * Callback for admin page render
	 */
	public function on_hook_page_profiler(){
        echo 1;
	}


}