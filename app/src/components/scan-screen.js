import { Component } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

import './scan-screen.scss'

class ScanScreen extends Component {

	render(props) {
		return (
			<div className="pw__scan-screen">
				
				<div className="header">
					<span>{__('Scan for Modified Files', 'patchwork')}</span>
				</div>

				<div className="body">
					<p>This is a test</p>
				</div>

			</div>
		);
	}
	
}

export default ScanScreen;