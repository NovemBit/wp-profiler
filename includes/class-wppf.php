<?php

defined( 'ABSPATH' ) || exit;

final class WPPF {

	public $version = '1.0.0';

	private static $_instance = null;

	/**
	 * @return WPPF|null
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->init_assets_version();
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string $name Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function define_constants() {
		$this->define( 'WPPF_ABSPATH', dirname( WPPF_PLUGIN_FILE ) . '/' );
		$this->define( 'WPPF_PLUGIN_BASENAME', plugin_basename( WPPF_PLUGIN_FILE ) );
		$this->define( 'WPPF_VERSION', $this->version );
		$this->define( 'WPPF_PLUGIN_ACTIVE', true );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.3
	 */
	private function init_hooks() {
		register_activation_hook( WPPF_PLUGIN_FILE, array( 'WPPF_Install', 'install' ) );
		register_deactivation_hook(WPPF_PLUGIN_FILE, array( 'WPPF_Install', 'uninstall' ));

//		register_shutdown_function( array( $this, 'log_errors' ) );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'init', array( $this, 'init' ), PHP_INT_MAX - 10 );
	}

	public function on_plugins_loaded() {
		do_action( 'wppf_loaded' );
	}


	public function init() {
		if( is_admin() ){
			WPPF_Admin_Manager::run();
		}
	}

	public function includes() {

		include_once "class-wppf-bootstrap.php";
		include_once "class-wppf-install.php";
		include_once "class-wppf-admin-manager.php";

		include_once "views/class-wppf-admin-profiler-page.php";
	}


	/**
	 * @param string $option
	 * @param null $default
	 *
	 * @return array|mixed|void
	 */
	public static function getOption($option, $default = null){
		$option = self::class."_".$option;
		if(defined($option)){
			return constant($option);
		}
		return get_option($option,$default);
	}

	/**
	 * @param $option
	 * @param $value
	 *
	 * @return bool
	 */
	public static function setOption($option,$value){

		$option = self::class."_".$option;

		if(update_option($option,$value)){
			return true;
		}

		return false;
	}

	/**
	 * Setting up assets version
	 */
	private function init_assets_version() {
		WPPF::setOption( 'assets_version', $this->version );
	}

}