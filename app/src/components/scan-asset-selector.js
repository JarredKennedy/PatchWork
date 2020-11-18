import React from 'react'
import {__} from '@wordpress/i18n'

import ScanAsset from './scan-asset'
import ScanStatus from './scan-status'

import './scan-asset-selector.scss'

const ScanAssetSelector = props => (
	<div className="pw__scan-asset-selector">

		<table>
			<tbody>
				{props.assets.map(asset => {
					let action, status = ScanStatus.STATUS_NONE;

					if (props.queuedAssets.indexOf(asset.id) >= 0) {
						action = () => props.unqueue(asset.id);
					} else {
						action = () => props.queue(asset.id);
					}

					let image = null;
					if (props.images.hasOwnProperty(asset.niceslug)) {
						image = props.images[asset.niceslug];
					}

					if (props.current == asset.id) {
						status = ScanStatus.STATUS_SCANNING;
					} else if (props.results.hasOwnProperty(asset.id)) {
						status = props.results[asset.id].status;

						if (props.results[asset.id].error) {
							action = () => props.uploadOriginal(asset.id);
						}
					}

					return (
						<ScanAsset
							key={asset.id}
							asset={asset}
							image={image}
							action={action}
							queued={(props.queuedAssets.indexOf(asset.id) >= 0)}
							result={props.results[asset.id]}
							status={status}
							inspect={() => props.inspectChanges(asset.id)} />
					);
				})}
			</tbody>
		</table>

	</div>
);

export default ScanAssetSelector;