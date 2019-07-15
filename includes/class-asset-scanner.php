<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for scanning assets to detect changes.
 * 
 * Procedure is resource and time intensive and so can
 * be completed in chunks. The process is:
 * 	1.	Obtain the original asset zip (usually from wordpress.org)
 *		for the installed version of the asset.
 * 	2.	Check the files in the zip against the files in the asset
 * 		path for changes.
 *	3.	If there are changes, that asset is marked as modified and
 *		and the next stage will extract a patch from those changes.
 *		If there are no changes, the asset is ignored.
 */
class Asset_Scanner {

	protected $start_time	= 0;

	protected $end_time		= 0;

	protected $assets		= [];

	protected $state		= self::STATE_STARTED;

	protected $errors		= [];

	public function __construct( $scan_parameters = [] ) {
		
	}

	public function scan_next() {
		$asset = array_shift( $this->assets );

		
	}

}