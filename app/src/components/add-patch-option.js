import Button from './button'
import { Link } from 'react-router-dom'

import './add-patch-option.scss'

const AddPatchOption = (props) => (
	<div className="pw__add-patch-option">
		<div className="header">{props.heading}</div>
		<div className="internal">
			<p className="shortDescription">{props.shortDescription}</p>

			<p className="icon">
				<img src={props.icon} />
			</p>

			<p className="action">
				<Link to={`/add-patch/${props.action}`}>
					<Button type="primary">{props.buttonText}</Button>
				</Link>
			</p>

			<hr className="separator" />

			<p className="fullDescription">{props.fullDescription}</p>
		</div>
	</div>
);

export default AddPatchOption;