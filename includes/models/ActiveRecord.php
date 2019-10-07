<?php

namespace WPPF\models;

use yii\db\Connection;

/**
 * Yii ActiveRecord
 * */
class ActiveRecord extends \yii\db\ActiveRecord {

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