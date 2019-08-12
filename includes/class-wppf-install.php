<?php

defined( 'ABSPATH' ) || exit;

class WPPF_Install {

	private static $filename = 'wppf.php';

	public static function install() {
		self::install_mu_plugin();
	}

	public function uninstall(){
		self::uninstall_mu_plugin();
	}

	private static function install_mu_plugin() {

		$source = dirname( __FILE__ ) . '/../mu-plugins/' . self::$filename;
		$target = WPMU_PLUGIN_DIR . '/' . self::$filename;

		if ( ! copy( $source, $target ) ) {
			add_action( 'admin_notices', function () {
				?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'Can\'t install mu-plugin file!', 'wp-profiler' ); ?></p>
                </div>
				<?php
			} );

		}

	}

	private static function uninstall_mu_plugin(){
		$target = WPMU_PLUGIN_DIR . '/' . self::$filename;

		if(unlink($target)){
		    return true;
        }
		return false;

	}


}