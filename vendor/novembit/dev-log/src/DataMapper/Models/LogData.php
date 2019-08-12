<?php

namespace DevLog\DataMapper\Models;

use DevLog\DevLogHelper;

class LogData {

	const STRING = 1;
	const OBJECT = 2;
	const ASSOC = 3;

	private $id;

	private $key;

	private $value;

	/**
	 * LogData constructor.
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @throws \Exception
	 */
	public function __construct( $id, $key, $value ) {
		$this->setId( $id );
		$this->setKey( $key );
		$this->setValue( $value );

	}

	/**
	 * @return mixed
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @param mixed $key
	 */
	public function setKey( $key ) {
		$this->key = $key;
	}

	/**
	 * @param $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getValue( $type = null ) {

		if ( $type == self::STRING ) {
			return $this->value;
		} elseif ( $type == self::OBJECT ) {
			$result = json_decode( $this->value );
		} else {
			$result = json_decode( $this->value, true );
		}

		return json_last_error() == JSON_ERROR_NONE ? $result : $this->value;


	}

	/**
	 * @param mixed $value
	 *
	 * @throws \Exception
	 */
	public function setValue( $value ) {
		if ( ! is_string( $value ) ) {
			$value = DevLogHelper::jsonEncode( $value );
		}
		$this->value = $value;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int|null $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}


}
