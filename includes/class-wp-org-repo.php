<?php

namespace PatchWork;

use PatchWork\Types\Zip_CDH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Org_Repo {

	const API_URL = 'https://api.wordpress.org/';

	protected $cdh_entries;

	public function __construct() {
		$this->cdh_entries = array();
	}

	public function get_asset_info( Asset $asset ) {
		$cached = get_transient( 'pw_wporg_asset_info_' . $asset->get_id() );

		if ( $cached ) {
			return $cached;
		}

		$url = self::API_URL;
		$url .= ( $asset->get_type() === 'plugin' ) ? 'plugins' : 'themes';
		$url .= '/info/1.0/';
		$url .= $asset->get_id();
		$url .= '.json';

		$api_response = wp_remote_get( $url );

		$info = wp_remote_retrieve_body( $api_response );
		$info = json_decode( $info, true );

		set_transient( 'pw_wporg_asset_info_' . $asset->get_id(), $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}

}