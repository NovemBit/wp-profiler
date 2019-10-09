<?php

namespace WPPF\profilers;

use Exception;
use ReflectionFunction;
use WP_Hook;

class Hook extends Profiler {

	const PREFIX = "WPPF_HP_";

	/**
	 * @return mixed|string|void
	 */
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
	 * @param $callback_name
	 *
	 * @throws \ReflectionException
	 */
	private function write( $callback_name ) {

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
		if ( function_exists( $callback_name ) ) {
			$reflFunc = new ReflectionFunction( $callback_name );
			$cat      = str_replace( ABSPATH, '',
					$reflFunc->getFileName() ) . ':' . $reflFunc->getStartLine();
		} else {
			$callback_name = "?";
		}

		$model             = new hook\models\Hook();
		$model->request_id = WPPF_REQUEST_ID;
		$model->name       = $callback_name;
		$model->is_hook    = false;
		$model->time       = microtime( true );
		$model->parent_id  = end( $this->hook_models )->id;
		$model->file       = $cat;
		$model->save();

		$this->callback_models[] = $model;
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


			$hook->callbacks = [ PHP_INT_MIN => isset( $hook->callbacks[ PHP_INT_MIN ] ) ? $hook->callbacks[ PHP_INT_MIN ] : [] ] + $hook->callbacks;

			$hook->callbacks[ PHP_INT_MAX ] = isset( $hook->callbacks[ PHP_INT_MAX ] ) ? $hook->callbacks[ PHP_INT_MAX ] : [];

			$hook->callbacks[ PHP_INT_MIN ] = [
				                                  self::PREFIX . 'hook_start' => [
					                                  'function'      => [ $this, 'hook_start' ],
					                                  'accepted_args' => 1
				                                  ]
			                                  ] + $hook->callbacks[ PHP_INT_MIN ];

			/**
			 * Adding Max priority haystack
			 * */
			$hook->callbacks[ PHP_INT_MAX ][ self::PREFIX . 'hook_end' ] = [
				'function'      => [ $this, 'hook_end' ],
				'accepted_args' => 1
			];

			/**
			 * Fetching Hook callbacks
			 *
			 * @var WP_Hook $hook
			 */
			foreach ( $hook->callbacks as $priority => &$callbacks ) {
				foreach ( $callbacks as $callback_name => &$callback ) {

					if (
						! isset( $callbacks[ self::PREFIX . 'CB_' . $callback_name ] ) &&
						strpos( $callback_name, self::PREFIX ) !== 0
					) {

						/**
						 * Insert callback after current callback
						 * */
						self::array_insert( $callbacks, $callback_name, [
							self::PREFIX . 'CB_' . $callback_name => [
								'function'      => function ( $a ) use ( $callback_name ) {
									$this->write( $callback_name );
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
		echo ($hook->is_hook && $hook->parent_id == null) ? " - ". round($hook->duration,5).'s' : '';
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
