<?php

namespace DevLog;

use Exception;

class DevLogHelper {


	/**
	 * @param $server
	 *
	 * @return mixed
	 */
	public static function getUserIpAddressFromServer( $server ) {
		$server = (array) $server;

		if ( isset( $server['HTTP_X_SUCURI_CLIENTIP'] ) ) {
			$ip = $server['HTTP_X_SUCURI_CLIENTIP'];
		} elseif ( isset( $server['HTTP_CLIENT_IP'] ) ) {
			$ip = $server['HTTP_CLIENT_IP'];
		} elseif ( isset( $server['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $server['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $server['HTTP_X_FORWARDED'] ) ) {
			$ip = $server['HTTP_X_FORWARDED'];
		} elseif ( isset( $server['HTTP_FORWARDED_FOR'] ) ) {
			$ip = $server['HTTP_FORWARDED_FOR'];
		} elseif ( isset( $server['HTTP_FORWARDED'] ) ) {
			$ip = $server['HTTP_FORWARDED'];
		} elseif ( isset( $server['REMOTE_ADDR'] ) ) {
			$ip = $server['REMOTE_ADDR'];
		} else {
			$ip = 'UNKNOWN';
		}

		return $ip;
	}

	/**
	 * @param $server
	 *
	 * @return string
	 */
	public static function getActualUrlFromServer( $server ) {
		return ( isset( $server['HTTPS'] ) && $server['HTTPS'] === 'on' ? "https" : "http" )
		       . "://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
	}

	/**
	 * @param $server
	 *
	 * @return bool
	 */
	public static function isXHRFromServer( $server ) {
		if ( isset( $server['HTTP_X_REQUESTED_WITH'] ) && strtolower( $server['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ) {
			return true;
		}

		return false;
	}


	/**
	 * @param $items
	 *
	 * @param $params
	 *
	 * @return string
	 */
	public static function getMenu( $items, $params ) {

		$result = '<div class="list-group">';

		$result .= self::getMenuItems( $items, isset( $params['items'] ) ? $params['items'] : [] );

		$result .= "</div>";

		return $result;

	}

	/**
	 * @param $items
	 * @param $params
	 *
	 * @return string
	 */
	public static function getMenuItems( $items, $params ) {

		$result = '';

		foreach ( $items as $index => $item ) {
			$_params = $params;
			$url     = isset( $item['url'] ) ? $item['url'] : false;
			if ( $url ) {
				$_params['href'] = $url;
			}

			if ( self::compareUrls( self::getActualUrlFromServer( $_SERVER ), $url ) ) {
				$_params['class'] .= " active";
			}

			$label = isset( $item['label'] ) ? $item['label'] : '';

			$result .= '<a ' . self::getHtmlParams( $_params ) . '>' . $label . '</a>';
		}

		return $result;
	}


	/**
	 * @param array $params
	 *
	 * @return string
	 */
	public static function getHtmlParams( array $params ) {
		return join( ' ', array_map( function ( $key ) use ( $params ) {
			if ( is_bool( $params[ $key ] ) ) {
				return $params[ $key ] ? $key : '';
			}

			return $key . '="' . $params[ $key ] . '"';
		}, array_keys( $params ) ) );
	}


	/**
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 */
	public static function compareUrls( $a, $b ) {
		$_a = parse_url( $a );

		$_b = parse_url( $b );

		$__a = [
			'path'  => isset( $_a['path'] ) && isset( $_b['path'] ) ? $_a['path'] : '',
			'query' => isset( $_a['query'] ) ? $_a['query'] : ''
		];

		$__b = [
			'path'  => isset( $_b['path'] ) && isset( $_a['path'] ) ? $_b['path'] : '',
			'query' => isset( $_b['query'] ) ? $_b['query'] : ''
		];


		return $__a == $__b;
	}


	/**
	 * @param $mem_usage
	 *
	 * @return string
	 */
	public static function getMemUsageReadable( $mem_usage ) {

		if ( $mem_usage < 1024 ) {
			return $mem_usage . " b";
		} elseif ( $mem_usage < 1048576 ) {
			return round( $mem_usage / 1024, 2 ) . " kb";
		} else {
			return round( $mem_usage / 1048576, 2 ) . " mb";
		}
	}

	/**
	 * @param $string
	 * @param int $limit
	 * @param string $end
	 *
	 * @return string
	 */
	public static function trimString( $string, $limit = 40, $end = '...' ) {
		return ( strlen( $string ) > $limit ) ? substr( $string, 0, $limit ) . $end : $string;
	}


	/**
	 * @param $phpInfo
	 *
	 * @return string|string[]|null
	 */
	public static function phpInfoCleaner( $phpInfo ) {

		$phpInfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms', '$1', $phpInfo );
		$phpInfo = preg_replace( '%(\<table.*?)(\>)%ms', '$1 class="table table-condensed table-bordered table-striped table-sm table-hover" $2', $phpInfo );

		return $phpInfo;

	}


	/**
	 * @param $array
	 *
	 * @param string $title
	 * @param int $depth
	 *
	 * @return string
	 */
	public static function arrayToHtmlTable( $array ) {
		if ( ! empty( $array ) ) {
			return "<table class=\"dev-log-auto-table table table-condensed table-bordered table-striped table-hover\">" . self::arrayToHtmlTableGroup( $array ) . "</table>";
		} else {
			return "<span>Empty.</span>";
		}
	}

	/**
	 * @param $array
	 *
	 * @param $title
	 * @param int $depth
	 *
	 * @return string
	 */
	public static function arrayToHtmlTableGroup( $array ) {
		$result = '';
		if ( ! empty( $array ) ) {
			$result .= self::_arrayToHtmlTableRows( $array );
		}

		return $result;
	}

	/**
	 * @param $array
	 * @param int $depth
	 *
	 * @return string
	 */
	private static function _arrayToHtmlTableRows( $array, $depth = 0 ) {
		$array  = (array) $array;
		$result = "";

		foreach ( $array as $key => $item ) {
			if ( is_string( $item ) || is_numeric( $item ) ) {
				$result .= "<tr><td class=\"align-middle\">$key</td><td>$item</td></tr>";
			} elseif ( is_array( $item ) || is_object( $item ) ) {
				if ( ! empty( $item ) ) {
					$depth ++;
					$result .= "<tr><td class=\"align-middle\">$key</td><td class=\"sub-table-column\">" . self::arrayToHtmlTable( $item ) . "</td></tr>";
				}
			}
		}

		return $result;

	}

	/**
	 * @param int $coreCount
	 * @param int $interval
	 *
	 * @return float
	 */
	public static function getCpuUsage( $coreCount = 2, $interval = 1 ) {
		$rs       = sys_getloadavg();
		$interval = $interval >= 1 && 3 <= $interval ? $interval : 1;
		$load     = $rs[ $interval ];

		return round( ( $load * 100 ) / $coreCount, 2 );
	}

	/**
	 * @param $code
	 *
	 * @return string
	 */
	public static function getHttpStatusBadge( $code ) {
		$statuses = [
			404 => 'danger',
			200 => 'success',
			300 => 'primary',
		];
		$class    = isset( $statuses[ $code ] ) ? $statuses[ $code ] : 'warning';

		return '<span class="badge badge-pill badge-' . $class . '">' . $code . '</span>';

	}

	public static function ipAddressValidation( $ip_addresses ) {
		if ( in_array( '*', $ip_addresses ) || in_array( DevLogHelper::getUserIpAddressFromServer( $_SERVER ), $ip_addresses ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $ip
	 * @param $ranges
	 *
	 * @return bool
	 */
	public static function ipInRange( $ip, $ranges ) {
		if(is_string($ranges)){
			$ranges = [$ranges];
		}
		foreach($ranges as $range){
			if ( strpos( $range, '/' ) == false ) {
				$range .= '/32';
			}
			// $range is in IP/CIDR format eg 127.0.0.1/24
			list( $range, $netmask ) = explode( '/', $range, 2 );
			$range_decimal = ip2long( $range );
			$ip_decimal = ip2long( $ip );
			$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
			$netmask_decimal = ~ $wildcard_decimal;
			if ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) ){
				return true;
			}
		}
		return false;
	}


	/**
	 * @param $message
	 *
	 * @param string $method
	 *
	 * @return false|string
	 */
	public static function dump( $message, $method = 'dump' ) {
		if ( ! is_string( $message ) ) {
			if ( $method == 'dump' ) {
				ob_start();
				var_dump( $message );
				$message = ob_get_clean();
			} elseif ( $method == 'export' ) {
				$message = var_export( $message, true );
			} elseif ( $method == 'table' ) {
				$message = self::arrayToHtmlTable( $message );
			}
		}

		return $message;
	}

	public static function deleteDir( $dirPath ) {
		if ( ! is_dir( $dirPath ) ) {
			return false;
		}
		if ( substr( $dirPath, strlen( $dirPath ) - 1, 1 ) != '/' ) {
			$dirPath .= '/';
		}
		$files = glob( $dirPath . '*', GLOB_MARK );
		foreach ( $files as $file ) {
			if ( is_dir( $file ) ) {
				self::deleteDir( $file );
			} else {
				unlink( $file );
			}
		}
		if ( rmdir( $dirPath ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $json
	 *
	 * @param bool $assoc
	 *
	 * @return array|object
	 * @throws Exception
	 */
	public static function jsonDecode( $json, $assoc = true ) {
		$val = json_decode( $json, $assoc );
		if ( $assoc == true && ! is_array( $val ) ) {
			throw new Exception( 'Could not decode JSON' );
		} elseif ( $assoc == false && ! is_object( $val ) ) {
			throw new Exception( 'Could not decode JSON' );
		}

		return $val;
	}

	/**
	 * @param $array
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function jsonEncode( $array ) {
		$val = json_encode( $array );
		if ( $val === false || is_null( $val ) ) {
			throw new Exception( 'Could not encode JSON' );
		}

		return $val;
	}

}