<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Update_Protection {

	public static function start_service() {
		add_filter( 'auto_update_plugin', [ self::class, '' ] );
	}

	// public static function 

}