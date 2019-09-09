<?php
defined( 'ABSPATH' ) || exit;

abstract class WPPF_Profiler_Base {

	private static $_id = 0;

	public static $name;

	public static $is_defined_config;

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
	 * @throws ReflectionException
	 */
	public function init() {
		self::$_id ++;

		$this->registerEndpoints();

		if ( isset( $_POST[ static::class ] ) ) {
			$this->run();
		} else {
			$this->prepare();
		}
	}

	/**
	 * @return mixed
	 */
	public static function getName() {

		if ( isset( static::$name ) ) {
			return static::$name;
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

	/**
	 * @return string|string[]|null
	 */
	public static function getSlug() {
		return WPPF::SLUG . '-' . strtolower( preg_replace( '/\s+/', '-', static::getName() ) );
	}

	/**
	 * @param $word
	 *
	 * @return string
	 */
	public static function generateName( $word ) {
		return ucfirst( preg_replace( '/_/', ' ', $word ) );
	}

	/**
	 * @return bool
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function registerEndpoints() {

		$modelReflector = new ReflectionClass( $this );
		$methods        = $modelReflector->getMethods();
		if ( isset( $_GET[ static::class . '_view' ] ) && isset( $_GET['endpoint'] ) ) {

			$action = $_GET['endpoint'];
			$method = $modelReflector->getMethod( "endpoint" . $action );
			if ( $method ) {

				$values = [];
				foreach ( $method->getParameters() as $parameter ) {

					if ( ! isset( $_GET[ $parameter->getName() ] ) ) {
						throw new Exception( 'Unknown ' . $parameter->getName() . ' value.', 404 );
					}
					$values[] = $_GET[ $parameter->getName() ];
				}

				call_user_func_array( array( $this, $method->name ), $values );
				die;
			}

		}

		return false;
	}

	public static function beforeRenderPage() {
		/*
		 * If constant set
		 * */
		if ( WPPF::isOptionConstant( static::class . "_config" ) ) {

			add_action( 'admin_notices', function () {
				$class   = 'notice notice-success';
				$message = __( 'The configs from admin disabled, because it\'s defined in wp-config.php!', 'wppf' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			} );
			self::$is_defined_config = true;
		}

		if ( isset( $_POST[ static::class ] ) && is_array( $_POST[ static::class ] ) ) {

			if ( WPPF::setOption( static::class . "_config", $_POST[ static::class ] ) ) {

				add_action( 'admin_notices', function () {
					$class   = 'notice notice-success';
					$message = __( 'Saved!', 'wppf' );

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				} );
				/*if ( wp_redirect(admin_url('admin.php?page='.WPPF::SLUG)) ) {
					exit;
				}*/
			} else {
				add_action( 'admin_notices', function () {
					$class   = 'notice notice-error';
					$message = __( 'No changes!', 'wppf' );

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				} );
			}
		}
	}

	public static function formFields() {
		return [
		];
	}

	public static function renderTab() {
		if ( self::$is_defined_config === true ) {
			return;
		}
		$reflect     = new ReflectionClass( static::class );
		$props       = $reflect->getProperties( ReflectionProperty::IS_PUBLIC );
		$option_name = static::class . "_config";

		$config = WPPF::getOption( $option_name, [] );

		?>
        <form method="post" id="form">
        <table class="form-table">
            <tbody>
			<?php
			foreach ( $props as $prop ) {
				/*
				 * Get only child class properties
				 * */
				if ( $prop->class != $reflect->name ) {
					continue;
				}
				$name        = $prop->name;
				$value       = isset( $config[ $name ] ) ? $config[ $name ] : null;
				$input_type  = isset( static::formFields()[ $name ]['type'] ) ? static::formFields()[ $name ]['type'] : null;
				$hint        = isset( static::formFields()[ $name ]['hint'] ) ? static::formFields()[ $name ]['hint'] : null;
				$description = isset( static::formFields()[ $name ]['description'] ) ? static::formFields()[ $name ]['description'] : null;
				?>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $name; ?>">
							<?php echo self::generateName( $name ); ?>
                        </label>
                    </th>
                    <td>
						<?php
						switch ( $input_type ) {
							case 'textarea':
								echo sprintf( '<textarea id="%s" name="%s" form="form" rows="10" cols="50" class="large-text code">%s</textarea>', $name, static::class . '[' . $name . ']', $value );
								break;
							default:
								echo "default";
								break;
						}
						?>
                    </td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>
		<?php submit_button( 'Save Changes', 'primary', 'submit', true, array() );
	}

}
