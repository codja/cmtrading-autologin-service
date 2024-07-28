<?php

namespace cmas\classes;

use cmas\traits\Singleton;

class CRM_DB {

	use Singleton;

	public function get_user_register_data( string $column, string $value, string $fields = 'account_no' ): ?array {
		if ( ! $column || ! $value ) {
			return null;
		}

		$crm_db = self::instance()->db();

		if ( ! $crm_db ) {
			return null;
		}

		$base_request = $crm_db->get_results(
			$crm_db->prepare(
				"SELECT $fields FROM vtiger_account WHERE $column = %s",
				sanitize_text_field( $value )
			),
			ARRAY_A
		);

		$crm_db->close();

		return $base_request
			? reset( $base_request )
			: null;
	}

	private function db(): ?\Wpdb {
		if ( ! $this->check_constants() ) {
			return null;
		}

		$panda_db = new \Wpdb(
			CRM_DB_USER,
			CRM_DB_PASS,
			CRM_DB_NAME,
			CRM_DB_HOST
		);

		if ( ! $panda_db->check_connection() ) {
			return null;
		}

		return $panda_db;
	}

	private function check_constants(): bool {
		return defined( 'CRM_DB_USER' )
		&& defined( 'CRM_DB_PASS' )
		&& defined( 'CRM_DB_NAME' )
		&& defined( 'CRM_DB_HOST' );
	}

}

