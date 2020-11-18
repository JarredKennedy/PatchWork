import apiFetch from '@wordpress/api-fetch'

/**
 * This endpoint pre-scans an asset to check there are any
 * file changes between the asset and the original.
 * 
 * @param {string} targetAssetId 
 */
const prescan = targetAssetId => {
	return apiFetch({method: 'POST', path: `/patchwork/v1/scan/${targetAssetId}/prescan`});
};

/**
 * This endpoint is called to get the line-by-line differences
 * between an asset and its original.
 * 
 * @param {string} scanToken
 */
const scan = scanToken => {
    return apiFetch({method: 'POST', path: `/patchwork/v1/scan/${scanToken}/scan`});
};

/**
 * This endpoint is called to create a patch for a set of
 * changes at the user's request.
 * 
 * @param {string} scanToken
 */
const extract = scanToken => {
    return apiFetch({method: 'POST', path: `/patchwork/v1/scan/${scanToken}/extract`});
};

/**
 * This endpoint uploads the original asset package for an asset. This
 * is used for assets not in the WordPress repository or otherwise undiscoverable.
 * 
 * @param {string} targetAssetId
 */
const upload = (targetAssetId, file) => {
    let body = new FormData();
    body.append('package', file);

    return apiFetch({
        method: 'POST',
        path: `/patchwork/v1/scan/${targetAssetId}/upload`,
        body
    });
};

export default {
	prescan,
    scan,
    extract,
    upload
};