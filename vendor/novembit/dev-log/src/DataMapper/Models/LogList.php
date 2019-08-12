<?php

namespace DevLog\DataMapper\Models;

class LogList {

	private $list = [];


	public function __construct() {

	}

	/**
	 * @param Log $log
	 */
	public function addLog( Log $log ) {
		$this->list[] = $log;
	}

	/**
	 * @return Log[]
	 */
	public function getList() {
		return $this->list;
	}


	/**
	 * @param bool $last
	 *
	 * @return Log|null
	 */
	public function one( $last = true ) {
		if ( $last ) {
			return end( $this->list ) ?? null;
		} else return $this->getList()[0] ?? null;
	}

	/**
	 * @param Log[] $list
	 */
	public function setList( array $list ) {
		$this->list = $list;
	}

}
