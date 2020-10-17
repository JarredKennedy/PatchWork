<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Writeable_Asset_Source extends Asset_Source {

	public function mkdir( $path, $recursive = false );

	public function get_file( $file_path, $create = false );

	public function delete_file( $file_path );

}