<?php

/**
 *
 * @property int request_id
 * @property string name
 * @property bool is_hook
 * @property int parent_id
 * @property double time
 * @property double duration
 * @property string file
 * @property string module
 */
class WPPF_Hook_profiler_model extends WPPF_active_record {

	/**
	 * Table name
	 *
	 * @return string
	 */
	public static function tableName() {
		return "{{%hook_profiler_log}}";
	}

	public function rules() {
		return [
			/*[['from_language', 'to_language'], 'required'],*/

			[ [ 'name', 'request_id' ], 'required' ],
			[ [ 'is_hook' ], 'boolean' ],
			[ [ 'name' ], 'string', 'max' => 191 ],
			[ [ 'parent_id', 'request_id' ], 'integer' ],
			[ [ 'time', 'duration' ], 'double' ],
		];
	}

	/**
	 * Yii component behaviours
	 *  Using timestamp behaviour to set created and updated at
	 *  Column values.
	 *
	 * @return array
	 */
	public function behaviors() {
		return [
			/*,
			[
				'class' => AttributeBehavior::className(),
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => 'name_id',
				],
				'value' => function ($event) {
					return FlyName::find()
						->orderBy(new Expression('rand()'))->one()->id;
				},
			],*/
		];
	}


}