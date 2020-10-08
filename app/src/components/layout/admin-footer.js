import './admin-footer.scss'

const AdminFooter = (props) => (
	<footer className="pw__admin-footer">

		<div className="logo">
			<img src={`${window.patchwork.pw_url}/app/images/pwmain.png`} />
		</div>

	</footer>
);

export default AdminFooter;