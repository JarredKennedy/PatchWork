import React, {useState} from 'react'
import {sprintf, __} from '@wordpress/i18n'
import {Icon, plugins, brush, plus, close, check, chevronRight} from '@wordpress/icons'

import stop from './icons/stop'
import ScanStatus from './scan-status'

import './scan-asset.scss'

const PluginIcon = (<Icon icon={plugins} size="80" />);
const ThemeIcon = (<Icon icon={brush} size="80" />);
const QueueIcon = (<Icon icon={plus} size="35" />);
const UnqueueIcon = (<Icon icon={close} size="35" />);
const CheckIcon = (<Icon icon={check} size="35" />);
const StopIcon = (<Icon icon={stop} size="35" />);
const ResultsView = (<Icon icon={chevronRight} size="35" />)

const ScanAsset = props => {
	const [hover, setHover] = useState(false);

	let statusText, actionButton;
	switch (props.status) {
		case ScanStatus.STATUS_NONE:
			statusText = "";
			actionButton = props.queued ? (
				<div
					onClick={e => props.action()}
					className={`icon-button queued${hover ? ' hover': ''}`}
					onMouseEnter={e => setHover(true)}
					onMouseLeave={e => setHover(false)}>
					{hover ? UnqueueIcon : CheckIcon}
				</div>
			) : (
				<div
					onClick={e => {
						setHover(false);
						props.action()
					}}
					className="icon-button">
					{QueueIcon}
				</div>
			);
			break;
		case ScanStatus.STATUS_SCANNING:
			statusText = __('Scanning...', 'patchwork');
			actionButton = (
				<div
					onClick={e => {props.action()}}
					className="icon-button">
					{StopIcon}
				</div>
			);
			break;
		case ScanStatus.STATUS_NOT_FOUND:
			statusText = sprintf(__('PatchWork could not find the original copy of this %s', 'patchwork'), props.asset.type);
			actionButton = null;
			break;
		case ScanStatus.STATUS_FAILED:
			statusText = props.result.error;
			actionButton = null;
			break;
		case ScanStatus.STATUS_PRESCANNED:
			if (props.result.prescan.status == 'modified') {
				statusText = __('Changed files found, click the arrow button to see changes and create a patch.', 'patchwork');
				actionButton = (
					<div
						onClick={e => props.inspect()}
						className="icon-button">
						{ResultsView}
					</div>
				);
			} else {
				statusText = __('No changes found', 'patchwork');
				actionButton = null;
			}
			break;
	}

	return(
		<tr className="pw__scan-asset">
			<td className="asset-logo">
				{props.image ? (<img width="100" height="100" src={props.image} />) : props.asset.type === 'plugin' ? PluginIcon : ThemeIcon}
			</td>
			<td className="asset-details">
				<div className="name">{props.asset.name}</div>
				<div className="author">{sprintf(__('By %s', 'patchwork'), props.asset.author)}</div>
				<div className="status-text">{statusText}</div>

				{((props.result && props.result.error) || props.status === ScanStatus.STATUS_NOT_FOUND) && (
					<div className="recourse">
						<a>{__('Retry', 'patchwork')}</a> | {!props.result.uploading ? (
							<a onClick={props.action}>{__('upload the original file', 'patchwork')}</a>
						) : (
							<span>{__('uploading...', 'patchwork')}</span>
						)}
					</div>
				)}

			</td>
			<td className="asset-action">
				{actionButton}
			</td>
		</tr>
	);
};

ScanAsset.defaultProps = {
	asset: null
};

export default ScanAsset;