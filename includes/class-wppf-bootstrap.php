<?php
/**
 * Bootstrap class
 *
 * @package WPPF
 * */

defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap class
 * */
class WPPF_Bootstrap {


	/**
	 * Init method
	 *
	 * Exception
	 *
	 * Exception @throws Exception
	 */
	public static function init() {
		self::includeFiles();

		self::defineConstants();

		foreach ( WPPF::getActiveProfilerList() as $profiler ) {
			/**
			 * Creating profiler object with config
			 * */
			$obj = new $profiler( WPPF::getOption( $profiler . '_config', [] ) );

			/**
			 * Calling init method of profiler
			 * */
			call_user_func( [ $obj, 'init' ] );
		}

	}


	/**
	 * Include composer file
	 */
	private static function includeFiles() {
		/*
		 * Include composer vendor autoload.php file
		*/
		include_once dirname( __FILE__ ) . '/../vendor/autoload.php';

		/**
		 * Check if yii framework not initialized
		 */
		if ( ! class_exists( 'Yii' ) ) {
			defined( 'YII_DEBUG' ) || define( 'YII_DEBUG', false );
			defined( 'YII_ENV' ) || define( 'YII_ENV', 'prod' );
			include __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
		}
		include __DIR__ . '/models/class-wppf-active-record.php';
		include __DIR__ . '/models/class-wppf-request-model.php';

		include_once 'class-wppf.php';
		include_once 'profilers/class-wppf-profiler-base.php';
		include_once 'profilers/class-wppf-hook-profiler.php';
		include_once 'profilers/class-wppf-request-profiler.php';

	}


	/**
	 * Define constants
	 */
	private static function defineConstants() {

		$request = new WPPF_Request_model();
		$request->time = microtime(true);

		if($request->save()) {
			define( 'WPPF_REQUEST_ID', $request->id );
		}

	}


}
