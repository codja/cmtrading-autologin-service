<?php

namespace cmas;

use cmas\classes\Autologin;
use cmas\classes\core\Error;
use cmas\classes\plugins\ACF;

class Autoloader {

	public static function register() {
		spl_autoload_register(
			function ( $class ) {
				$class       = str_replace( __NAMESPACE__, 'includes', $class );
				$parse_class = explode( '\\', $class );

				switch ( true ) {
					case in_array( 'traits', $parse_class, true ):
						$type = 'trait';
						break;
					case in_array( 'interfaces', $parse_class, true ):
						$type = 'interface';
						break;
					default:
						$type = 'class';
				}

				$file_class_name = $type . '-' . strtolower( str_replace( '_', '-', array_pop( $parse_class ) ) ) . '.php';
				$class           = implode( DIRECTORY_SEPARATOR, $parse_class ) . DIRECTORY_SEPARATOR . $file_class_name;
				$file_path       = CMAS_AUTOLOGIN_PLUGIN_PATH . $class;

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					return true;
				}

				return false;
			}
		);
	}

	public static function start(): void {
		new ACF();
		new Autologin();
		Error::instance();
	}
}

Autoloader::register();
Autoloader::start();
