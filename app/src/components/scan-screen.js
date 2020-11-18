import {Component} from '@wordpress/element'
import {__, sprintf} from '@wordpress/i18n'

import ScanProgress from './scan-progress'
import ScanAssetSelector from './scan-asset-selector'
import ScanResults from './scan-results'

import './scan-screen.scss'

class ScanScreen extends Component {
	constructor(props) {
		super(props);

		this.state = {
			inspecting: null
		};

		this.inspectChanges = this.inspectChanges.bind(this);
	}

	render() {
		return (
			<div className={this.state.inspecting ? 'pw__scan-screen inspecting' : 'pw__scan-screen'}>
				
				<div className="header">
					<span>
						{!this.state.inspecting ?
							__('Scan for Modified Files', 'patchwork') :
							sprintf(__('Showing File Changes for %s', 'patchwork'), this.props.assets.byId[this.state.inspecting].name)}
					</span>
				</div>

				<div className="body">
					{(!this.state.inspecting) ? (
						<>
							<ScanProgress
								itemCount={this.props.queuedAssets.length}
								start={this.props.scan}
								reset={this.props.reset}
								isNew={this.props.isNew}
								isScanning={this.props.isScanning}
								isComplete={this.props.isComplete} />
							<ScanAssetSelector
								assets={(this.props.isNew ? this.props.assets.all : this.props.queuedAssets).map(assetId => this.props.assets.byId[assetId])}
								images={this.props.assets.images}
								isNew={this.props.isNew}
								isScanning={this.props.isScanning}
								isComplete={this.props.isComplete}
								queued={this.props.queuedAssets.sort((assetA, assetB) => this.props.assets.all.indexOf(assetA) - this.props.assets.all.indexOf(assetB))}
								current={this.props.scanning}
								results={this.props.results}
								queuedAssets={this.props.queuedAssets}
								unqueue={this.props.unqueue}
								queue={this.props.enqueue}
								uploadOriginal={this.props.uploadOriginal}
								inspectChanges={this.inspectChanges} />
						</>
					) : (
						<ScanResults
							scanToken={this.props.results[this.state.inspecting].prescan.token} />
					)}
					
				</div>

			</div>
		);
	}

	inspectChanges(assetId) {
		this.setState({
			inspecting: assetId
		});
	}

}

export default ScanScreen;