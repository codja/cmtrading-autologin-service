<?php

namespace cmas\classes\external_api\panda;

class Authorization {

	public const BASE_URL_API = 'https://cmtrading.pandats-api.io/api/v3/';

	public function get_auth_data(): string {
		return 'Bearer ' . $this->get_jwt_token();
	}

	private function get_access_key(): string {
		return sha1( PANDA_PARTNER_ID . time() . PANDA_PARTNER_SECRET_KEY );
	}

	private function authorization() {
		$data = [
			'partnerId' => PANDA_PARTNER_ID,
			'time'      => time(),
			'accessKey' => $this->get_access_key(),
		];

		return Request_Api::send_api(
			self::BASE_URL_API . 'authorization',
			wp_json_encode( $data ),
			'POST',
			[
				'Content-Type' => 'application/json',
			]
		);
	}

	private function get_jwt_token() {
		$exist_token = get_option( 'panda_token' ) ? json_decode( get_option( 'panda_token' ), true ) : false;

		if ( $exist_token && $exist_token['expire'] > time() ) {
			return $exist_token['token'];
		}

		$authorization = $this->authorization();

		if ( ! $authorization ) {
			wp_send_json_error( __( 'Error on client server. Check Request_Api log', 'rgbcode-authform' ) );
		}

		if ( isset( $authorization['error'] ) ) {
			wp_send_json_error( $authorization['error'][0]['description'] );
		}

		$token_data = [
			'token'  => $authorization['data']['token'],
			'expire' => strtotime( $authorization['data']['expire'] ),
		];

		update_option( 'panda_token', wp_json_encode( $token_data ), false );

		return $token_data['token'];
	}

}
