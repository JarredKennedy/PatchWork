import apiFetch from '@wordpress/api-fetch'

const getAssets = () => {
	return apiFetch( { path: '/patchwork/v1/assets' } )
};

const getAsset = id => {

};

export default {
	getAssets,
	getAsset
}