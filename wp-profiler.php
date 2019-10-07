<?php
/**
 * Plugin Name: WordPress Profiler - Powerful WordPress Profiler and Debugging Tool
 * Plugin URI:
 * Description: Profile your WordPress website to find slow hooks and callbacks
 * Version: 0.1
 * Author: Novembit
 * Author URI:
 * License: GPLv3
 * Text Domain: wp-profiler
 */

use WPPF\WPPF;

defined( 'ABSPATH' ) || exit;

// Include the main WPPF class.
include_once dirname( __FILE__ ) . '/vendor/autoload.php';

// Define WPPF_PLUGIN_FILE.
if ( ! defined( 'WPPF_PLUGIN_FILE' ) ) {
	define( 'WPPF_PLUGIN_FILE', __FILE__ );
}

/**
 * Returns the main instance of WPPF.
 *
 * @return WPPF
 */
function WPPF() {
	return WPPF::instance();
}

// Global for backwards compatibility.
$GLOBALS['wppf'] = WPPF();
