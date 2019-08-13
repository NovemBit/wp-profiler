<?php
defined( 'ABSPATH' ) || exit;

use DevLog\DevLog;
use DevLog\DevLogHelper;

class WPPF_Request_Profiler extends WPPF_Profiler_Base {

	public $url_exclusions = '';

	public function init() {

		preg_match_all( '/^.+$/m', $this->url_exclusions,$matches);
		foreach ( $matches[0] as $exclusion ) {
			$exclusion = trim($exclusion,' ');
			$url = DevLogHelper::getActualUrlFromServer( $_SERVER );
			if ( preg_match( "/$exclusion/", $url ) ) {
			    return;
			}
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		register_shutdown_function( function () {
			$data = DevLog::getLog();

			$server = $data->getDataList()->getData( '_server' )->getValue( 3 );
			?>

            <div id="<?php echo self::class; ?>" class="<?php echo self::class; ?>">
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
                            Time: <?php echo round( $data->getDataList()->getData( 'end_time' )->getValue() -
							                        $data->getDataList()->getData( 'start_time' )->getValue(), 5 ); ?>s
                        </td>

                        <td>
                            Mem: <?php echo DevLogHelper::getMemUsageReadable( $data->getDataList()->getData( 'memory_usage' )->getValue() ); ?>
                        </td>

                        <td>
                            Messages: <?php echo count( $data->getMessageList()->getList() ); ?>
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
            </style>

			<?php

		} );

	}


}
