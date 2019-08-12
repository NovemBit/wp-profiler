<?php

namespace DevLog;

use DevLog\DataMapper\Models\Log;
use DevLog\DataMapper\Models\LogData;
use DevLog\DataMapper\Models\LogMessage;
use PDOException;


class DevLogBase {

	public static $scriptName = "DevLog";

	public static $scriptVersion = "1.0.4";

	public static $messageTypes = [];

	public static $max_served_logs_count = 100;

	private static $log;

	private static $db;

	private static $_logs_hash;

	public static $hash_length = 12;

	public static $registered = false;

	/**
	 * Register logger
	 * To initialize logger should run this action
	 * @throws \Exception
	 */
	public static function register() {

		/**
		 * If DevLog disabled then prevent registration
		 * */
		if ( defined( 'DEV_LOG' ) && DEV_LOG == false ) {
			return;
		}

		/*
		 * To avoid second time registration
		 * Of methods
		 * */
		if ( self::$registered == true ) {
			return;
			/*throw new \Exception( "DevLog already registered." );*/
		}

		/*
		 * Default constants init.
		 * */
		self::defineConstants();

		$exclusion = false;

		/*
		 * Check if is callable
		 * */
		if ( is_callable( DEV_LOG_EXCLUSION ) ) {
			$exclusion = call_user_func( DEV_LOG_EXCLUSION );
		}

		if ( $exclusion !== true ) {
			/*
			 * Register request shutdown actions
			 * Then save log file as json
			 * And register inline debugger
			 * */
			register_shutdown_function(
				function () {

					/*
					 * Register custom shutdown actions
					 * */
					static::registerShutDownActions();

					/*
					 * Save all logged data
					 * */
					\DevLog\DataMapper\Mappers\Log::save( self::$log );
				}
			);

			static::registerStartActions();
		}

		self::$registered = true;

	}

	/**
	 * @return bool
	 */
	public static function exclusionStatus() {
		return false;
	}

	/**
	 * Define standard constants
	 */
	private static function defineConstants() {

		if ( ! defined( "DEV_LOG" ) ) {
			define( "DEV_LOG", true );
		}

		if ( ! defined( "DEV_LOG_EXCLUSION" ) ) {
			define( "DEV_LOG_EXCLUSION", [ self::class, 'exclusionStatus' ] );
		}

		if ( ! defined( "DEV_LOG_DB" ) ) {
			define( "DEV_LOG_DB", [
				'pdo' => 'sqlite:' . dirname( __FILE__ ) . '/../runtime/db/DevLog.db',

				/*
				 * Connect of mysql
				 ```
				    'pdo'      => 'mysql:host=localhost;dbname=my_db',
					'username' => 'root',
					'password' => 'root',
					'config'   => [
						\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
					]
				```
				*/
			] );
		}
	}

	/**
	 * @return \PDO
	 * @throws \Exception
	 */
	public static function getDb() {

		if ( ! isset( self::$db ) && defined( 'DEV_LOG_DB' ) ) {
			if ( ! isset( DEV_LOG_DB['pdo'] ) ) {
				throw new \Exception( 'DEV_LOG_DB["pdo"] not found' );
			}

			$pdo      = DEV_LOG_DB['pdo'];
			$username = DEV_LOG_DB['username'] ?? 'root';
			$password = DEV_LOG_DB['password'] ?? null;
			$config   = DEV_LOG_DB['config'] ?? [];

			self::$db = new \PDO( $pdo, $username, $password, $config );
		}

		return self::$db;
	}

	/**
	 * Register start script
	 * @throws \Exception
	 */
	public static function registerStartActions() {

		self::setLog( new Log( null, self::getLogHash(), 'request' ) );

		set_error_handler( [ self::class, 'errorHandler' ] );

		self::getLog()->setName( self::getLogHash() );

		self::getLog()->getDataList()->addData( new LogData( null, 'start_time', microtime( true ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_server', ( isset( $_SERVER ) ? $_SERVER : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_session', ( isset( $_SESSION ) ? $_SESSION : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_env', ( isset( $_ENV ) ? $_ENV : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_get', ( isset( $_GET ) ? $_GET : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_post', ( isset( $_POST ) ? $_POST : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_cookie', ( isset( $_COOKIE ) ? $_COOKIE : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, '_files', ( isset( $_FILES ) ? $_FILES : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, 'request_headers', ( function_exists( 'getallheaders' ) ? getallheaders() : [] ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, 'response_headers', headers_list() ) );
		/*self::getLog()->getDataList()->addData( new LogData(null, 'php_info', self::getPhpInfo() ) );*/

	}

	/**
	 * @param $errorNumber
	 * @param $errorString
	 * @param $errorFile
	 * @param $errorLine
	 *
	 * @throws \Exception
	 */
	public static function errorHandler( $errorNumber, $errorString, $errorFile, $errorLine ) {

		if ( ! defined( "DEV_LOG_PHP_REPORTING_LEVEL" ) ) {
			define( 'DEV_LOG_PHP_REPORTING_LEVEL', E_ERROR );
		}

		if ( $errorNumber > DEV_LOG_PHP_REPORTING_LEVEL ) {
			return;
		}

		$types = [
			E_ERROR             => "E_ERROR",
			E_WARNING           => "E_WARNING",
			E_PARSE             => "E_PARSE",
			E_NOTICE            => "E_NOTICE",
			E_CORE_ERROR        => "E_CORE_ERROR",
			E_CORE_WARNING      => "E_CORE_WARNING",
			E_COMPILE_ERROR     => "E_COMPILE_ERROR",
			E_COMPILE_WARNING   => "E_COMPILE_WARNING",
			E_USER_ERROR        => "E_USER_ERROR",
			E_USER_WARNING      => "E_USER_WARNING",
			E_USER_NOTICE       => "E_USER_NOTICE",
			E_STRICT            => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED        => "E_DEPRECATED",
			E_USER_DEPRECATED   => "E_USER_DEPRECATED",
			E_ALL               => "E_ALL",
		];

		$type = $types[ $errorNumber ] ?? "UNDEFINED";

		self::log( "error",
			[
				"type"    => $type,
				'message' => $errorString,
				'file'    => $errorFile,
				'line'    => $errorLine
			],
			'PHP'
		);
	}

	/**
	 * Register shutdown script
	 * @throws \Exception
	 */
	public static function registerShutDownActions() {

		self::getLog()->getDataList()->addData( new LogData( null, 'memory_usage', memory_get_usage( true ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, 'end_time', microtime( true ) ) );
		self::getLog()->getDataList()->addData( new LogData( null, 'status', http_response_code() ) );

	}


	private static function getPhpInfo() {

		ob_start();
		phpinfo();
		$php_info = ob_get_contents();
		ob_get_clean();

		return $php_info;
	}

	/**
	 * @return string
	 */
	public static function getLogHash() {
		if ( ! isset( self::$_logs_hash ) ) {
			self::$_logs_hash = substr( md5( uniqid( rand(), true ) ), 0, static::$hash_length );
		}

		return self::$_logs_hash;
	}

	/**
	 * @param $type
	 * @param $message
	 * @param string $category
	 *
	 * @return LogMessage
	 * @throws \Exception
	 */
	public static function log( $type, $message, $category = "default" ) {
		return self::getLog()->getMessageList()->addMessage( new LogMessage(
			null,
			$type,
			$message,
			$category,
			microtime( true )
		) );
	}

	/**
	 * @return Log
	 */
	public static function getLog() {
		return self::$log;
	}

	/**
	 * @param Log $log
	 */
	public static function setLog( Log $log ) {
		self::$log = $log;
	}


}