<?php

namespace cmas\classes;

use cmas\classes\core\Error;
use cmas\classes\external_api\panda\Authorization;
use cmas\classes\helpers\Helpers;
use cmas\classes\helpers\Location;
use cmas\classes\helpers\Request_Api;

class Autologin {

	const ENDPOINTS = [
		'autologin'     => 'account_no',
		'autologin_new' => 'customer_id',
	];

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'autologin' ] );
	}

	public function autologin(): void {
		if ( ! Error::instance()->is_defined_constants() ) {
			return;
		}
		$start = microtime( true );
		$url   = trim(
			wp_parse_url( $_SERVER['REQUEST_URI'] )['path'],
			'/'
		);

		if ( ! array_key_exists( $url, self::ENDPOINTS ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$redirect = sanitize_text_field( $_GET['redirect'] ?? '' );
			wp_safe_redirect( "/$redirect" );
			exit;
		}

		$error_redirect = '/webtrader/?action=forexLogin';

		if ( ! $this->check_blacklist_ip() ) {
			wp_safe_redirect( $error_redirect );
			exit;
		}

		$email      = sanitize_email( $_GET['emailaddress'] ?? '' );
		$account_no = sanitize_text_field( $_GET['account_no'] ?? '' );
		$action     = sanitize_text_field( $_GET['action'] ?? '' );

		$start_panda     = microtime( true );
		$user_registered = Panda_DB::instance()->get_user_register_data( 'email', $email, self::ENDPOINTS[ $url ] );
		$panda_diff      = wp_sprintf( '%.6f sec.', microtime( true ) - $start_panda );
		Error::instance()->log_error( 'Panda DB Time', $panda_diff );

		if ( is_null( $user_registered ) || ! $this->is_account_no_match( $user_registered[ self::ENDPOINTS[ $url ] ], $account_no ) ) {
			wp_safe_redirect( $error_redirect );
			exit;
		}

		$auth     = new Authorization();
		$response = Request_Api::send_api(
			$auth::BASE_URL_API . 'system/loginToken',
			wp_json_encode(
				[
					'email' => $email,
				]
			),
			'POST',
			[
				'Authorization' => $auth->get_auth_data(),
				'Content-Type'  => 'application/json',
			]
		);

		if ( ! $response ) {
			wp_safe_redirect( $error_redirect );
			exit;
		}

		if ( isset( $response['error'] ) ) {
			$description = $response['error'][0]['description'] ?? '';
			$request_id  = $response['requestId'] ?? '';
			Error::instance()->log_error( 'class-endpoint-error', "[$request_id] $description" );
		}

		$link_for_redirect = Request_Api::get_response_link(
			$response['data']['url'] ?? '',
			'action',
			true,
			$action
		);

		$total_diff = wp_sprintf( '%.6f sec.', microtime( true ) - $start );
		Error::instance()->log_error( 'Total Time', $total_diff );

		wp_safe_redirect( $link_for_redirect );
		exit;
	}

	private function is_dates_match( string $user_registered, string $registration_date ): bool {
		if ( ! $user_registered || ! $registration_date ) {
			return false;
		}

		$date_without_time = explode( ' ', $user_registered, 2 );
		return reset( $date_without_time ) === $registration_date;
	}

	private function is_account_no_match( string $user_registered, string $account_no ): bool {
		if ( ! $user_registered || ! $account_no ) {
			return false;
		}

		return $user_registered === $account_no;
	}

	private function check_blacklist_ip(): bool {
		$ip_visitor    = Location::get_ip();
		$black_list_ip = Helpers::get_array( get_field( 'rgbc_authform_ip_black_list', 'option' ) );

		$result = true;
		foreach ( $black_list_ip as $not_allowed_ip ) {
			$not_allowed_ip = is_array( $not_allowed_ip )
				? reset( $not_allowed_ip )
				: $not_allowed_ip;

			if ( rest_is_ip_address( (string) $not_allowed_ip ) && $ip_visitor === $not_allowed_ip ) {
				$result = false;
				break;
			}
		}

		return $result;
	}
}
