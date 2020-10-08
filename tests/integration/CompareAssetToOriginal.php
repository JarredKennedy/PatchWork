<?php

class CompareAssetToOriginalTest extends PHPUnit\Framework\TestCase {

	/**
	 * Tests the use-case:
	 * 	1. Make Asset
	 * 	2. Make asset local source
	 * 	3. Make asset repo source
	 * 	4. Get file tree for local source
	 * 	5. Get file tree for repo source
	 * 	6. Detect changed files between the local and remote
	 * 	7. Obtain orignal versiosn of changed files from the repo
	 * 	8. Produce a diff for each changed file
	 */
	public function test_comapre_local_plugin_to_repo_plugin() {
		
	}

}