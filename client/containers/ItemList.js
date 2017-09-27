
import { connect } from 'react-redux';
import { get } from 'axios';

import { setCustomers, setCustomer } from '../actions';
import ItemList from '../components/ItemList';

const mapDispatchToProps = (dispatch) => {
	return {}
}

function mapStateToProps(state, ownProps) {

	let total = 0;
	for(let i = 0; i < state.items.length; i++) {
		let item = state.items[i];
		total += item.count*item.price;
	}

	return {
		count: state.items.length,
		total: total
	};
}

export default connect(mapStateToProps, mapDispatchToProps)(ItemList);