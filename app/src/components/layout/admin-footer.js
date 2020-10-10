import './admin-footer.scss'

const AdminFooter = (props) => (
	<footer className="pw__admin-footer">

		<div className="logo">
			<img src={`${window.patchwork.pw_url}/app/images/pwmain.png`} />

			<div className="identity">
				<span>{`PatchWork v${window.patchwork.pw_version}`}</span>
			</div>
		</div>

	</footer>
);

export default AdminFooter;