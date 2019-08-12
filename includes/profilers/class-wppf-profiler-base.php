<?php
defined( 'ABSPATH' ) || exit;

abstract class WPPF_Profiler_Base {

	public $name;

	public function __construct($config) {

		foreach($config as $key=>$value){
			$this->{$key} = $value;
		}
	}

	/**
	 * Form to run profiler
	 */
	public function prepare() {
		add_action('wp_footer',function() {
			echo sprintf( '<form id="%s" class="%s" action="" method="post"><input type="submit" name="%s" value="%s"></form>',
				static::class,
				static::class . '_form',
				static::class,
				static::getName()
			);
		});
	}

	public function init() {
		if ( isset( $_POST[ static::class ] ) ) {
			$this->run();
		} else {
			$this->prepare();
		}
	}

	public function getName() {

		if ( isset( $this->name ) ) {
			return $this->name;
		}

		$name = preg_replace( '/^WPPF_/', '', static::class );

		return str_replace( '_', ' ', $name );
	}

	public function run(){}

//	abstract public static function view();

}
