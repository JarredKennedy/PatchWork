<?php

namespace PatchWork;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * An anonymous plugin is a single-file plugin created by PatchWork
 * to include custom code. It can be managed activated, deactivated
 * & uninstalled like any other WordPress plugin. The plugin remains
 * when PatchWork is deactivated or uninstalled.
 * 
 * This asset is the target of patches which are not modifications
 * to themes, plugins or any other asset.
 */
class Anon_Plugin extends Plugin {

	public function get_type() {
		return 'anon';
	}

}