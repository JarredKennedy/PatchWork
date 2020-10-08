const PatchCard = props => (
	<div className="pw__patch-card">
		<button onClick={event => props.activate()}>Activate</button>
		&nbsp;&mdash;&nbsp;
		<button  onClick={event => props.deactivate()}>Deactivate</button>
	</div>
);

export default PatchCard;