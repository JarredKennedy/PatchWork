import { Component } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

import ScanProgress from './scan-progress'
import ScanAssetSelector from './scan-asset-selector'

import './scan-screen.scss'

class ScanScreen extends Component {

	constructor(props) {
		super(props);

		this.state = {
			assets: [],
			queued: [],
			assetImages: {}
		};

		this.loadAssets = this.loadAssets.bind(this);
		this.loadAssetImages = this.loadAssetImages.bind(this);
		this.queue = this.queue.bind(this);
		this.unqueue = this.unqueue.bind(this);
	}

	render(props) {
		return (
			<div className="pw__scan-screen">
				
				<div className="header">
					<span>{__('Scan for Modified Files', 'patchwork')}</span>
				</div>

				<div className="body">
					<ScanProgress
						itemCount={this.state.queued.length} />
					<ScanAssetSelector
						assets={this.state.assets}
						images={this.state.assetImages}
						queued={this.state.queued}
						queue={this.queue}
						unqueue={this.unqueue} />
				</div>

			</div>
		);
	}

	componentDidMount() {
		this.loadAssets();
	}

	loadAssets() {
		patchwork.api.assets.getAssets()
		.then(assets => {
			this.setState({
				assets: assets.map(asset => {
					let slug = asset.slug;
	
					if (asset.type == 'plugin') {
						let slashAt = slug.indexOf('/');
						if (slashAt >= 0) {
							slug = slug.substr(slashAt + 1);
						}
	
						slug = slug.replace('.php', '');
					}
	
					asset.niceslug = slug;

					return asset;
				})
			}, this.loadAssetImages);
		});
	}

	queue(asset_id) {
		if (this.state.queued.indexOf(asset_id) >= 0) {
			return;
		}

		this.setState({
			queued: [...this.state.queued, asset_id]
		});
	}

	unqueue(asset_id) {
		this.setState({
			queued: this.state.queued.filter(id => id != asset_id)
		});
	}
	
	loadAssetImages() {
		let toScan = this.state.assets.filter(asset => asset.type == 'plugin').map(asset => asset.niceslug);
		
		let loaded = null;
		if (window.localStorage) {
			loaded = window.localStorage.getItem('patchwork_plugin_images');

			if (loaded) {
				loaded = JSON.parse(loaded);

				if (Array.isArray(loaded)) {
					let exclude = loaded.map(requestedImage => requestedImage.slug);
					let assetImages = loaded
						.filter(requestedImage => requestedImage.status == 1)
						.reduce((loaded, requestedImage) => {
							loaded[requestedImage.slug] = requestedImage.image;
							return loaded;
						}, this.state.assetImages);

						toScan = toScan.filter(slug => exclude.indexOf(slug) < 0);

					this.setState({assetImages});
				}
			}
		}

		const tryLoad = (slug, size) => {
			let prom = new Promise((resolve, reject) => {
				let img = new Image();
				let url = `https://ps.w.org/${slug}/assets/icon-${size}x${size}.png`;

				img.addEventListener('load', () => resolve([slug, url]));
				img.addEventListener('error', () => {
					if (size == 128) {
						tryLoad(slug, 256).then(resolve).catch(reject);
					} else {
						reject(slug);
					}
				});

				img.src = url;
			});

			return prom;
		};

		let imageRequests = toScan.map(slug => tryLoad(slug, 128));

		Promise.allSettled(imageRequests)
		.then(results => {
			this.setState({
				assetImages: results
					.filter(result => result.status == 'fulfilled')
					.map(result => result.value)
					.reduce((images, pair) => {
						images[pair[0]] = pair[1];
						return images;
					}, this.state.assetImages)
			});

			if (window.localStorage) {
				if (!loaded) {
					loaded = [];
				}

				loaded = loaded.concat(results.map(result => {
					let status = result.status == 'fulfilled' ? 1 : 0;
					let slug = status ? result.value[0] : result.reason;

					let item = {
						status,
						slug
					};

					if (status) {
						item.image = result.value[1];
					}

					return item;
				}));

				window.localStorage.setItem('patchwork_plugin_images', JSON.stringify(loaded));
			}
		});
	}

}

export default ScanScreen;