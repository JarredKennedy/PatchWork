import AdminHeader from './admin-header'
import AdminFooter from './admin-footer'

const AdminPage = (props) => (
	<div className="pw__admin-page">
		<AdminHeader
			currentPage={props.currentPage} />

		{props.children}

		<AdminFooter/>
	</div>
);

export default AdminPage;