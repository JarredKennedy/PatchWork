<?php

use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase {

	public function test_estimate_cdh_size() {
		$file_tree = include SUPPORT_DIR . 'plugin-file-tree.php';
		$file_tree = unserialize( $file_tree );

		/*
		=========================================================
		FILE NAME										LENGTH
		=========================================================
		some-plugin/										12
		some-plugin/file-a.php								22
		some-plugin/other-file.txt							26
		some-plugin/lang/									17
		some-plugin/lang/some-plugin.pot					32
		some-plugin/includes/								21
		some-plugin/includes/something.jpeg					35
		some-plugin/includes/functions.php					34
		some-plugin/includes/admin/							27
		some-plugin/includes/admin/menus.php				36
		some-plugin/includes/admin/list-table.php			41
		=========================================================
										TOTAL (files)		303
										TOTAL (CDH heads)	506
										TOTAL (CDH)			809
		=========================================================
		*/
		$expected_cdh_size = 809;

		$estimated_cdh = patchwork_estimate_cdh_size( $file_tree );

		$this->assertIsInt( $estimated_cdh );
		$this->assertEquals( $expected_cdh_size, $estimated_cdh );
	}

	public function test_cdh_to_file_tree() {
		$cdh_list = include SUPPORT_DIR . 'plugin-cdh-extracted.php';
		$cdh_list = unserialize( $cdh_list );

		$expected_tree = include SUPPORT_DIR . 'plugin-file-tree.php';
		$expected_tree = unserialize( $expected_tree );

		$file_tree = patchwork_cdh_to_file_tree( $cdh_list );

		$this->assertEquals( $expected_tree, $file_tree );
	}

}