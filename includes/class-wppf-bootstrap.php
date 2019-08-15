<?php

defined( 'ABSPATH' ) || exit;

use DevLog\DevLog;

class WPPF_Bootstrap {

	/**
	 * @throws Exception
	 */
	public static function init() {

		self::includeFiles();

		self::defineConstants();

		DevLog::register();

		foreach ( self::getActiveProfilerList() as $profiler ) {

			/*
			 * Creating profiler object with config
			 * */
			$obj = new $profiler( WPPF::getOption( $profiler . "_config", [] ) );

			/*
			 * Calling init method of profiler
			 * */
			call_user_func( [ $obj, 'init' ] );
		}

	}


	/**
	 * Getting all active profiler list
	 * With array
	 * @return string[]
	 */
	private static function getActiveProfilerList() {
		return WPPF::getOption( 'active_profiler_list', [] );
	}

	/**
	 * Include composer file
	 */
	private static function includeFiles() {

		/*
		 * Include composer vendor autoload.php file
		 * */
		include_once dirname( __FILE__ ) . "/../vendor/autoload.php";

		include_once "class-wppf.php";
		include_once "profilers/class-wppf-profiler-base.php";
		include_once "profilers/class-wppf-hook-profiler.php";
		include_once "profilers/class-wppf-request-profiler.php";
	}

	/**
	 * Define constants
	 */
	private static function defineConstants() {

		/*
		 * Connect WP database to DevLog Profiler
		 * */
		if ( ! defined( 'DEV_LOG_DB' ) ) {
			define( 'DEV_LOG_DB', array(
				'pdo'      => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
				'username' => DB_USER,
				'password' => DB_PASSWORD,
				'config'   => [
					PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
				]
			) );
		}
	}
}