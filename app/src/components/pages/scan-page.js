import BasePage from '../layout/base-page'
import ScanScreen from '../scan-screen'
import withAssets from '../abstract/with-assets'
import withScanController from '../abstract/with-scan-controller'

const ScanScreenWithDependencies = withScanController(withAssets(ScanScreen, true));

const ScanPage = (props) => (
	<BasePage
		currentPage="add-page">
		
		<ScanScreenWithDependencies />

	</BasePage>
);

export default ScanPage;