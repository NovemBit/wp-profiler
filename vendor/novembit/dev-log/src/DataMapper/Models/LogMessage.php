<?php

namespace DevLog\DataMapper\Models;

use DevLog\DevLogHelper;

class LogMessage {

	const STRING = 1;
	const OBJECT = 2;
	const ASSOC = 3;

	private $id;

	private $type;

	private $message;

	private $category;

	private $time;

	/**
	 * LogData constructor.
	 *
	 * @param $id
	 * @param $type
	 * @param $message
	 * @param $category
	 * @param $time
	 *
	 * @throws \Exception
	 */
	public function __construct( $id, $type, $message, $category, $time ) {

		$this->setId( $id );
		$this->setType( $type );
		$this->setMessage( $message );
		$this->setCategory( $category );
		$this->setTime( $time );

	}

	/**
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param mixed $type
	 */
	public function setType( $type ) {
		$this->type = $type;
	}

	/**
	 * @param $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getMessage( $type = null ) {

		if ( $type == self::STRING ) {
			return $this->message;
		} elseif ( $type == self::OBJECT ) {
			$result = json_decode( $this->message );
		} else {
			$result = json_decode( $this->message, true );
		}

		return json_last_error() == JSON_ERROR_NONE ? $result : $this->message;

	}

	/**
	 * @param mixed $message
	 *
	 * @throws \Exception
	 */
	public function setMessage( $message ) {
		if ( ! is_string( $message ) ) {
			$message = DevLogHelper::jsonEncode( $message );
		}
		$this->message = $message;
	}

	/**
	 * @return mixed
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param mixed $category
	 */
	public function setCategory( $category ) {
		$this->category = $category;
	}

	/**
	 * @return mixed
	 */
	public function getTime() {
		return $this->time;
	}

	/**
	 * @param mixed $time
	 */
	public function setTime( $time ) {
		$this->time = $time;
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
