<?php
namespace WPPF;

use Exception;
use WPPF\models\Request;

/**
 * Bootstrap class
 * */
class Bootstrap {


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
			$class = "\\WPPF\\profilers\\".ucfirst($profiler);

			$obj = new $class( WPPF::getOption( $profiler . '_config', [] ) );


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
		/**
		 * Check if yii framework not initialized
		 */
		if ( ! class_exists( 'Yii' ) ) {
			defined( 'YII_DEBUG' ) || define( 'YII_DEBUG', false );
			defined( 'YII_ENV' ) || define( 'YII_ENV', 'prod' );
			include __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
		}

	}


	/**
	 * Define constants
	 */
	private static function defineConstants() {

		$request = new Request();
		$request->time = microtime(true);

		if($request->save()) {
			define( 'WPPF_REQUEST_ID', $request->id );
		}

	}


}
