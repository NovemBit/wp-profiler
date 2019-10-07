<?php


defined( 'ABSPATH' ) || exit;

$active_plugins = get_option( 'active_plugins' );

if ( !in_array( "wp-profiler/wp-profiler.php", $active_plugins ) ) {
	return;
}

include_once WP_PLUGIN_DIR . '/wp-profiler/vendor/autoload.php';

WPPF\Bootstrap::init();