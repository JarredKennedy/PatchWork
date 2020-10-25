import { createHooks } from '@wordpress/hooks'

import scanning from './scanning'
import assets from './assets'

const hook = createHooks();

export {
	scanning,
	assets,
	hook
}