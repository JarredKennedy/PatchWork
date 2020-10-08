import { __ } from '@wordpress/i18n'
import { Link } from 'react-router-dom'

import './admin-header.scss'

const headerLinks = [
	{
		id: 'patch-list',
		text: __('Patches', 'patchwork')
	},
	{
		id: 'add-patch',
		text: __('Add Patch', 'patchwork')
	},
	{
		id: 'settings',
		text: __('Settings', 'patchwork')
	}
];

const AdminHeader = (props) => (
	<nav className="pw__admin-header">
		<ul>
			{headerLinks.map(link => (
				<li
					key={link.id}
					className={(props.currentPage == link.id) ? 'active': ''}>
					<Link to={`/${link.id}`}>{link.text}</Link>
				</li>
			))}
		</ul>
	</nav>
);

export default AdminHeader;