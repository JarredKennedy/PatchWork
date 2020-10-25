import React from 'react'
import {sprintf, __} from '@wordpress/i18n'
import { Icon, plugins, brush } from '@wordpress/icons'

import './scan-asset.scss'

const PluginIcon = (
	<Icon icon={plugins} size="80" />
);

const ThemeIcon = (
	<Icon icon={brush} size="80" />
);

const ScanAsset = props => (
	<div className="pw__scan-asset">
		<div className="asset-logo">
			{props.image ?
				(
					<img width="100" height="100" src={props.image} />
				) :
				props.asset.type === 'plugin' ? PluginIcon : ThemeIcon
			}
		</div>

		<div className="asset-details">
			<div className="name">{props.asset.name}</div>
			<div className="author">{sprintf(__('By %s', 'patchwork'), props.asset.author)}</div>
		</div>

		<div className="asset-action">
			{props.action}
		</div>
	</div>
);

ScanAsset.defaultProps = {
	asset: null
};

export default ScanAsset;