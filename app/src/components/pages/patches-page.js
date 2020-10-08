import BasePage from '../layout/base-page'
import withPatchDisplayController from '../patches-display'
import PatchList from '../patch-list'
import PatchCard from '../patch-card.js'

const PatchesDisplay = withPatchDisplayController(PatchList, PatchCard);

const PatchesPage = (props) => (
	<BasePage
		currentPage={props.currentPage}>

		<PatchesDisplay />

	</BasePage>
);

export default PatchesPage;