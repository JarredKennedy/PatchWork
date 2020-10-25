import React from 'react'
import {__} from '@wordpress/i18n'

import ScanAsset from './scan-asset'

import './scan-asset-selector.scss'

const ScanAssetSelector = props => (
	<div className="pw__scan-asset-selector">

		{props.assets.map(asset => {
			let action;

			if (props.queued.indexOf(asset.id) >= 0) {
				action = (
					<button onClick={e => props.unqueue(asset.id)}>{__('Unqueue', 'patchwork')}</button>
				);
			} else {
				action = (
					<button onClick={e => props.queue(asset.id)}>{__('Queue', 'patchwork')}</button>
				);
			}

			let image = null;
			if (props.images.hasOwnProperty(asset.niceslug)) {
				image = props.images[asset.niceslug];
			}

			return (
				<ScanAsset
					key={asset.id}
					asset={asset}
					image={image}
					action={action} />
			);
		})}

	</div>
);

export default ScanAssetSelector;