<?php

use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * Yii ActiveRecord
 * */
class WPPF_active_record extends ActiveRecord {

	private static $_connection;
	/**
	 * Global DB
	 *
	 * @return Connection
	 */
	public static function getDb() {

		if(!isset(self::$_connection)){

			self::$_connection = new Connection(
				[
					'dsn'         => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
					'username'    => DB_USER,
					'password'    => DB_PASSWORD,
					'charset'     => 'utf8mb4',
					'tablePrefix' => 'wppf_'
				]
			);

		}

		return self::$_connection;

	}

}