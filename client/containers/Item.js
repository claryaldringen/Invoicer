
import { connect } from 'react-redux';
import { get } from 'axios';

import { addItem, setItem } from '../actions';
import Item from '../components/Item';

const mapDispatchToProps = (dispatch) => {
	return {
		addItem(index) {
			dispatch(addItem(index));
		},
		setItem(item, index) {
			dispatch(setItem(item, index));
		}
	}
}

function mapStateToProps(state, ownProps) {

	return {
		name: state.items[ownProps.index].name,
		count: state.items[ownProps.index].count,
		price: state.items[ownProps.index].price,
		total: state.items[ownProps.index].price*state.items[ownProps.index].count
	};
}

export default connect(mapStateToProps, mapDispatchToProps)(Item);