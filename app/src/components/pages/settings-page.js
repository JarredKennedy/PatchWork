import BasePage from '../layout/base-page'

const SettingsPage = (props) => (
	<BasePage
		currentPage="settings">
		
		<div>
			<h3>Settings</h3>
			<ul>
				<li>Only allow verified patches (default: off)</li>
				<li>(Advanced) Send telemetry now</li>
				<li>(Advanced) Rebuild patches cache</li>
			</ul>
		</div>

	</BasePage>
);

export default SettingsPage;