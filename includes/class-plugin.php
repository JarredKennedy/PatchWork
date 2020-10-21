<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' )  ) {
	exit;
}

class Plugin implements Asset {

	protected $name;

	protected $version;

	protected $id;

	protected $slug;

	protected $status;

	protected $path;

	public function __construct( $name, $version, $id, $slug, $status, $path ) {
		$this->name = $name;
		$this->version = $version;
		$this->id = $id;
		$this->slug = $slug;
		$this->status = $status;
		$this->path = $path;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_version() {
		return $this->version;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_type() {
		return 'plugin';
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_status() {
		return $this->status;
	}

	public function get_path() {
		return $this->path;
	}

}