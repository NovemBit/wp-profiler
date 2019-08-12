<?php

namespace DevLog\DataMapper\Models;
class Log {

	private $id;

	private $name;

	private $type;

	private $dataList;

	private $messageList;

	/**
	 * Log constructor.
	 *
	 * @param $id
	 * @param $name
	 * @param $type
	 * @param LogDataList|null $dataList
	 * @param LogMessage $messageList
	 */
	public function __construct( $id, $name, $type, LogDataList $dataList = null, LogMessage $messageList = null ) {

		$this->setId( $id );

		$this->setName( $name );

		$this->setType( $type );

		$this->setDataList( $dataList == null ? new LogDataList() : $dataList );

		$this->setMessageList( $messageList == null ? new LogMessageList() : $messageList );

	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName( string $name ) {
		$this->name = $name;
	}

	/**
	 * @return LogDataList
	 */
	public function getDataList() {
		return $this->dataList;
	}

	/**
	 * @param LogDataList $dataList
	 */
	public function setDataList( LogDataList $dataList ) {
		$this->dataList = $dataList;
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
	 * @return LogMessageList
	 */
	public function getMessageList() {
		return $this->messageList;
	}

	/**
	 * @param mixed $messageList
	 */
	public function setMessageList( $messageList ) {
		$this->messageList = $messageList;
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
