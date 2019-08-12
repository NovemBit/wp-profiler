<?php

namespace DevLog\DataMapper\Mappers;


use DevLog\DataMapper\Models\LogMessageList;
use DevLog\DevLog;
use PDO;

class LogMessage {

	/**
	 * @param array $criteria
	 * @param array $order
	 *
	 * @param array $limit
	 *
	 * @return LogMessageList
	 * @throws \Exception
	 */
	public static function get( array $criteria = [], array $order = [], array $limit = [] ) {

		$db = DevLog::getDb();

		/*
		 * Select fields as array
		 * */
		$select = [
			"id",
			"type",
			"message",
			"category",
			"time"
		];

		$fields = '';
		foreach ( $select as $key => $field ) {
			if ( is_string( $key ) ) {
				$fields .= $key . " AS " . $field;
			} else {
				$fields .= $field;
			}

			if ( next( $select ) == true ) {
				$fields .= ', ';
			}
		}

		/*
		 * Building where statement
		 * */
		$where = '';
		foreach ( $criteria as $value ) {
			if ( is_array( $value ) ) {
				$where .= "$value[0] $value[1] :" . crc32( $value[0] );
				if ( next( $criteria ) == true ) {
					$where .= isset( $value[3] ) ? $value[3] : ' AND ';
				}
			}
		}
		$where = $where != '' ? "WHERE " . $where : '';


		/*
		 * Building order by statement
		 * */
		$order_by = '';
		foreach ( $order as $key => $value ) {

			$order_by .= $key . ' ' . $value;

			if ( next( $order ) == true ) {
				$order_by .= ', ';
			}
		}
		$order_by = $order_by != '' ? "ORDER BY " . $order_by : '';


		$limit_string = '';
		$limit_string .= $limit[0] ?? '';
		$limit_string .= isset( $limit[1] ) ? ", " . $limit[1] : '';
		$limit_string = $limit_string != '' ? "LIMIT " . $limit_string : '';


		$sql = "SELECT $fields FROM logs_messages";

		$sql .= " $where $order_by $limit_string";


		$stmt = $db->prepare( $sql );

		foreach ( $criteria as $value ) {
			if ( is_array( $value ) ) {
				$stmt->bindValue( ":" . crc32( $value[0] ), $value[2] );
			}
		}

		$stmt->execute();


		$list = new LogMessageList();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$list->addMessage(
				new \DevLog\DataMapper\Models\LogMessage(
					$row['id'],
					$row['type'],
					$row['message'],
					$row['category'],
					$row['time'] )
			);
		}

		return $list;
	}
}
