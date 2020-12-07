# Writing Patches

## Creating a Patch
A PatchWork Patch is an instance of `PatchWork\Patch`. A Patch instance has two dependencies, the Patch Header and an array of Diffs. The Patch Header records which theme or plugin the patch patches, information about the patch author, and other metadata. A Patch Header is an instance of `PatchWork\Types\Patch_Header` which is essentially a struct (a class without methods). A Patch Diff is an an instance of `PatchWork\Diff` which contains the operations (OPs) performed on one version of a file to transform it into another version of the same file. An OP is an instance of `PatchWork\Types\Diff_Op`, a class containing the lines added and lines removed. A Patch Diff contains one OP for each set of changes that occur on sequential lines in a file.

```
Code Structure of a PatchWork Patch Object

PatchWork\Patch
    PatchWork\Types\Patch_Header
    
    [
        PatchWork\Diff
            [
                PatchWork\Types\Diff_OP
                ...
                PatchWork\Types\Diff_OP
            ]
        ...
        PatchWork\Diff
            [
                PatchWork\Types\Diff_OP
                ...
                PatchWork\Types\Diff_OP
            ]
    ]
    
```

Example: Creating a Patch

```php
$target_asset_identifier = 'plugin:some-plugin/some-plugin.php:1.0.1';

$asset = patchwork_get_asset( $target_asset_identifier );

// Make the Patch Header //

$patch_header = new \PatchWork\Types\Patch_Header();

$patch_header->format_version = PATCHWORK_USE_PATCH_VERSION;
$patch_header->target_asset_identifier = $asset->get_id();
$patch_header->author_name = 'Some Person';
$patch_header->author_url = 'https://some-person.test';
$patch_header->vendor_id = 'test.some-person';
$patch_header->name = 'Fixes something in Some Plugin';
$patch_header->description = "There was a thing that was broken, this patch fixes that thing.";

// Make the Patch Diffs //

// Get an Asset Source for the installed version of a plugin.
$installed_source = new \PatchWork\Asset_Source\Local_Asset_Source( $asset->get_path() );
// Get an Asset Source for a modified copy of the same plugin.
$fixed_source = new \PatchWork\Asset_source\Local_Asset_Source( './my-projects/some-plugin-fixed' );

// Find which files differ between the installed plugin source and the modified plugin source.
$changed_files = patchwork_diff_file_trees( $installed_source->get_file_tree(), $fixed_source->get_file_tree() );

// Get a differ. The default differ will do.
$differ = patchwork_get_differ();

$diffs = patchwork_diff_files( $changed_files, $installed_source, $fixed_source, $differ );

// Finally, create the Patch instance.
$patch = new \PatchWork\Patch( $patch_header, $diffs );
```

## Writing a Patch to a File
PatchWork defines an interface (includes/class-patch-writer.php) that a compliant Patch Writer will implement. This interface defines a method with the following signature:

```php
public function write( PatchWork\Patch $patch, string $patch_file_path, bool $overwrite_existing = false );
```

You can use the helper function `patchwork_get_patch_writer( PATCHWORK_USE_PATCH_VERSION )` to get a patch writer instance. With this instance you can pass an instance of `PatchWork\Patch` which is the patch that will be written, the absolute path to the file the patch will be written to, and a boolean to set whether the function can overwrite an existing file.

Example: Writing a Patch

```php
$patch_writer = patchwork_get_patch_writer( PATCHWORK_USE_PATCH_VERSION );

try {
    $patch_writer->write( $patch, PATCHWORK_PATCH_DIR . '/some-plugin-fix.pwp', true );
} catch ( \Exception $error ) {
    // Handle the error.
}

```