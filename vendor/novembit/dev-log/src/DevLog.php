<?php

namespace DevLog;
/*
 * Dev log
 * Simple and Powerful debugging tool
 * */

final class DevLog extends DevLogBase {

	public static $messageTypes = [
		'message'   => "table-dark",
		'info'      => "table-success",
		'warning'   => "table-warning",
		'error'     => "table-danger",
		'note'      => "table-info",
		'secondary' => "table-secondary",
		'important' => "table-primary",
	];

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return DataMapper\Models\LogMessage
	 * @throws \Exception
	 */
	public static function message( $message, $category = "default" ) {

		return static::log( 'message', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function info( $message, $category = "default" ) {

		return static::log( 'info', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function note( $message, $category = "default" ) {

		return static::log( 'note', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function secondary( $message, $category = "default" ) {

		return static::log( 'secondary', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function important( $message, $category = "default" ) {

		return static::log( 'important', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function warning( $message, $category = "default" ) {

		return static::log( 'warning', $message, $category );

	}

	/**
	 * @param $message
	 * @param string $category
	 *
	 * @return int|string|null
	 * @throws \Exception
	 */
	public static function error( $message, $category = "default" ) {

		return self::log( 'error', $message, $category );

	}


	/**
	 * Register page shutdown actions
	 * @throws \Exception
	 */
	public static function registerShutDownActions() {
		parent::registerShutDownActions();
		static::important( "Page loaded." );
	}

	/**
	 * Register page start actions
	 * @throws \Exception
	 */
	public static function registerStartActions() {
		parent::registerStartActions();
		static::important( "Page started." );
	}


	/**
	 * @return array
	 */
	public static function getTrackers() {
		if ( ! defined( "DEV_LOG_TRACKERS" ) ) {
			return parent::getTrackers();
		}

		return array_merge( parent::getTrackers(), include_once( DEV_LOG_TRACKERS ) );
	}
}