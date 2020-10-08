<?php

class RepositoryAssetSourceTest extends PHPUnit\Framework\TestCase {

	public function test_get_file_tree() {

		$asset_info = include SUPPORT_DIR . 'plugin-info.php';
		$expected_file_tree = include SUPPORT_DIR . 'plugin-file-tree.php';
		$expected_file_tree = unserialize( $expected_file_tree );

		$asset = $this->getMockBuilder( PatchWork\Asset::class )
			->setMethods(['get_id', 'get_version', 'get_name', 'get_type', 'get_slug'])
			->getMock();

		$asset->expects( $this->once() )
			->method('get_version')
			->will( $this->returnValue( '1.2.3' ) );

		$repository = $this->getMockBuilder( PatchWork\WP_Org_Repo::class )
			->setMethods(['get_asset_info', 'list_files'])
			->getMock();

		$repository->expects( $this->once() )
			->method('get_asset_info')
			->with( $this->equalTo( $asset ) )
			->will( $this->returnValue( $asset_info ) );

		$repository->expects( $this->once() )
			->method('list_files')
			->with(
				$this->equalTo( $asset ),
				$this->equalTo( $asset_info['download_link'] )
			)
			->will( $this->returnValue( $expected_file_tree ) );

		$source = new PatchWork\Asset_Source\Repository_Asset_Source( $asset, $repository );

		$file_tree = $source->get_file_tree();

		$this->assertEquals( $expected_file_tree, $file_tree );
	}

}