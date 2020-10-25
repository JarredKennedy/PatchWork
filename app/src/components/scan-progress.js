import React from 'react'
import { __, sprintf } from '@wordpress/i18n'

import './scan-progress.scss'

const ScanProgress = props => (
	<div className="pw__scan-progress">

		<div className="top-row">

			<div className="scan-status">
				<h3>{sprintf(__('New Scan (%d items)', 'patchwork'), props.itemCount)}</h3>	
			</div>

			<div className="scan-action">
				<button disabled={(props.status == 'new' && props.itemCount < 1)}>{__('Start Scan', 'patchwork')}</button>
			</div>
			
		</div>

		<div className="description">
			<p>{__('Add plugins and themes to scan for changes below then press "Start Scan" to begin.', 'patchwork')}</p>
		</div>

	</div>
);

ScanProgress.defaultProps = {
	status:	'new',
	itemCount: 0
};

export default ScanProgress