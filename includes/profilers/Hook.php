<?php

namespace WPPF\profilers;

use Exception;
use ReflectionFunction;
use WP_Hook;

class Hook extends Profiler {

	const PREFIX = "WPPF_HP_";

	public static function getName() {
		return __( 'Hook Profiler', 'wppf' );
	}

	/**
	 * @throws \yii\db\Exception
	 */
	public function run() {

		$transaction = hook\models\Hook::getDb()->beginTransaction();

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
					'href'   => '/?' . self::getSlug() . '-view&endpoint=Results&request_id=' . WPPF_REQUEST_ID,
					'meta'   => array( 'target' => '_blank' )
				) );
			} );
	}


	private $_mutex = [];


	/**
	 * Previous hook model
	 *
	 * @var hook\models\Hook
	 */
	private $hook_model;

	/**
	 * Previous callback model
	 *
	 * @var hook\models\Hook
	 */
	private $callback_model;

	private $hook_models = [];

	private $callback_models = [];

	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_start( $a ) {

		$model             = new hook\models\Hook();
		$model->request_id = WPPF_REQUEST_ID;
		$model->name       = current_filter();
		$model->is_hook    = true;
		$model->time       = microtime( true );


		if ( ( $callback = end( $this->callback_models ) ) !== false ) {
			$model->parent_id = $callback->id;
		}

		$model->save();

		$this->hook_models[] = $model;

		return $a;
	}

	/**
	 * @param $a
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function hook_end( $a ) {
		$model = array_pop( $this->hook_models );

		if ( $model ) {

			$model->duration = microtime( true ) - $model->time;

			$model->save();

			$callback = array_pop( $this->callback_models );

			if ( $callback ) {
				$callback->duration = microtime( true ) - $callback->time;
				$callback->save();
			}

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
					                                  self::PREFIX . 'hook_start' => array(
						                                  'function'      => array( $this, 'hook_start' ),
						                                  'accepted_args' => 1
					                                  )
				                                  ) + $hook->callbacks[ PHP_INT_MIN ];

				$hook->callbacks[ PHP_INT_MAX ] = array(
					                                  self::PREFIX . 'hook_end' => array(
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

						if ( strpos( $index, self::PREFIX ) !== 0 ) {

							/**
							 * Insert callback after current callback
							 * */
							self::array_insert( $callbacks, $index, [
								self::PREFIX . 'CB_' . $index => [
									'function'      => function ( $a ) use ( $index ) {

										$model = array_pop( $this->callback_models );
										if ( $model ) {
											$model->duration = microtime( true ) - $model->time;
											$model->save();
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

										$model             = new hook\models\Hook();
										$model->request_id = WPPF_REQUEST_ID;
										$model->name       = $index;
										$model->is_hook    = false;
										$model->time       = microtime( true );
										$model->parent_id  = end( $this->hook_models )->id;
										$model->file       = $cat;
										$model->save();

										$this->callback_models[] = $model;

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
	 * @param \WPPF\profilers\hook\models\Hook $hook
	 */
	private static function retHook( $hook ) {
		echo "<li>" . ( $hook->is_hook ? "HOOK - " : "CALLBACK - " ) . $hook->name;

		if ( $hook->getChilds()->count() > 0 ) {
			echo "<ul>";
			foreach ( $hook->getChilds()->all() as $child ) {
				self::retHook( $child );
			}
			echo "</ul>";
		}
		echo "</li>";
	}

	/**
	 * @param $log_name
	 *
	 * @throws Exception
	 */
	public static function endpointResults( $request_id ) {

		$hooks = hook\models\Hook::find()->where( [ 'request_id' => $request_id ] )->andWhere( [ 'parent_id' => null ] )->all();
		echo "<ul>";
		foreach ( $hooks as $hook ) {
			self::retHook( $hook );
		}
		echo "</ul>";
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
