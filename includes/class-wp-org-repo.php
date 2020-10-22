<?php

namespace PatchWork;

use PatchWork\Types\Zip_CDH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Org_Repo {

	const API_URL = 'https://api.wordpress.org/';

	public function __construct() {
	}

	public function get_asset_info( Asset $asset ) {
		$cached = get_transient( 'pw_wporg_asset_info_' . $asset->get_id() );

		if ( $cached ) {
			return $cached;
		}

		$url = self::API_URL;
		$url .= ( $asset->get_type() === 'plugin' ) ? 'plugins' : 'themes';
		$url .= '/info/1.0/';
		$url .= $this->get_repo_slug( $asset );
		$url .= '.json';

		$api_response = wp_remote_get( $url );

		$info = wp_remote_retrieve_body( $api_response );
		$info = json_decode( $info, true );

		if ( isset( $info['sections'] ) ) {
			unset( $info['sections'] );
		}

		set_transient( 'pw_wporg_asset_info_' . $asset->get_id(), $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}

	public function get_repo_slug( Asset $asset ) {
		$slug = $asset->get_slug();

		if ( false === strpos( $slug, '/' ) ) {
			$slug = basename( $slug, '.php' );
		} else {
			$slug = dirname( $slug );
		}

		return $slug;
	}

}