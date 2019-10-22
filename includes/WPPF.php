<?php
namespace WPPF;

use WPPF\profilers\Hook;
use WPPF\profilers\Profiler;
use WPPF\profilers\Request;
use WPPF\views\AdminBar;

defined( 'ABSPATH' ) || exit;

class WPPF {

	const SLUG = "wppf";

	const PROFILER_ACTIVE = 1;
	const PROFILER_INACTIVE=0;

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
		register_activation_hook( WPPF_PLUGIN_FILE, [ Install::class, 'install' ] );
		register_deactivation_hook( WPPF_PLUGIN_FILE, [ Install::class, 'uninstall' ] );

//		register_shutdown_function( array( $this, 'log_errors' ) );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'init', array( $this, 'init' ), PHP_INT_MAX - 10 );
	}

	public function on_plugins_loaded() {
		do_action( 'wppf_loaded' );
	}


	/**
	 * @throws \Exception
	 */
	public function init() {
		new AdminBar();
		if ( is_admin() ) {
			AdminManager::run();
		}
	}

	/**
	 * @param string $option
	 * @param null $default
	 *
	 * @return array|mixed|void
	 */
	public static function getOption( $option, $default = null ) {
		if ( self::isOptionConstant( $option ) ) {
			return constant( self::class . "_" . $option );
		}

		return get_option( self::class . "_" . $option, $default );
	}


	/**
	 * @param $option
	 *
	 * @return bool
	 */
	public static function isOptionConstant( $option ) {
		$option = self::class . "_" . $option;

		return defined( $option );
	}

	/**
	 * @param $option
	 * @param $value
	 *
	 * @return bool
	 */
	public static function setOption( $option, $value ) {

		$option = self::class . "_" . $option;

		if ( update_option( $option, $value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $profiler
	 *
	 * @return bool
	 */
	public static function isActiveProfiler( $profiler ) {
		return in_array( $profiler, self::getAllProfilerList() ) && in_array( $profiler, self::getActiveProfilerList() );
	}

	public static function getActiveProfilerList() {
		return WPPF::getOption( 'active_profiler_list', [] );
	}

	/**
	 * @return Profiler[]
	 */
	public static function getAllProfilerList() {
		return [
			Hook::className(),
			Request::className()
		];
	}

	/**
	 * @param $profiler
	 * @param bool $status
	 */
	public static function setProfilerStatus( $profiler, $status ) {

		$active_profiler_list = WPPF::getActiveProfilerList();

		$active_profiler_list = array_unique($active_profiler_list);

		if ( !in_array( $profiler, $active_profiler_list ) && $status === self::PROFILER_ACTIVE ) {
			$active_profiler_list[] = $profiler;
		} elseif(in_array( $profiler, $active_profiler_list ) && $status === self::PROFILER_INACTIVE){
			if (($key = array_search($profiler, $active_profiler_list)) !== false) {
				unset($active_profiler_list[$key]);
			}
		}

		WPPF::setOption( 'active_profiler_list', $active_profiler_list );
	}

	/**
	 * Setting up assets version
	 */
	private function init_assets_version() {
		WPPF::setOption( 'assets_version', $this->version );
	}

}