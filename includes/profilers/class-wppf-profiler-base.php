<?php
defined( 'ABSPATH' ) || exit;

abstract class WPPF_Profiler_Base {

	public static $name;

	public static function rules() {

	}

	/**
	 * Form to run profiler
	 */
	public static function prepare() {
		add_action('wp_footer',function() {
			echo sprintf( '<form id="%s" class="%s" action="" method="post"><input type="submit" name="%s" value="%s"></form>',
				static::class,
				static::class . '_form',
				static::class,
				static::getName()
			);
		});
	}

	public static function init() {
		if ( isset( $_POST[ static::class ] ) ) {
			static::run();
		} else {
			static::prepare();
		}
	}

	public static function getName() {

		if ( isset( static::$name ) ) {
			return static::$name;
		}

		$name = preg_replace( '/^WPPF_/', '', static::class );

		return str_replace( '_', ' ', $name );
	}

	abstract public static function run();

}
