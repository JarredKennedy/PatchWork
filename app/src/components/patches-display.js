import { Component } from '@wordpress/element'

function withPatchDisplayController(ListComponent, ItemComponent) {
	/*
		PatchesDisplay is a smart component which manages a list of displayed patches.
	*/
	return class PatchesDisplay extends Component {

		constructor(props) {
			super(props);

			this.state = {
				patches: [
					{
						id: 'caabad',
						name: 'Some hash A'
					},
					{
						id: '6604a3',
						name: 'Some other hash B'
					},
					{
						id: '60d76f',
						name: 'Some final hash C'
					}
				]
			};
		}

		render(props) {
			return (
				<ListComponent>

					{this.state.patches.map(patch => (
						<ItemComponent
							key={patch.id}
							patch={patch}
							activate={() => this.activatePatch(patch.id)}
							deactivate={() => this.deactivatePatch(patch.id)} />
					))}

				</ListComponent>
			);
		}

		activatePatch(patchId) {
			alert("Would activate patch " + patchId);
		}

		deactivatePatch(patchId) {
			alert("Would deactivate patch " + patchId);
		}

	}
}

export default withPatchDisplayController;