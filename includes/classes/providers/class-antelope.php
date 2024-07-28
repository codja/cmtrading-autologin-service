<?php

namespace cmas\classes\providers;


use cmas\classes\helpers\Request_Api;
use cmas\interfaces\Autologin;

class Antelope implements Autologin {

	public const BASE_URL_API = 'https://api.cmtrading.com/SignalsServer/api/';

	public function get_autologin_link( $param ): ?string {
		if ( ! $param ) {
			return null;
		}

		$body = [
			'userid' => absint( $param ),
			'apikey' => ANTILOPE_API_AFFILIATE_KEY,
		];

		$response = Request_Api::send_api(
			self::BASE_URL_API . 'getUser'. '?' . http_build_query( $body ),
			[],
			'POST',
			$this->get_headers()
		);

		return $response['result']['brokerLoginUrl'] ?? null;
	}

	private function get_headers(): array {

		return [
			'Content-Type'    => 'application/json',
			'Accept'          => 'application/json',
		];
	}

}
