import { render, createElement } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import {
	HashRouter as Router,
	Route,
	Switch
} from 'react-router-dom'

import PatchesPage from './components/pages/patches-page'
import AddPatchPage from './components/pages/add-patch-page'
import ScanPage from './components/pages/scan-page'

(function() {
	// window.patchwork.apiClient.init(); // Init the PatchWork API client.

	var wrap = document.getElementById('patchwork-wrap');

	if (!wrap) {
		console.warn('[PATCHWORK] ' + __('PatchWork App was loaded but #patchwork-wrap element was not found.', 'patchwork'));
		return;
	}

	window.patchwork.container = document.createElement('div');

	wrap.appendChild(window.patchwork.container);

	const Base = () => (
		<Router>
			<Switch>
				<Route exact path={['/', '/patch-list']}>
					<PatchesPage currentPage="patch-list" />
				</Route>
				<Route path="/add-patch/scan">
					<ScanPage />
				</Route>
				<Route path="/add-patch">
					<AddPatchPage currentPage="add-patch" />
				</Route>
			</Switch>
		</Router>
	);

	render(
		<Base />,
		window.patchwork.container
	);
})();