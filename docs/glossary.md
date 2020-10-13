# PatchWork Glossary
This document explains some PatchWork concepts.

## Anonymous Plugin
An anonymous plugin is a plugin created by PatchWork for the purpose of loading custom code which has no target asset. PatchWork does not need to be active
for anonymous plugins to function.

## Asset
An asset is a theme, plugin or anonymous plugin. Assets are the _target_ of a patch.

## Patch
A patch is a file which contains information about how one or more files are transformed into other files. A patch also contains:  
- Patch format version
- Target asset identifier
- Vendor ID io.patchwork.PatchWork
- Deprecation trigger version
- Signature
- Author Name
- Author URL
- Description
- Date Created

### Patch Format Version
One byte char identifying the version of the PatchWork patch format used when the patch was created. Used by the patch reader to retrieve data from the patch file.

### Target Asset Identifier
Variable length string which identifies a release of an asset. It is composed of the asset type, slug and version. Example: `plugin:patchwork:1.0.0`  
If the patch creates an anonymous plugin then the asset type is `anon` and the slug is empty

### Deprecation Trigger Version
This is an optional 30 byte string which holds a version expression. Example `>=1.2.3`  
When a new version of the asset targeted by this patch is available, if the version of that asset satisfies the expression, the patch will be deactivated.

---

## Scanning
Scanning is the process of checking the files of installed plugins and themes for changes. The process obtains the unmodified package of the asset, usually from wordpress.org,
and checks if any of the local files differ from those in the original. If there are differences, these can be extracted into one or more patch files.

## Patch Extraction
Patch extraction is the process of creating patch files from changes made to files.

## Patch Deprecation
Patch deprecation is the conditional retirement (deactivation) of a patch. The deprecation triggers are always based on changes to the target asset, thus patch deprecation is not applicable to Anonymous Plugins. There are two paths which can lead to a patch being deactivated:
1. **Deprecation Trigger Version** This field in the patch header can contain a version expression which, if satisfied when tested against the new target asset version, will deprecate the patch.
2. **Deprecation Policy** This field in the patch header determines what (if any) check to perform when a new version of the target asset is available. It can perform no check, a check to determine whether the changes in the patch are also in the new version of the target asset or a check to determine whether the changes in the patch are similar to code in the new version of the target asset.

Deprecated patches cannot be reactivated unless the patch's target asset is restored to the version targeted by the patch.

## Update Protection
_Update Protection enables a theme or plugin to be updated, without losing changes made to that theme or plugin, which is pretty much this plugin's value proposition._

Update Protection is a service provided by the plugin to prevent assets from updating if patches targeting that asset cannot be applied to a newer version of the asset. Put simply, if you've patched a plugin or theme, and there is a new version of that plugin or theme, and that new version modifies any of the code you've patched, Update Protection will prevent that new version being installed while the patch is active. Update Protection will detect if the new version of the asset changes any code already modified by an active patch, if there is a conflict, the system will indicate that the asset is being prevented from updating due to a patch conflict. If the new version of the asset satisfies the _patch deprecation trigger_ expression for all patches targeting the asset, then Update Protection does not check for conflicts and will allow the update to proceed and subsequently the patches targeting the asset will be deactivated.

## Patch Similarity
Two sets of changes are considered similar if any of the following conditions are true:
- The changes are identical without considering whitespace, or
- The more recent set of changes is a superset of the patch changes.

---

## Central Directory Header (CDH)
A central directory header is series of bytes in a zip archive describing a file or directory within the archive. There is one CDH for each file and directory. A CDH entry contains:  
- A checksum for the file
- The length of the file name and the file name itself
- The compressed and uncompressed size of the file
- The byte offset of where the local file header for this file can be found in the archive
- See .ZIP File Format Specification section 4.3.12 for a full list of fields