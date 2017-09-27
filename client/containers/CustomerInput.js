
import { connect } from 'react-redux';
import { get } from 'axios';

import { setCustomers, setCustomer, showEditForm } from '../actions';
import CustomerInput from '../components/CustomerInput';

const mapDispatchToProps = (dispatch) => {
	return {
		loadFromServer() {
			get('/api/customers').then((response) => {

				if (response.status !== 200) {
					return;
				}

				dispatch(setCustomers(response.data));
			});
		},
		setCustomer(id) {
			dispatch(setCustomer(id));
		},
		setEditForm(value) {
			dispatch(showEditForm(value))
		}
	}
}

function mapStateToProps(state, ownProps) {

	return {
		customers: state.customers,
		customerId: state.customerId,
		showEditForm: state.editForm
	};
}

export default connect(mapStateToProps, mapDispatchToProps)(CustomerInput);
