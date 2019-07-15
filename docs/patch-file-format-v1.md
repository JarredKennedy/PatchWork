# Patch File Format v1
_All integer values are unsigned, unless otherwise indicated._

## Header
The header section is the first section of the patch file.
| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| H.0 | Patch file magic number | char | The magic number identifies the file as a PatchWork patch file | 1F 50 41 54 43 48 1A | 7 | 1F 50 41 54 43 48 1A |
| H.1 | Patch file format version | char | The patch file format version of the data in the patch file | 1 | 1 | 1 |
| H.2 | Diff blocks offset | short | The byte offset of the start of the diff block section | _variable_ | 2 | 264 |
| H.3 | Target asset identifier length | char | The length of the Target Asset Identifier (H.5) | _variable_ | 1 | 22 |
| H.4 | Target asset identifier | string | String identifying the asset this patch targets | _variable_ | _value of H.2_ | plugin:patchwork:1.0.0 |
| H.5 | Vendor ID length | char | The length of the vendor ID field (H.6) | _variable_ | 1 | 23 |
| H.6 | Vendor ID | string | The vendor ID. String representing the vendor in reverse domain name notation | _variable_ | _value of H.4_ | app.patchwork.PatchWork |
| H.7 | Deprecation trigger version | string | A version expression. When the target asset version satisfies this expression, the patch is deactivated | _variable_ | 30 | >=1.1.0, next, 1.0.1 |
| H.8 | Deprecation policy | char | Determines which deprecation check to use when an update introduces changes to any line(s) changed by the patch. _Note: Update Protection is not enforced for deactivated patches._ | **0** The patch is not deactivated regardless of changes<br> **1** The patch will be deactivated if the set of changes in the patch are identically matched in the updated asset<br> **2** The patch will be deactivated if the set of changes in the patch are similarly matched in the updated asset. (see [Glossary: Similarity](./glossary.md#Patch-Similarity)) | 1 | 2 |
| H.9 | Signature | char | Signature for the patch file, signed with the PatchWork private key | _variable_ | 128 | --- |
| H.10 | Author name length | char | The length of the author field (H.10) | _variable_ | 1 | 9 |
| H.11 | Author name | string | The author of the patch | _variable_ | _value of H.8_ | PatchWork |
| H.12 | Author url length | char | The length of the author url (H.12) | _variable_ | 1 | 21 |
| H.13 | Author url | string | The url of the patch author | _variable_ | _value of H.10_ | https://patchwork.dev |
| H.14 | Patch description length | char | The length of the patch description field (H.14) | _variable_ | 1 | 12 |
| H.15 | Patch description | string | A description of what the patch does | _variable_ | _value of H.12_ | Does a thing |
| H.16 | Patch creation timestamp | long | 32bit unix timestamp representing when the patch was created | _variable_ | 4 | 1546300800 |

## Diff Blocks
A patch file contains at least one diff block. There is one diff block per changed file. Diff blocks have a header which contains some metadata, such as the name of the file.
The rest of the diff block contains a series of changes made to the file. There is one change entry for every consecutive series of changed lines. Each change entry contains a list
of changed lines. Changed lines for the original and the modified version are included so the patch is able to be undone.

### Diff Block: Header
| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| DBH.0 | Block length | short | The length of the diff block (including the header and this field) | _variable_ | 2 | 1012 |
| DBH.1 | Original file path length | short | The length of the original file path (DBH.2) | _variable_ | 2 | 43 |
| DBH.2 | Original file path | string | The original file path | _variable_ | _value of DBH.1_ | /wp-content/plugins/patchwork/patchwork.php |
| DBH.3 | Patched file path length | short | The length of the patched file path (DBH.4) | _variable_ | 2 | 43 |
| DBH.4 | Patched file path | string | The patched file path. This is usually the same as DBH.2, except in the case the file was renamed | _variable_ | _value of DBH.3_ | /wp-content/plugins/patchwork/patchwork.php |
| DBH.5 | Changed lines count | short | The number of lines this block modifies | _variable_ | 2 | 20 |

### Diff Block: Body
Contains a list of line change entries, which are consecutive changed lines.  
**Line Change Entry**
| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| DBB.0 | Original file start line | short | Number of the line which marks the first changed line in this entry in the original file | _variable_ | 2 | 100 |
| DBB.1 | Original file changed lines | short | Number of the lines changed for this entry in the original file | _variable_ | 2 | 100 |
| DBB.2 | Patched file start line | short | Number of the line which marks the first changed line in this entry in the patched file | _variable_ | 2 | 100 |
| DBB.3 | Patched file changed lines | short | Number of the lines changed for this entry in the patched file | _variable_ | 2 | 100 |