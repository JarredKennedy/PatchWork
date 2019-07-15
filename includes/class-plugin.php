<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' )  ) {
	exit;
}

class Plugin extends Asset {

	const ID_PREFIX = 'wpplugin';

	protected $type = 'plugin';

	protected $name;

	public function __construct( $plugin_slug ) {
		
	}

	public function get_name() {

	}

	public function get_version() {

	}

}