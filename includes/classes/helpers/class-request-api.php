<?php

namespace cmas\classes\helpers;

/*
 * Class for send remote request
*/

use cmas\classes\core\Error;

abstract class Request_Api {

	/**
	 * Send remote request, get response
	 *
	 * @param string $url
	 * @param mixed $body
	 * @param string $method
	 * @param array $headers
	 *
	 * @return false|mixed
	 */
	public static function send_api(
		string $url,
		$body = [],
		string $method = 'GET',
		array $headers = [],
		bool $body_json = false
	) {
		$request = wp_remote_request(
			$url,
			[
				'headers' => $headers,
				'method'  => $method,
				'body'    => $body_json ? wp_json_encode( $body ) : $body,
				'timeout' => 90,
			],
		);

		if ( is_wp_error( $request ) ) {
			Error::instance()->log_error( 'request_api', $request->get_error_message() );
			return false;
		} else {
			return json_decode( wp_remote_retrieve_body( $request ), true );
		}
	}

	public static function get_response_link( $url, $param = '', $change = false, $changeable_value = '' ): string {
		if ( ! $url ) {
			return get_home_url();
		}

		$base_url = wp_parse_url( $url );
		parse_str( $base_url['query'], $parameters );

		if ( $change && $changeable_value ) {
			$parameters[ $param ] = $changeable_value;
		} elseif ( $param && isset( $parameters[ $param ] ) ) {
			unset( $parameters[ $param ] );
		}

		$new_query = http_build_query( $parameters );
		$path      = wp_is_mobile() ? '/mobile/' : $base_url['path'];

		return $path . '?' . $new_query;
	}
}
