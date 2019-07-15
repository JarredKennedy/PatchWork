<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility to scan through zip archive from wordpress.org and compare
 * CRC32 checksums to determine which files have changed without having
 * to extract the files from the archive.
 * 
 * TODO: Account for renamed/moved files.
 * 
 * @since 0.1.0
 */
class Zip_Diff {

	const FILE_STATUS_UNCHANGED	= 0;
	const FILE_STATUS_MODIFIED	= 1;
	const FILE_STATUS_RENAMED	= 2;
	const FILE_STATUS_ADDED		= 3;
	const FILE_STATUS_REMOVED	= 4;

	/**
	 * @var string Path to archive
	 */
	protected $archive_path;

	/**
	 * @var string Path containing the files of the asset
	 */
	protected $asset_path;

	/**
	 * @var array Checksums extracted from the archive indexed by file paths
	 */
	protected $archive_checksums = [];

	/**
	 * @var array List of paths (files and directories) which should not be tracked by PatchWork
	 */
	protected $excluded_paths;

	public function __construct( $archive_path, $asset_path ) {
		$this->archive_path = $archive_path;
		$this->asset_path = trailingslashit( $asset_path );

		$this->excluded_paths = patchwork_get_untracked_paths();
	}

	/**
	 * Returns an array of files, both in the asset path and in the archive. The
	 * array is indexed by file path relative to the asset path and the values are
	 * one of the FILE_STATUS constants defined above.
	 * 
	 * @since 0.1.0
	 * 
	 * @return array
	 */
	public function get_file_statuses() {
		$all_files = array_merge( array_keys( $this->local_checksums ), array_keys( $this->archive_checksums ) );

		return array_reduce( $all_files, function( $file_statuses, $file ) {
			if ( ! isset( $this->local_checksums[$file] ) ) {
				$file_statuses[$file] = self::FILE_STATUS_ADDED;
			} elseif ( ! isset( $this->local_checksums[$file] ) ) {
				$file_statuses[$file] = self::FILE_STATUS_REMOVED;
			} elseif ( true ) {
				$file_statuses[$file] = self::FILE_STATUS_MODIFIED;
			} else {
				$file_statuses[$file] = self::FILE_STATUS_UNCHANGED;
			}

			return $file_statuses;
		}, [] );
	}

	/**
	 * Reads through the archive and extracts the CRC32 checksums.
	 */
	protected function extract_archive_checksums() {
		$file = @fopen( $this->archive_path, 'rb' );

		if ( $file !== false ) {
			while ( fread( $file, 12 ) !== false ) {

			}
		} else {
			return new WP_Error( 'archive_path_unreadable', __( 'Cannot read archive', 'patchwork' ) );
		}

		fclose( $file );
	}

}