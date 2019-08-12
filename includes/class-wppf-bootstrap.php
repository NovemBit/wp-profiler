<?php

use DevLog\DevLog;

class WPPF_Bootstrap {

	/**
	 * @throws Exception
	 */
	public static function init() {

		self::includeFiles();

		self::defineConstants();

		DevLog::register();

		foreach (self::getActiveProfilerList() as $profiler){
			call_user_func([$profiler,'init']);
		}

	}

	/**
	 * @return string[]
	 */
	private static function getActiveProfilerList(){

		return WPPF::getOption('active_profiler_list');

	}

	/**
	 * Include composer file
	 */
	private static function includeFiles() {

		include_once dirname( __FILE__ ) . "/../vendor/autoload.php";

		include_once "class-wppf.php";
		include_once "profilers/class-wppf-profiler-base.php";
		include_once "profilers/class-wppf-hook-profiler.php";
	}

	/**
	 * Define constants
	 */
	private static function defineConstants() {
		if ( ! defined( 'DEV_LOG_DB' ) ) {
			define( 'DEV_LOG_DB', array(
				'pdo'      => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
				'username' => DB_USER,
				'password' => DB_PASSWORD,
				'config'   => [
					\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
				]
			) );
		}
	}
}