
import { SET_CUSTOMERS, SET_CUSTOMER, UPDATE_CUSTOMER, ADD_CUSTOMER, SHOW_EDIT_FORM, SET_ITEM, ADD_ITEM } from './actions';

export default (state = {customers: [], customerId: 0, editForm: true, items: [{count: 1, name: '', price: ''}]}, action) => {

	switch(action.type) {
		case SET_CUSTOMERS:
			return { ...state, customers: action.customers };
		case SET_CUSTOMER:
			return { ...state, customerId: action.customerId, editForm: action.customerId == 0};
		case UPDATE_CUSTOMER:
			const customers = state.customers.map((customer) => {
				if(customer.id == action.customer.id) {
					return action.customer;
				}
				return customer;
			});
			return { ...state, customers: customers };
		case ADD_CUSTOMER:
			return { ...state, customers: [ ...state.customers, action.customer ] };
		case SHOW_EDIT_FORM:
			return { ...state, editForm: action.value};
		case SET_ITEM:
			const items = state.items.map( (item, index) => {
				if(action.index == index) {
					return action.item;
				}
				return item;
			});
			return { ...state, items: items };
		case ADD_ITEM:
			if(action.index == state.items.length - 1) {
				return {...state, items: [...state.items, {count: 1, name: '', price: ''}]};
			} else {
				return state;
			}
		default:
			return state;
	}
}