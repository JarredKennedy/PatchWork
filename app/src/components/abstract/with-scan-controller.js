import React from 'react'
import {Component, createRef} from '@wordpress/element'

import ScanStatus from '../scan-status'

const STATUS_NEW = 1;
const STATUS_SCANNING = 2;
const STATUS_COMPLETE = 3;

export default (WrappedComponent) => class extends Component {

	constructor(props) {
		super(props);

		this.state = {
			status: STATUS_NEW,
			scanning: null,
			queue: [],
			scanned: [],
			results: {}
		};

		this.assetToUpload = null;
		this.fileRef = createRef();

		this.enqueue = this.enqueue.bind(this);
		this.unqueue = this.unqueue.bind(this);
		this.scan = this.scan.bind(this);
		this.stop = this.stop.bind(this);
		this.reset = this.reset.bind(this);
		this.uploadOriginal = this.uploadOriginal.bind(this);
		this.handleManualUpload = this.handleManualUpload.bind(this);
	}

	render() {
		return (
			<>
				<WrappedComponent
					scanning={this.state.scanning}
					isNew={this.state.status === STATUS_NEW}
					isScanning={this.state.status == STATUS_SCANNING}
					isComplete={this.state.status == STATUS_COMPLETE}
					queuedAssets={this.state.queue}
					enqueue={this.enqueue}
					unqueue={this.unqueue}
					scan={this.scan}
					stop={this.stop}
					reset={this.reset}
					results={this.state.results}
					uploadOriginal={this.uploadOriginal}
					{...this.props} />

				<input type="file" accept=".zip" ref={this.fileRef} style={{display: 'none'}} onChange={this.handleManualUpload} />
			</>
		);
	}

	enqueue(assetId) {
		this.setState({
			queue: this.state.queue.filter(inQueue => inQueue != assetId).concat(assetId)
		});
	}

	unqueue(assetId) {
		this.setState({
			queue: this.state.queue.filter(inQueue => inQueue != assetId)
		});
	}

	uploadOriginal(assetId) {
		this.assetToUpload = assetId;
		this.fileRef.current.value = '';
		this.fileRef.current.click();
	}

	handleManualUpload() {
		if (!this.fileRef.current.files.length) return;
	
		let file = this.fileRef.current.files[0];
		let assetId = this.assetToUpload;
		this.assetToUpload = null;

		this.setState({
			results: {
				...this.state.results,
				[assetId]: {
					...this.state.results[assetId],
					uploading: true
				}
			}
		});

		patchwork.api.scan.upload(assetId, file)
			.then(response => {
				console.log(response);
				this.setState({
					scanned: this.state.scanned.filter(id => id != assetId),
					results: {
						...this.state.results,
						[assetId]: {
							...this.state.results[assetId],
							status: ScanStatus.STATUS_NONE,
							uploading: false
						}
					}		
				}, this.scan);
			})
			.catch(error => {
				console.log(error);
				this.setState({
					results: {
						...this.state.results,
						[assetId]: {
							...this.state.results[assetId],
							status: STATUS_FAILED,
							error: error.message
						}
					}		
				});
			});
	}

	reset() {
		this.setState({
			queue: [],
			scanning: null,
			scanned: [],
			results: {},
			status: STATUS_NEW
		});
	}

	stop() {
		this.setState({
			status: STATUS_COMPLETE
		});
	}

	scan() {
		if (this.state.status === STATUS_SCANNING) return;

		this.setState({
			status: STATUS_SCANNING	
		});

		const doScan = assetId => {
			this.setState({
				scanning: assetId
			});

			patchwork.api.scan.prescan(assetId)
			.then(prescanStatus => {
				if (!prescanStatus.hasOwnProperty('status')) {
					throw new Error('Pre-scan response was malformed');
				}
				
				let status = (prescanStatus.status === 'missing_original') ? ScanStatus.STATUS_NOT_FOUND : ScanStatus.STATUS_PRESCANNED;

				this.setState({
					scanned: this.state.scanned.concat(assetId),
					results: {
						...this.state.results,
						[assetId]: {
							status,
							prescan: prescanStatus
						}
					}
				});
			})
			.catch(error => {
				// Set error in results.
				console.log(error);

				this.setState({
					scanned: this.state.scanned.concat(assetId),
					results: {
						...this.state.results,
						[assetId]: {
							status: ScanStatus.STATUS_FAILED,
							prescan: null,
							error: error.message || 'Unknown error'
						}
					}
				});
			})
			.finally(() => {
				let toScan = this.state.queue.filter(id => this.state.scanned.indexOf(id) < 0);

				if (toScan.length && this.state.status == STATUS_SCANNING) {
					doScan(toScan[0]);
				} else {
					this.setState({
						status: STATUS_COMPLETE,
						scanning: null
					});
				}
			});
		};

		let toScan = this.state.queue.filter(id => this.state.scanned.indexOf(id) < 0);
		if (toScan.length > 0) {
			doScan(toScan[0]);
		}
	}

}