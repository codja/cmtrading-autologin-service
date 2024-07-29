<?php

namespace cmas\classes\providers;


use cmas\classes\core\Error;
use cmas\classes\helpers\Request_Api;
use cmas\interfaces\Autologin;

class Antelope implements Autologin {

	public const BASE_URL_API = 'https://api.cmtrading.com/SignalsServer/api/';

	public function get_autologin_link( $param ): ?string {
		if ( ! $param ) {
			return null;
		}

		$body = [
			'userId' => absint( $param ),
			'apikey' => ANTILOPE_API_AFFILIATE_KEY,
		];

		$response = Request_Api::send_api(
			self::BASE_URL_API . 'regenerateUserAutologinUrl'. '?' . http_build_query( $body ),
			[],
			'POST',
			$this->get_headers()
		);

		if ( isset( $response['error'] ) ) {
			$description   = $response['error']['errorDesc'] ?? '';
			$error_log_msg = ' request_id[' . ( $response['requestId'] ?? '' ) . ']: ' . $description . ' ' . ( $response['error']['errorDetails'] ?? '' );
			Error::instance()->log_error( 'Antelope_Api', $error_log_msg );

			return null;
		}

		return $response['result'] ?? null;
	}

	private function get_headers(): array {

		return [
			'Content-Type'    => 'application/json',
			'Accept'          => 'application/json',
		];
	}

}
