# Patch File Format v1
- All integer values are unsigned, unless otherwise indicated.
- The byte order for numbers is always little-endian.
- All strings are to be encoded as UTF-8 and the string lengths are expressed in bytes, not characters.

## Header
The header section is the first section of the patch file.

| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| H.0 | Patch file magic number | char | The magic number identifies the file as a PatchWork patch file | 1F 50 41 54 43 48 1A | 7 | 1F 50 41 54 43 48 1A |
| H.1 | Patch file format version | char | The patch file format version of the data in the patch file | 1 | 1 | 1 |
| H.2 | Diff blocks offset | short | The byte offset of the start of the diff block section | _variable_ | 2 | 264 |
| H.3 | Target asset identifier length | char | The length of the Target Asset Identifier (H.4) | _variable_ | 1 | 22 |
| H.4 | Target asset identifier | string | String identifying the asset this patch targets | _variable_ | _value of H.3_ | plugin:patchwork:1.0.0 |
| H.5 | Vendor ID length | char | The length of the vendor ID field (H.6) | _variable_ | 1 | 23 |
| H.6 | Vendor ID | string | The vendor ID. String representing the vendor in reverse domain name notation | _variable_ | _value of H.5_ | app.patchwork.PatchWork |
| H.7 | Deprecation trigger version | string | A version expression. When the target asset version satisfies this expression, the patch is deactivated | _variable_ | 30 | >=1.1.0, next, 1.0.1 |
| H.8 | Deprecation policy | char | Determines which deprecation check to use when an update introduces changes to any line(s) changed by the patch. _Note: Update Protection is not enforced for deactivated patches._ | **0** The patch is not deactivated regardless of changes<br> **1** The patch will be deactivated if the set of changes in the patch are identically matched in the updated asset<br> **2** The patch will be deactivated if the set of changes in the patch are similarly matched in the updated asset. (see [Glossary: Similarity](./glossary.md#Patch-Similarity)) | 1 | 2 |
| H.9 | Signature | char | Signature for the patch file, signed with the PatchWork private key | _variable_ | 128 | --- |
| H.10 | Author name length | char | The length of the author field (H.11) | _variable_ | 1 | 9 |
| H.11 | Author name | string | The author of the patch | _variable_ | _value of H.10_ | PatchWork |
| H.12 | Author url length | char | The length of the author url (H.13) | _variable_ | 1 | 21 |
| H.13 | Author url | string | The url of the patch author | _variable_ | _value of H.12_ | https://patchwork.dev |
| H.14 | Patch description length | char | The length of the patch description field (H.15) | _variable_ | 1 | 12 |
| H.15 | Patch description | string | A description of what the patch does | _variable_ | _value of H.14_ | Does a thing |
| H.16 | Total lines added | long | The number of lines added or modified in this patch | _variable_ | 4 |  100 |
| H.17 | Total lines removed | long | The number of lines removed or modified in this patch | _variable_ | 4 |  40 |
| H.18 | Patch creation timestamp | long | 32bit unix timestamp representing when the patch was created | _variable_ | 4 | 1546300800 |
| H.19 | Patch checksum | char | SHA-1 hash of the patch file. Calculated with NULL bytes for this field | _variable_ | 20 | 2E 99 75 85 48 97 2A 8E 88 22 AD 47 FA 10 17 FF 72 F0 6F 3F |

## Diff Blocks
A patch file contains at least one diff block. There is one diff block per changed file. Diff blocks have a header which contains some metadata, such as the name of the file.
The rest of the diff block contains a series of changes made to the file. There is one change entry for every consecutive series of changed lines. Each change entry contains a list
of changed lines. Changed lines for the original and the modified version are included so the patch is able to be undone.

### Diff Block: Header
| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| DBH.0 | Diff Block Header magic number | char | Magic number marking the beginning of a diff block header | 1F 50 57 44 48 1A | 6 | 1F 50 57 44 48 1A |
| DBH.1 | Block length | long | The length of the diff block (including the header and this field) | _variable_ | 4 | 1012 |
| DBH.2 | Original file path length | short | The length of the original file path (DBH.3) | _variable_ | 2 | 23 |
| DBH.3 | Original file path | string | The original file path | _variable_ | _value of DBH.2_ | patchwork/patchwork.php |
| DBH.4 | Patched file path length | short | The length of the patched file path (DBH.5) | _variable_ | 2 | 23 |
| DBH.5 | Patched file path | string | The patched file path. This is usually the same as DBH.3, except in the case the file was renamed | _variable_ | _value of DBH.4_ | patchwork/patchwork.php |
| DBH.6 | Number of lines added | short | The number of lines added and modified in this diff | _variable_ | 2 | 20 |
| DBH.7 | Number of lines removed | short | The number of lines removed and modified in this diff | _variable_ | 2 | 51 |

### Diff Block: Body
Contains a list of line change entries, which are consecutive changed lines.  
**Line Change Entry**

| Field ID | Field | Type | Description | Value(s) | Length (bytes) | Example(s) |
| --- | --- | --- | --- | --- | --- | --- |
| DBB.0 | Diff Block Body entry magic number | char | Magic number marking the beginning of a diff block body entry | 1F 50 57 44 42 1A | 6 | 1F 50 57 44 42 1A |
| DBB.1 | Original file start line | short | Number of the line which marks the first changed line in this entry in the original file | _variable_ | 2 | 100 |
| DBB.2 | Original file changed lines | short | Number of the lines changed for this entry in the original file | _variable_ | 2 | 100 |
| DBB.3 | Patched file start line | short | Number of the line which marks the first changed line in this entry in the patched file | _variable_ | 2 | 100 |
| DBB.4 | Patched file changed lines | short | Number of the lines changed for this entry in the patched file | _variable_ | 2 | 100 |
| DBB.5 | Original lines length | long | The number of bytes changed (deleted) from the original file | _variable_ | 4 | 14 |
| DBB.6 | Original lines | string | The affected lines as they were in the original file (includes newline characters) | _variable_ | _value of DBB.5_ | Cello, World! |
| DBB.7 | Patched lines length | long | The number of bytes modified (inserted) to replace the original lines | _variable_ | 4 | 14 |
| DBB.8 | Patched lines | string | The updated lines as they should be after the patch is applied (includes newline characters) | _variable_ | _value of DBB.7_ | Hello, World! |