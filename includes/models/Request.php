<?php
namespace WPPF\models;

/**
 *
 * @property int id
 * @property double time
 */
class Request extends ActiveRecord {

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