<?php
namespace WPPF\profilers;

use Exception;

class Request extends Profiler {

	public static function getName() {
		return __( 'Request Profiler', 'wppf' );
	}

	public $url_exclusions = '';

	public static function formFields() {

		return [
			'url_exclusions' => [ 'type' => 'textarea' ]
		];

	}

	/**
	 * @throws Exception
	 */
	public function init() {
        return;
		self::registerEndpoints();

		/*
		 * Split exclusions string to array
		 * */
		preg_match_all( '/^.+$/m', $this->url_exclusions, $matches );

		foreach ( $matches[0] as $exclusion ) {

			/*
			 * Clear whitespace
			 * */
			$exclusion = trim( $exclusion, ' ' );
			$url       = DevLogHelper::getActualUrlFromServer( $_SERVER );
			if ( preg_match( "/$exclusion/", $url ) ) {
				return;
			}
		}

		/*
		 * Dont show profiler if
		 * is wordpress ajax endpoint
		 * */
		if ( wp_doing_ajax() ) {
			return;
		}

		register_shutdown_function( function () {
			$data = DevLog::getLog();

			$server = $data->getDataList()->getData( '_server' )->getValue( 3 );
			?>

            <div id="<?php echo self::class; ?>" class="<?php echo self::class; ?>">
                <div class="endpoint"></div>
                <table>
                    <tr>
                        <td>
                            Hash: <?php echo $data->getName(); ?>
                        </td>

                        <td>
                            URL: <?php echo DevLogHelper::getActualUrlFromServer( $server ); ?>
                        </td>

                        <td>
                            Method: <?php echo $server['REQUEST_METHOD']; ?>
                        </td>

                        <td>
                            Status: <?php echo $data->getDataList()->getData( 'status' )->getValue(); ?>
                        </td>

                        <td>
                            <div class="flybox">
                                <table>
                                    <tr>
                                        <td>Start Time</td>
                                        <td><?php echo $data->getDataList()->getData( 'start_time' )->getValue(); ?></td>
                                    </tr>
                                    <tr>
                                        <td>End Time</td>
                                        <td><?php echo $data->getDataList()->getData( 'end_time' )->getValue(); ?></td>
                                    </tr>
                                </table>
                            </div>
                            Time: <?php echo round( $data->getDataList()->getData( 'end_time' )->getValue() -
							                        $data->getDataList()->getData( 'start_time' )->getValue(), 5 ); ?>s
                        </td>

                        <td>
                            Mem: <?php echo DevLogHelper::getMemUsageReadable( $data->getDataList()->getData( 'memory_usage' )->getValue() ); ?>
                        </td>

                        <td class="messages">
                            <div class="flybox">
                                <table>
									<?php foreach ( $data->getMessageList()->getList() as $key => $message ): ?>
                                        <tr>
                                            <td><?php echo $key; ?></td>
                                            <td><?php echo $message->getType(); ?></td>
                                            <td><?php echo $message->getMessage(); ?></td>
                                            <td><?php echo $message->getCategory(); ?></td>
                                        </tr>
										<?php if ( $key == 50 ): ?>
                                            <tr>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>View more...</td>
                                            </tr>
											<?php break; endif; ?>
									<?php endforeach; ?>
                                </table>
                            </div>
                            <a href="?<?php echo self::class . '_view&endpoint=ViewMessages&log_name=' . $data->getName(); ?>">
                                Messages: <?php echo count( $data->getMessageList()->getList() ); ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <style>
                #WPPF_Request_Profiler {
                    position: fixed;
                    bottom: 0;
                    width: 100%;
                    background: #4b4b4b;
                    color: #fff;
                    z-index: 999999;
                }

                #WPPF_Request_Profiler table {
                    margin: 0;

                }

                #WPPF_Request_Profiler table tr,
                #WPPF_Request_Profiler table td {
                    border: none;
                    color: #fff;
                    text-align: center;
                }

                #WPPF_Request_Profiler table tr {
                    border-top: solid #000;
                }

                #WPPF_Request_Profiler table td + td {
                    border-left: solid #000;
                }

                #WPPF_Request_Profiler .flybox {
                    display: none;
                    overflow: auto;
                    position: absolute;
                    bottom: 35px;
                    right: 0;
                    width: 100%;
                    height: auto;
                    max-height: 50vh;
                    background: #000000;
                }

                #WPPF_Request_Profiler .flybox table tr {
                    border: solid 1px;
                }

                #WPPF_Request_Profiler .flybox table td {
                    border: solid 1px;
                }

                #WPPF_Request_Profiler table td:hover .flybox {
                    display: block;
                }

                #WPPF_Request_Profiler a {
                    color: #ff9c9c;
                }
            </style>

			<?php

		} );

	}


	/**
	 * Messages endpoint
	 *
	 * @param $log_name
	 *
	 * @throws Exception
	 */
	public static function endpointViewMessages( $log_name ) {


	}


}
