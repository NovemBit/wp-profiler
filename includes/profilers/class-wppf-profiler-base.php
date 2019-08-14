<?php
defined( 'ABSPATH' ) || exit;

abstract class WPPF_Profiler_Base {

	private static $_id = 0;

	public $name;

	/**
	 * WPPF_Profiler_Base constructor.
	 *
	 * @param $config
	 */
	public function __construct( $config ) {

		foreach ( $config as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Form to run profiler
	 * Prepare method can be overwrite
	 */
	public function prepare() {
		add_action( 'wp_footer', function () {
			echo sprintf( '<form id="%s" class="%s" action="" method="post"><input type="submit" name="%s" value="%s"></form>',
				static::class,
				self::class . '_form ' . static::class . '_form child_' . self::$_id,
				static::class,
				static::getName()
			);
			?>

            <style>
                .WPPF_Profiler_Base_form {
                    position: fixed;
                    left: 10px;
                    bottom: 35px;
                    margin-bottom: 0;
                    z-index: 999999;
                }

                .WPPF_Profiler_Base_form input[type=submit] {
                    background: #8e0000;
                    border-radius: 0;
                    font-size: 12px;
                }
            </style>

			<?php
		} );
	}

	/**
	 * Initialization
	 * If isset Post of current class name
	 * Then run profiling
	 */
	public function init() {

		self::$_id ++;

		if ( isset( $_POST[ static::class ] ) ) {
			$this->run();
		} else {
			$this->prepare();
		}
	}

	/**
	 * @return mixed
	 */
	public function getName() {

		if ( isset( $this->name ) ) {
			return $this->name;
		}

		$name = preg_replace( '/^WPPF_/', '', static::class );

		return str_replace( '_', ' ', $name );
	}

	/**
	 * Method that runes profiler
	 */
	public function run() {
		//TODO: run action of base class
	}

}
