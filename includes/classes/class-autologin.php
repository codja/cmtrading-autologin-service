<?php

namespace cmas\classes;

use cmas\classes\core\Error;
use cmas\classes\helpers\Helpers;
use cmas\classes\helpers\Location;
use cmas\classes\helpers\Request_Api;
use cmas\classes\providers\Antelope;

class Autologin {

	const DEFAULT_DOMAIN = 'https://www.cmtrading.com';

	const DEFAULT_LINK = 'https://myaccount.cmtrading.com/#/login';

	const DEFAULT_LANGUAGE = 'en';

	const ENDPOINTS = [
		'autologin'       => 'account_no',
		'autologin_new'   => 'customer_id',
		'simpleautologin' => '',
	];

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'autologin' ] );
	}

	/**
	 * Handles the autologin functionality based on the requested URL and user status.
	 *
	 * @return void
	 */
	public function autologin(): void {
		if ( ! Error::instance()->is_form_enabled() ) {
			return;
		}

		$url = trim( wp_parse_url( $_SERVER['REQUEST_URI'] )['path'] ?? '', '/' );

		if ( ! $url || ! array_key_exists( $url, self::ENDPOINTS ) ) {
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

		$platform         = sanitize_text_field( $_GET['platform'] ?? '' );
		$asset            = sanitize_text_field( $_GET['asset'] ?? '' );
		$is_platform_link = $platform === 'Webtrader' && ! empty( $asset );

		$user_data           = CRM_DB::instance()->get_user_register_data( 'email', $email, 'customer_id, accountid' );
		$db_customer_id      = $user_data['customer_id'] ?? null;
		$account_id          = $user_data['accountid'] ?? null;
		$is_simple_autologin = $url === 'simpleautologin';

		// Ensure the user data is valid
		if ( is_null( $db_customer_id ) || is_null( $account_id ) || ( ! $is_simple_autologin && ! $this->is_account_no_match( $db_customer_id,
					$account_no ) ) ) {
			wp_redirect( esc_url_raw( self::DEFAULT_LINK ) );
			exit;
		}

		$provider          = new Antelope();
		$link_for_redirect = null;

		// Attempt to get the SSO link if applicable
		if ( $is_platform_link ) {
			$link_for_redirect = $this->get_sso_link( $provider, $asset, $account_id );
		}

		// If no SSO link, fall back to autologin link
		if ( ! $link_for_redirect ) {
			$link_for_redirect = $provider->get_autologin_link( $account_id );
		}

		// If no valid link is found, redirect to the default link
		if ( ! $link_for_redirect ) {
			$link_for_redirect = self::DEFAULT_LINK;
		}

		wp_redirect( esc_url_raw( $link_for_redirect ) );
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

	/**
	 * Generate the SSO link to the webtrader
	 *
	 * @param Antelope $provider
	 * @param $account_id
	 *
	 * @return string|null
	 */
	private function get_sso_link( Antelope $provider, $asset, $account_id ): ?string {
		if ( ! $account_id
			|| ! $asset
			|| ! defined( 'SSO_WEBTRADER_URL' )
			|| ! defined( 'SSO_WEBTRADER_TOKEN' )
			|| ! defined( 'SSO_WEBTRADER_REDIRECT_URL' )
		) {
			return null;
		}

		// get all of the client trading accounts
		$trading_accounts = Helpers::get_array( $provider->get_trading_accounts( $account_id ) );
		// we will use the last one that had update
		$last_trading_account = $this->get_last_trading_account( $trading_accounts );
		// from the returned result we need to get the externalID
		$external_id = $last_trading_account['externalId'] ?? null;
		if ( ! $external_id ) {
			return null;
		}

		$response_from_sso = Request_Api::send_api(
			SSO_WEBTRADER_URL,
			[
				'login' => (int) $external_id,
			],
			'POST',
			[
				'Content-Type'  => 'application/json',
				'Authorization' => SSO_WEBTRADER_TOKEN,
			],
			true
		);
		if ( ! $response_from_sso ) {
			return null;
		}

		$sso_token = $response_from_sso['__token'] ?? null;
		if ( ! $sso_token ) {
			return null;
		}

		return esc_url_raw( SSO_WEBTRADER_REDIRECT_URL . $asset . '&token=' . $sso_token );
	}

	/**
	 * We take all the records for whom brokerName === Live and select the most recent one by lastUpdateTime,
	 * if there is no live, then simply by lastUpdateTime
	 *
	 * @param array $trading_accounts
	 *
	 * @return array|null
	 */
	private function get_last_trading_account( array $trading_accounts ): ?array {
		$best_match = null;

		// We are looking for an array with brokerName === 'Live' and the largest lastUpdateTime
		foreach ( $trading_accounts as $item ) {
			if ( ! isset( $item['brokerName'] ) || ! isset( $item['lastUpdateTime'] ) ) {
				continue;
			}

			if ( $item['brokerName'] === 'Live' ) {
				if ( $best_match === null || $item['lastUpdateTime'] > $best_match['lastUpdateTime'] ) {
					$best_match = $item;
				}
			}
		}

		// If array with brokerName === 'Live' is not found, look for the largest lastUpdateTime among all
		if ( $best_match === null ) {
			foreach ( $trading_accounts as $item ) {
				if ( ! isset( $item['lastUpdateTime'] ) ) {
					continue;
				}

				if ( $best_match === null || $item['lastUpdateTime'] > $best_match['lastUpdateTime'] ) {
					$best_match = $item;
				}
			}
		}

		return $best_match;
	}
}
