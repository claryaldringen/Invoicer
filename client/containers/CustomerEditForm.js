
import { connect } from 'react-redux';
import { get, put, post } from 'axios';

import { updateCustomer, addCustomer, setCustomer, showEditForm } from '../actions';
import CustomerEditForm from '../components/CustomerEditForm';

const mapDispatchToProps = (dispatch) => {
	return {
		updateCustomer(customer) {
			put('api/customers', customer).then((response) => {
				if(response.status == 200) {
					dispatch(updateCustomer(customer));
					dispatch(setCustomer(response.data.id));
					dispatch(showEditForm(false));
				}
			});
		},
		addCustomer(customer) {
			post('api/customers', customer).then((response) => {
				if(response.status == 200) {
					customer.id = response.data.id;
					dispatch(addCustomer(customer));
					dispatch(setCustomer(response.data.id));
					dispatch(showEditForm(false));
				}
			});
		}
	}
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
		customer: customer
	};
}

export default connect(mapStateToProps, mapDispatchToProps)(CustomerEditForm);
