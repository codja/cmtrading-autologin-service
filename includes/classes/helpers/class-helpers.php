<?php

namespace cmas\classes\helpers;

abstract class Helpers {

	/**
	 * Trim string or return second argument if string is empty
	 *
	 * @param $value
	 * @param string $return_if_not_string
	 *
	 * @return mixed|string
	 */
	public static function trim_string( $value, string $return_if_not_string = '' ) {
		if ( ! is_string( $value ) ) {
			return $return_if_not_string;
		}

		return trim( $value );
	}

	/**
	 * Check if value is array and return it or return an empty array if value is not array
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public static function get_array( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		return $value;
	}

}
