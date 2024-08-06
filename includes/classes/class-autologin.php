<?php

namespace cmas\classes;

use cmas\classes\core\Error;
use cmas\classes\helpers\Helpers;
use cmas\classes\helpers\Location;
use cmas\classes\providers\Antelope;

class Autologin {

	const DEFAULT_DOMAIN = 'https://www.cmtrading.com';

	const DEFAULT_LANGUAGE = 'en';

	const ENDPOINTS = [
		'autologin'     => 'account_no',
		'autologin_new' => 'customer_id',
	];

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'autologin' ] );
	}

	public function autologin(): void {
		if ( ! Error::instance()->is_form_enabled() ) {
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

		if ( ! $this->check_blacklist_ip() ) {
			wp_die( esc_html__( 'Forbidden. Contact the administrator.', 'cmtrading-autologin' ) );
		}

		$email      = sanitize_email( $_GET['emailaddress'] ?? '' );
		$account_no = sanitize_text_field( $_GET['account_no'] ?? '' );
		$action     = sanitize_text_field( $_GET['action'] ?? '' );
		$lang       = sanitize_text_field( $_GET['lang'] ?? self::DEFAULT_LANGUAGE );

		// TODO: temporary changes
		wp_redirect( 'https://myaccount.cmtrading.com/#/login' );
		exit;

		$start_panda = microtime( true );

		$fields        = 'account_no, accountid';
		$user_data     = CRM_DB::instance()->get_user_register_data( 'email', $email, $fields );
		$db_account_no = $user_data[ 'account_no' ] ?? null;
		$account_id    = $user_data[ 'accountid' ] ?? null;

		$panda_diff      = wp_sprintf( '%.6f sec.', microtime( true ) - $start_panda );
		Error::instance()->log_error( 'CRM DB Time', $panda_diff );

		if ( is_null( $db_account_no ) || is_null( $account_id ) || ! $this->is_account_no_match( $db_account_no, $account_no ) ) {
			wp_die( esc_html__( 'CRM DB error. Account not found.', 'cmtrading-autologin' ), '', [ 'response' => 403 ] );
		}

		$provider          = new Antelope();
		$link_for_redirect = $provider->get_autologin_link( $account_id );

		if ( ! $link_for_redirect ) {
			wp_die( esc_html__( 'CRM API error. Contact the administrator.', 'cmtrading-autologin' ), '', [ 'response' => 403 ] );
		}

		$total_diff = wp_sprintf( '%.6f sec.', microtime( true ) - $start );
		Error::instance()->log_error( 'Total Time', $total_diff );

		wp_redirect( $link_for_redirect );
		exit;
	}

	private function is_dates_match( string $user_registered, string $registration_date ): bool {
		if ( ! $user_registered || ! $registration_date ) {
			return false;
		}

		$date_without_time = explode( ' ', $user_registered, 2 );
		return reset( $date_without_time ) === $registration_date;
	}

	private function is_account_no_match( string $user_id, string $account_no ): bool {
		if ( ! $user_id || ! $account_no ) {
			return false;
		}

		return $user_id === $account_no;
	}

	private function check_blacklist_ip(): bool {
		$ip_visitor    = Location::get_ip();
		$black_list_ip = Helpers::get_array( get_field( 'cmas_autologin_ip_black_list', 'option' ) );

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

	private function get_login_by_lang( string $lang ) {
		$lang              = $lang ? $lang : self::DEFAULT_LANGUAGE;
		$lang_domains_list = Helpers::get_array( get_field( 'cmas_matching_lang_list', 'option' ) );

		if ( ! $lang_domains_list ) {
			return self::DEFAULT_DOMAIN;
		}

		foreach ( $lang_domains_list as $lang_domain_list ) {
			$lang_param = $lang_domain_list['param_lang_value'] ?? null;
			$domain     = $lang_domain_list['param_lang_domain'] ?? null;

			if ( ! $lang_param || ! $domain ) {
				continue;
			}

			if ( $lang_param === $lang ) {
				return $domain;
			}

		}

		return self::DEFAULT_DOMAIN;
	}
}
