
export const SET_CUSTOMERS = 'SET_CUSTOMERS';
export const SET_CUSTOMER = 'SET_CUSTOMER';
export const UPDATE_CUSTOMER = 'UPDATE_CUSTOMER';
export const ADD_CUSTOMER = 'ADD_CUSTOMER';
export const SHOW_EDIT_FORM = 'SHOW_EDIT_FORM';
export const SET_ITEM = 'SET_ITEM';
export const ADD_ITEM = 'ADD_ITEM';

export function setCustomers(customers) {
	return {type: SET_CUSTOMERS, customers: customers}
}

export function setCustomer(id) {
	return {type: SET_CUSTOMER, customerId: id}
}

export function updateCustomer(customer) {
	return {type: UPDATE_CUSTOMER, customer: customer}
}

export function addCustomer(customer) {
	return {type: ADD_CUSTOMER, customer: customer}
}

export function showEditForm(value) {
	return {type: SHOW_EDIT_FORM, value: value}
}

export function setItem(item, index) {
	return {type: SET_ITEM, item: item, index: index}
}

export function addItem(index) {
	return {type: ADD_ITEM, index: index}
}