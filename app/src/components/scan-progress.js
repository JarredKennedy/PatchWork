import React from 'react'
import {__, sprintf, _n} from '@wordpress/i18n'

import './scan-progress.scss'

const ScanProgress = props => {
	let statusText;
	
	if (props.isComplete) {
		statusText = __('Completed', 'patchwork');
	} else if (props.isScanning) {
		statusText = sprintf(__('Scanning (1/%d)', 'patchwork'), props.itemCount);
	} else {
		statusText = sprintf(_n('New Scan (%d item)', 'New Scan (%d items)', props.itemCount, 'patchwork'), props.itemCount);
	}

	return (
		<div className="pw__scan-progress">

			<div className="top-row">

				<div className="scan-status">
					<h3>{statusText}</h3>	
				</div>

				<div className="scan-action">
					{props.isComplete && (
						<button
							onClick={e => props.reset()}>{__('New Scan', 'patchwork')}</button>
					)}

					{props.isScanning && (
						<button
							onClick={e => props.stop()}
							className="stop">{__('Stop Scan', 'patchwork')}</button>
					)}

					{props.isNew && (
						<button
							onClick={e => props.start()}
							disabled={props.itemCount < 1}>{__('Start Scan', 'patchwork')}</button>
					)}
				</div>
				
			</div>

			<div className="description">
				<p>{__('Add plugins and themes to scan for changes below then press "Start Scan" to begin.', 'patchwork')}</p>
			</div>

		</div>
	);
};

export default ScanProgress