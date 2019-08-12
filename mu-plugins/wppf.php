<?php

$active_plugins = get_option( 'active_plugins' );

if ( !in_array( "wp-profiler/wp-profiler.php", $active_plugins ) ) {
	return;
}

include_once WP_PLUGIN_DIR . '/wp-profiler/includes/class-wppf-bootstrap.php';

WPPF_Bootstrap::init();