<?php

namespace PatchWork\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Menu {

	private static $instance;

	public static function init() {

	}

	public static function get_instance() {
		return self::$instance;
	}

}