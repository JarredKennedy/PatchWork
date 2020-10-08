import { __ } from '@wordpress/i18n'

import BasePage from '../layout/base-page'
import AddPatchOptionGroup from '../add-patch-option-group'

const options = [
	{
		action: 'scan',
		heading: __('Scan', 'patchwork'),
		shortDescription: __('Find changes in your plugin and theme files automatically.', 'patchwork'),
		fullDescription: __('Recommended if you have modified theme or plugin files and need to preserve those changes as a patch.', 'patchwork'),
		icon: `${window.patchwork.pw_url}/app/images/scan.svg`,
		buttonText: __('Start Scan', 'patchwork')
	},
	{
		action: 'import',
		heading: __('Import', 'patchwork'),
		shortDescription: __('Upload one or more patch files.', 'patchwork'),
		fullDescription: __('Recommended if you need to restore an exported patch or a patch obtained from a vendor.', 'patchwork'),
		icon: `${window.patchwork.pw_url}/app/images/download.svg`,
		buttonText: __('Import Patches', 'patchwork')
	},
	{
		action: 'create',
		heading: __('Create Patch', 'patchwork'),
		shortDescription: (
			<>
			{__('Add a snippet of code to WordPress.', 'patchwork')}
			<br/>
			{__('This creates a new, single-file, plugin.', 'patchwork')}
			</>
		),
		fullDescription: __('Recommended if you have been instructed to add code to functions.php or just need to add some code to WordPress.', 'patchwork'),
		icon: `${window.patchwork.pw_url}/app/images/code.svg`,
		buttonText: __('Create Patch', 'patchwork')
	}
];

const AddPatchPage = (props) => (
	<BasePage
		currentPage={props.currentPage}>

		<AddPatchOptionGroup
			options={options} />

	</BasePage>
);

export default AddPatchPage;