<?php

namespace cmas\classes\core;

use cmas\traits\Singleton;

class Error {

	use Singleton;

	public function __construct() {
		add_action( 'admin_notices', [ $this, 'notice_required_settings' ] );
	}

	public function log_error( string $title, string $error ): void {
		error_log(
			'[' . gmdate( 'Y-m-d H:i:s' ) . '] Error: {' . $title . ':' . $error . "} \n===========\n",
			3,
			CMAS_AUTOLOGIN_PLUGIN_PATH . 'errors.log'
		);
	}

	public function notice_required_settings(): void {
		if ( ! $this->is_defined_constants() ) {
			printf(
				'<div class="notice notice-error"><h3>%s</h3><p>%s</p></div>',
				esc_html( 'Cmtrading Autologin:' ),
				esc_html__( 'You need add constants PANDA_PARTNER_ID and PANDA_PARTNER_SECRET_KEY in wp_config.', 'rgbcode-authform' ),
			);
		}
	}

	public function is_defined_constants(): bool {
		return defined( 'PANDA_PARTNER_ID' ) && defined( 'PANDA_PARTNER_SECRET_KEY' );
	}

	public function is_form_enabled(): bool {
		$enable = get_field( 'cmas_autologin_enable', 'option' );
		return $enable && $this->is_defined_constants();
	}
}
