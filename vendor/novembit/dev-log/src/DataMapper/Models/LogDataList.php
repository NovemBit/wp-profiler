<?php

namespace DevLog\DataMapper\Models;

class LogDataList {

	private $list = [];


	public function __construct() {

	}

	/**
	 * @param $key
	 *
	 * @return LogData|null
	 */
	public function getData( $key ) {
		foreach ( $this->getList() as $data ) {
			if ( $key == $data->getKey() ) {
				return $data;
			}
		}

		return null;
	}

	/**
	 * @param LogData $data
	 */
	public function addData( LogData $data ) {
		$this->list[] = $data;
	}

	/**
	 * @return LogData[]
	 */
	public function getList() {
		return $this->list;
	}

	/**
	 * @param LogData[] $list
	 */
	public function setList( array $list ) {
		$this->list = $list;
	}

}
