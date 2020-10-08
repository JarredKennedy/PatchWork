import AddPatchOption from './add-patch-option'

import './add-patch-option-group.scss'

const AddPatchOptionGroup = (props) => (
	<div className="pw__add-patch-option-group">

		{props.options.map(option => (
			<AddPatchOption
				key={option.action}
				{...option} />
		))}

	</div>
);

export default AddPatchOptionGroup;