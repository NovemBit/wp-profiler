<?php
defined( 'ABSPATH' ) || exit;

use DevLog\DevLog;

class WPPF_Hook_Profiler extends WPPF_Profiler_Base {



	public static function run() {
		self::retrieve_wp_hooks();
	}

	private static $_mutex = [];

	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_start( $a ) {
		DevLog::log( 'S', current_filter(), '' );

		return $a;
	}

	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_end( $a ) {
		DevLog::log( 'E', current_filter(), '' );
		self::retrieve_wp_hooks();

		return $a;

	}

	/**
	 * Fetch hooks and add callbacks to measure
	 * Compiling time
	 */
	private static function retrieve_wp_hooks() {

		/*
		 * Taking global hooks variable
		 * */
		global $wp_filter;

		foreach ( $wp_filter as $name => &$hook ) {

			if ( ! isset( self::$_mutex[ $name ] ) ) {
				self::$_mutex[ $name ] = true;


				$hook->callbacks = array( PHP_INT_MIN => $hook->callbacks[ PHP_INT_MIN ] ?? array() ) + $hook->callbacks;

				$hook->callbacks[ PHP_INT_MAX ] = $hook->callbacks[ PHP_INT_MAX ] ?? [];

				$hook->callbacks[ PHP_INT_MIN ] = array(
					                                  'DevLog_hook_start' => array(
						                                  'function'      => array( self::class, 'hook_start' ),
						                                  'accepted_args' => 1

					                                  )
				                                  ) + $hook->callbacks[ PHP_INT_MIN ];

				$hook->callbacks[ PHP_INT_MAX ] = array(
					                                  'DevLog_hook_end' => array(
						                                  'function'      => array( self::class, 'hook_end' ),
						                                  'accepted_args' => 1

					                                  )
				                                  ) + $hook->callbacks[ PHP_INT_MAX ];

				/** @var WP_Hook $hook */
				foreach ( $hook->callbacks as $priority => &$callbacks ) {
					$_callbacks = $callbacks;
					foreach ( $_callbacks as $index => &$callback ) {

						if ( strpos( $index, 'DevLog_' ) !== 0 ) {
							self::array_insert( $callbacks, $index, [
								'DevLog_CB_' . $index => [
									'function'      => function ( $a ) use ( $index ) {

										/*
										 * Getting declaration coordinates
										 * File and line
										 * */
										$cat = '';
										if ( function_exists( $index ) ) {
											$reflFunc = new ReflectionFunction( $index );
											$cat      = str_replace( ABSPATH, '', $reflFunc->getFileName() ) . ':' . $reflFunc->getStartLine();
										} else {
											$index = "?";
										}

										DevLog::log( 'C', $index, $cat );

										return $a;
									},
									'accepted_args' => 1
								]
							] );
						}
					}

				}
			}
		}
	}

	/**
	 * @param $array
	 * @param $position
	 * @param $insert
	 */
	private static function array_insert( &$array, $position, $insert ) {
		if ( is_int( $position ) ) {
			array_splice( $array, $position, 0, $insert );
		} else {
			$pos   = array_search( $position, array_keys( $array ) );
			$array = array_merge(
				array_slice( $array, 0, $pos ),
				$insert,
				array_slice( $array, $pos )
			);
		}
	}
}
