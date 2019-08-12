<?php

/*
 * Register class autoload statement
 *
 * */
try {
	spl_autoload_register( function ( $className ) {

		if ( preg_match( '/^DevLog\\\\.*/', $className ) ) {

			$className = preg_replace( '/^DevLog/', 'src', $className );

			$className = str_replace( "\\", DIRECTORY_SEPARATOR, $className );

			include_once( __DIR__ . "/$className.php" );
		}
	} );
} catch ( Exception $e ) {
}