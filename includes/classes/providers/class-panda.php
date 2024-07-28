<?php

namespace cmas\classes\providers;

use cmas\classes\core\Error;
use cmas\classes\external_api\panda\Authorization;
use cmas\classes\helpers\Request_Api;
use cmas\interfaces\Autologin;

class Panda implements Autologin {

	private Authorization $auth;

	public function __construct() {
		$this->auth = new Authorization();
	}

	public function get_autologin_link( string $email ): ?string {
		$email = is_email( $email ) ? sanitize_email( $email ) : null;
		if ( ! $email ) {
			return null;
		}

		$response = Request_Api::send_api(
			$this->auth::BASE_URL_API . 'system/loginToken',
			wp_json_encode(
				[
					'email' => $email,
				]
			),
			'POST',
			[
				'Authorization' => $this->auth->get_auth_data(),
				'Content-Type'  => 'application/json',
			]
		);

		if ( isset( $response['error'] ) ) {
			$description = $response['error'][0]['description'] ?? '';
			$request_id  = $response['requestId'] ?? '';
			Error::instance()->log_error( 'panda-error', "[$request_id] $description" );
		}

		return Request_Api::get_response_link(
			$response['data']['url'] ?? '',
		);
	}

}
