import React from 'react'
import {Component} from '@wordpress/element'

export default (WrappedComponent, withImages) => class extends Component {
    constructor(props) {
        super(props);

        this.state = {
            assets: {
                all: [],
                byId: {},
                images: {}
            }
        };

        this.loadAssetImages = this.loadAssetImages.bind(this);
    }

    render() {
        return <WrappedComponent assets={this.state.assets} {...this.props} />
    }

    componentDidMount() {
        patchwork.api.assets.getAssets()
        .then(assets => {
            this.setState({
                assets: {
                    ...this.state.assets,
                    byId: assets.reduce((byId, asset) => {
                        let slug = asset.slug;
        
                        if (asset.type == 'plugin') {
                            let slashAt = slug.indexOf('/');
                            if (slashAt >= 0) {
                                slug = slug.substr(0, slashAt);
                            }
        
                            slug = slug.replace('.php', '');
                        }
        
                        asset.niceslug = slug;
                        byId[asset.id] = asset;
                        return byId;
                    }, this.state.assets.byId),
                    all: this.state.assets.all.concat(assets.map(asset => asset.id)).filter((assetId, index, all) => all.indexOf(assetId) == index)
                }
            }, () => {
                if (withImages) {
                    this.loadAssetImages();
                }
            });
        });
    }

    loadAssetImages() {
		let toScan = this.state.assets.all
			.filter(assetId => this.state.assets.byId[assetId].type == 'plugin')
			.map(assetId => this.state.assets.byId[assetId].niceslug);
		
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
						}, this.state.assets.images);

						toScan = toScan.filter(slug => exclude.indexOf(slug) < 0);

					this.setState({
                        assets: {
                            ...this.state.assets,
                            images: assetImages
                        }
                    });
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
                assets: {
                    ...this.state.assets,
                    images: results
                        .filter(result => result.status == 'fulfilled')
                        .map(result => result.value)
                        .reduce((images, pair) => {
                            images[pair[0]] = pair[1];
                            return images;
                        }, this.state.assets.images)
                }
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
};