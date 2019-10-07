<?php
namespace WPPF;

use Exception;
use WPPF\profilers\Hook;
use WPPF\profilers\Request;

class Install {

    /**
     * Mu plugin filename
     *
     * @var string
     * */
	private static $filename = '00000001_wppf.php';

	/**
	 * @throws Exception
	 */
	public static function install() {
		self::migration();

		self::installMuPlugin();

		WPPF::setProfilerStatus( Hook::class, WPPF::PROFILER_ACTIVE );
		WPPF::setProfilerStatus( Request::class, WPPF::PROFILER_ACTIVE );

	}


	/**
	 *
	 */
	public function uninstall() {
		self::uninstall_mu_plugin();

	}


	/**
	 *
	 */
	private static function installMuPlugin() {
		$source = dirname( __FILE__ ) . '/../mu-plugins/wppf.php';
		$target = WPMU_PLUGIN_DIR . '/' . self::$filename;

		if ( ! file_exists( WPMU_PLUGIN_DIR ) || ! is_dir( WPMU_PLUGIN_DIR ) ) {
			mkdir( WPMU_PLUGIN_DIR );
		}

		if ( ! copy( $source, $target ) ) {
			add_action(
				'admin_notices',
				function () {
					?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Can\'t install mu-plugin file!', 'wp-profiler' ); ?></p>
                    </div>
					<?php
				}
			);
		}

	}


	/**
	 * @return boolean
	 */
	private static function uninstall_mu_plugin() {
		$target = WPMU_PLUGIN_DIR . '/' . self::$filename;

		if ( unlink( $target ) ) {
			return true;
		}

		return false;

	}


	/**
	 * Exception @throws Exception
	 */
	private static function migration() {
		Bootstrap::init();
	}


}//end class

