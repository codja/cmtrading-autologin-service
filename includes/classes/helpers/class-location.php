<?php

namespace cmas\classes\helpers;

abstract class Location {

	public static function get_ip() {
		// If website is hosted behind CloudFlare protection.
		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && filter_var( $_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		if ( isset( $_SERVER['X-Real-IP'] ) && filter_var( $_SERVER['X-Real-IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return $_SERVER['X-Real-IP'];
		}

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = trim( current( explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );

			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return $ip;
			}
		}

		return $_SERVER['REMOTE_ADDR'];
	}

}
