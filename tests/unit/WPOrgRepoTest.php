<?php

class WPOrgRepoTest extends PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider package_provider
	 */
	public function test_read_package_cdh( $package_url, $cdh_size, $num_entries ) {

		$wp_repo = new PatchWork\WP_Org_Repo();

		$cdh_list = $wp_repo->read_package_cdh( $package_url, $cdh_size );
		
		$this->assertIsArray( $cdh_list );
		$this->assertContainsOnlyInstancesOf( PatchWork\Types\Zip_CDH::class, $cdh_list );
		$this->assertCount( $num_entries, $cdh_list );
	}

	public function package_provider() {
		return [
			[ 'https://downloads.wordpress.org/plugins/gn-publisher.1.0.5.zip', 1361, 17 ],
			[ 'https://downloads.wordpress.org/theme/astra.2.5.5.zip', 53649, 531 ],
			[ 'https://downloads.wordpress.org/theme/storefront.2.7.0.zip', 19393, 209 ],
			[ 'https://downloads.wordpress.org/plugin/google-sitemap-generator.4.1.1.zip', 7823, 87 ]
		];
	}

}