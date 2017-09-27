
import { connect } from 'react-redux';
import { get } from 'axios';

import { setCustomers, setCustomer } from '../actions';
import InvoiceForm from '../components/InvoiceForm';

const mapDispatchToProps = (dispatch) => {
	return {}
}

function mapStateToProps(state, ownProps) {

	let customer = {id: 0, name: '', street: '', number: '', city: '', post_code: '', ico: '', dic: '', email: ''};
	for(let i = 0; i < state.customers.length; i++) {
		if(state.customers[i].id == state.customerId) {
			customer = state.customers[i];
			break;
		}
	}

	return {
		customers: state.customers,
		customerId: state.customerId,
		customer: customer,
		items: state.items
	};
}

export default connect(mapStateToProps, mapDispatchToProps)(InvoiceForm);