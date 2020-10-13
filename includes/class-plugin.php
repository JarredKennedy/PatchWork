<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' )  ) {
	exit;
}

class Plugin implements Asset {

	protected $name;

	protected $version;

	protected $id;

	protected $type;

	protected $slug;

	public function __construct( $name, $version, $id, $type, $slug ) {
		
	}

	public function get_name() {

	}

	public function get_version() {

	}

	public function get_id() {
		
	}

	public function get_type() {
		return 'plugin';
	}

	public function get_slug() {
		
	}

}