<?php

/**
 *
 * @property int id
 * @property double time
 */
class WPPF_Request_model extends WPPF_active_record {

	/**
	 * Table name
	 *
	 * @return string
	 */
	public static function tableName() {
		return "{{%request}}";
	}

	public function rules()
	{
		return [

		];
	}

	/**
	 * Yii component behaviours
	 *  Using timestamp behaviour to set created and updated at
	 *  Column values.
	 *
	 * @return array
	 */
	public function behaviors()
	{
		return [

		];
	}


}