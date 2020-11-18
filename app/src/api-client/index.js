import { createHooks } from '@wordpress/hooks'

import scan from './scanning'
import assets from './assets'

const hook = createHooks();

export {
	scan,
	assets,
	hook
};