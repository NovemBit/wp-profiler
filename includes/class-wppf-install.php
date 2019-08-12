<?php

defined( 'ABSPATH' ) || exit;

class WPPF_Install {

	public static function install() {

		self::install_mu_plugin();

	}

	private static function install_mu_plugin() {

		$filename = 'wppf.php';
		$source   = dirname( __FILE__ ) . '/../mu-plugins/' . $filename;
		$target   = WPMU_PLUGIN_DIR . '/' . $filename;

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


}