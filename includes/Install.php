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

		WPPF::setProfilerStatus( Hook::className(), WPPF::PROFILER_ACTIVE );
		WPPF::setProfilerStatus( Request::className(), WPPF::PROFILER_ACTIVE );

		self::installMuPlugin();


	}


	/**
	 *
	 */
	public function uninstall() {
		self::uninstall_mu_plugin();

		global $wpdb;
		$sqls = [
			'DROP TABLE IF EXISTS `wppf_hook_profiler_log`;',
			'DROP TABLE IF EXISTS `wppf_request`;',
		];
		foreach($sqls as $sql) {
			$wpdb->query( $sql );
		}
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

		global $wpdb;

		$sqls = [
			'DROP TABLE IF EXISTS `wppf_hook_profiler_log`;',
			'CREATE TABLE `wppf_hook_profiler_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `is_hook` tinyint(1) DEFAULT NULL,
  `name` varchar(191) DEFAULT NULL,
  `file` varchar(191) DEFAULT NULL,
  `module` varchar(191) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `time` double DEFAULT NULL,
  `duration` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=126250 DEFAULT CHARSET=latin1;',
			'DROP TABLE IF EXISTS `wppf_request`;',
			'CREATE TABLE `wppf_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=896 DEFAULT CHARSET=latin1;'
		];
        foreach($sqls as $sql) {
	        $wpdb->query( $sql );
        }
	}


}

