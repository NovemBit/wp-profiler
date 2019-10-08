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


		add_action( 'shutdown', function () {
			var_dump( $this->a );
			var_dump( $this->b );
		} );
		/*		global $wp_filter;
				echo "<pre>";
				var_dump(array_chunk($wp_filter,10)[20]);
				echo "</pre>";die;*/

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

	public $a = [ 0, 0 ];
	public $b = [ 0, 0 ];

	/**
	 * @param $hook_name
	 * @param $callback_name
	 * @param $first
	 * @param $last
	 *
	 * @throws \ReflectionException
	 */
	private function write( $hook_name, $callback_name, $first, $last ) {

		$callback = array_pop( $this->callback_models );

		if ( $callback ) {
			$callback->duration = microtime( true ) - $callback->time;
			$callback->save();
		}

		if ( $first ) {
			$this->a[0] ++;
			$hook             = new hook\models\Hook();
			$hook->request_id = WPPF_REQUEST_ID;
			$hook->name       = $hook_name;
			$hook->is_hook    = true;
			$hook->time       = microtime( true );

			if ( $callback ) {
				$hook->parent_id = $callback->id;
			}

			$this->hook_models[] = $hook;
		}

		/*
		 * Getting declaration coordinates
		 * File and line
		 */
		$function_file = '';
		if ( function_exists( $callback_name ) ) {
			$reflection_function = new ReflectionFunction( $callback_name );
			$function_file       = str_replace( ABSPATH, '', $reflection_function->getFileName() ) . ':' . $reflection_function->getStartLine();
		} else {
			$callback_name = "?";
		}

		$model             = new hook\models\Hook();
		$model->request_id = WPPF_REQUEST_ID;
		$model->name       = $callback_name;
		$model->is_hook    = false;
		$model->time       = microtime( true );
		$model->parent_id  = end( $this->hook_models )->id;
		$model->file       = $function_file;
		$model->save();
		$this->callback_models[] = $model;

		if ( $last ) {
			$this->a[1] ++;
			$hook = array_pop( $this->hook_models );
			if ( $hook ) {
				$hook->duration = microtime( true ) - $hook->time;
				$hook->save();
			}

			$callback = array_pop( $this->callback_models );
			if ( $callback ) {
				$callback->duration = microtime( true ) - $callback->time;
				$callback->save();
			}

		}


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

		$start = false;

		foreach ( $wp_filter as $hook_name => &$hook ) {

			if ( ! $start ) {
				if ( $hook_name == 'plugins_loaded' ) {
					$start = true;
				} else {
					continue;
				}
			}

			reset( $hook->callbacks );
			$first_priority = key( $hook->callbacks );

			end( $hook->callbacks );
			$last_priority = key( $hook->callbacks );

			foreach ( $hook->callbacks as $priority => &$callbacks ) {

				reset( $callbacks );
				$first_callback_name = key( $callbacks );


				end( $callbacks );
				$last_callback_name = key( $callbacks );

				foreach ( $callbacks as $callback_name => $callback ) {

					if (
						! isset( $callbacks[ self::PREFIX . "CB_" . $callback_name ] ) &&
						strpos( $callback_name, self::PREFIX ) !== 0
					) {

						$first = $priority == $first_priority && $callback_name == $first_callback_name;
						$last  = $priority == $last_priority && $callback_name == $last_callback_name;

						/*if ( $first ) {
							$this->b[0] ++;
						}
						if ( $last ) {
							$this->b[1] ++;
						}*/

						/**
						 * Insert callback after current callback
						 * */
						self::array_insert( $callbacks, $callback_name,
							[
								self::PREFIX . 'CB_' . $callback_name => [

									'function'      => function ( $a ) use ( $hook_name, $callback_name, $first, $last ) {

										$this->write( $hook_name, $callback_name, $first, $last );

										self::retrieve_wp_hooks();

										return $a;
									},
									'accepted_args' => 1

								]
							]
						);

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
