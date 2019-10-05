<?php
defined( 'ABSPATH' ) || exit;

class WPPF_Hook_Profiler extends WPPF_Profiler_Base {

	public static function getName() {
		return __( 'Hook Profiler', 'wppf' );
	}

	public function run() {

		include __DIR__ . '/hook-profiler/models/class-wppf-hook-profiler-model.php';

		$transaction = WPPF_Hook_profiler_model::getDb()->beginTransaction();

		self::retrieve_wp_hooks();

		$transaction->commit();

		add_action(
			'wppf_admin_bar',
			function () {
				global $wp_admin_bar;
				$wp_admin_bar->add_menu( array(
					'parent' => 'wppf_admin_bar',
					'id'     => 'view_hook_profiler_results',
					'title'  => __( 'View Results' ),
					'href'   => 'page-hook-profiler.php?' . self::class . '_view&endpoint=Results&log_name=' . DevLog::getLogHash(),
					'meta'   => array( 'target' => '_blank' )
				) );
			} );
	}


	private $_mutex = [];


	/**
	 * Previous hook model
	 *
	 * @var WPPF_Hook_profiler_model
	 */
	private $hook_model;

	/**
	 * Previous callback model
	 *
	 * @var WPPF_Hook_profiler_model
	 */
	private $callback_model;


	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_start( $a ) {

		$this->hook_model             = new WPPF_Hook_profiler_model();
		$this->hook_model->request_id = WPPF_REQUEST_ID;
		$this->hook_model->name       = current_filter();
		$this->hook_model->is_hook    = true;
		$this->hook_model->time       = microtime( true );

		$this->hook_model->save();

		return $a;
	}

	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_end( $a ) {

		if ( $this->hook_model ) {
			$this->hook_model->duration = microtime( true ) - $this->hook_model->time;
			$this->hook_model->save();
		}

		return $a;

	}

	/**
	 * Fetch hooks and add callbacks to measure
	 * Compiling time
	 */
	private function retrieve_wp_hooks() {

		/**
		 * Taking global hooks variable
		 */
		global $wp_filter;

		foreach ( $wp_filter as $name => &$hook ) {

			if ( ! isset( $this->_mutex[ $name ] ) ) {
				$this->_mutex[ $name ] = true;

				$hook->callbacks = array(
					                   PHP_INT_MIN => isset( $hook->callbacks[ PHP_INT_MIN ] ) ? $hook->callbacks[ PHP_INT_MIN ] : array()
				                   ) + $hook->callbacks;

				$hook->callbacks[ PHP_INT_MAX ] = isset( $hook->callbacks[ PHP_INT_MAX ] ) ? $hook->callbacks[ PHP_INT_MAX ] : array();

				$hook->callbacks[ PHP_INT_MIN ] = array(
					                                  'DevLog_hook_start' => array(
						                                  'function'      => array( $this, 'hook_start' ),
						                                  'accepted_args' => 1
					                                  )
				                                  ) + $hook->callbacks[ PHP_INT_MIN ];

				$hook->callbacks[ PHP_INT_MAX ] = array(
					                                  'DevLog_hook_end' => array(
						                                  'function'      => array( $this, 'hook_end' ),
						                                  'accepted_args' => 1

					                                  )
				                                  ) + $hook->callbacks[ PHP_INT_MAX ];

				/**
				 * Fetching Hook callbacks
				 *
				 * @var WP_Hook $hook
				 */
				foreach ( $hook->callbacks as $priority => &$callbacks ) {
					$_callbacks = $callbacks;
					foreach ( $_callbacks as $index => &$callback ) {

						if ( strpos( $index, 'DevLog_' ) !== 0 ) {
							self::array_insert( $callbacks, $index, [
								'DevLog_CB_' . $index => [
									'function'      => function ( $a ) use ( $index ) {

										if ( $this->callback_model ) {
											$this->callback_model->duration = microtime( true ) - $this->callback_model->time;
											$this->callback_model->save();
										}

										/*
										 * Getting declaration coordinates
										 * File and line
										 */
										$cat = '';
										if ( function_exists( $index ) ) {
											$reflFunc = new ReflectionFunction( $index );
											$cat      = str_replace( ABSPATH, '',
													$reflFunc->getFileName() ) . ':' . $reflFunc->getStartLine();
										} else {
											$index = "?";
										}

										$this->callback_model             = new WPPF_Hook_profiler_model();
										$this->callback_model->request_id = WPPF_REQUEST_ID;
										$this->callback_model->name       = $index;
										$this->callback_model->is_hook    = false;
										$this->callback_model->time       = microtime( true );
										$this->callback_model->parent_id  = $this->hook_model->id;
										$this->callback_model->file       = $cat;
										$this->callback_model->save();

										self::retrieve_wp_hooks();

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


	/**
	 * @param $log_name
	 *
	 * @throws Exception
	 */
	public static function endpointResults( $log_name ) {

//		$log = Log::get( [ 'data', 'messages' ], [
//			[
//				'logs.name',
//				'=',
//				$log_name
//			]
//		] )->one();
//
//
//		self::render( 'page-hook-profiler', [
//			'log' => $log,
//		] );

	}

}
