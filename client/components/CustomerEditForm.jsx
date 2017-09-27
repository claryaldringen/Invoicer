
import React from 'react';
import { findDOMNode } from 'react-dom'

export default class CustomerEditForm extends React.Component {

	handleSubmit(event) {
		event.preventDefault();

		const customer = {
			id: this.props.customer.id,
			name: findDOMNode(this.refs.name).value,
			street: findDOMNode(this.refs.street).value,
			number: findDOMNode(this.refs.number).value,
			city: findDOMNode(this.refs.city).value,
			post_code: findDOMNode(this.refs.post_code).value,
			ico: findDOMNode(this.refs.ico).value,
			dic: findDOMNode(this.refs.dic).value,
			email: findDOMNode(this.refs.email).value
		}

		if(customer.id > 0) {
			this.props.updateCustomer(customer);
		} else {
			this.props.addCustomer(customer);
		}

	}

	render() {
		const customer = this.props.customer;

		return(
			<form onSubmit={this.handleSubmit.bind(this)}>
			<table>
				<tr><td>Jméno:</td><td><input className="form-control" type="text" ref="name" defaultValue={customer.name} /></td></tr>
				<tr><td>Ulice:</td><td><input type="text" className="form-control" ref="street" defaultValue={customer.street} /></td></tr>
				<tr><td>Č.p.:</td><td><input type="text" className="form-control" ref="number" defaultValue={customer.number} /></td></tr>
				<tr><td>Město:</td><td><input type="text" className="form-control" ref="city" defaultValue={customer.city}/></td></tr>
				<tr><td>PSČ:</td><td><input type="text" className="form-control" ref="post_code" defaultValue={customer.post_code} /></td></tr>
				<tr><td>IČ:</td><td><input type="text" className="form-control" ref="ico" defaultValue={customer.ico} /></td></tr>
				<tr><td>DIČ:</td><td><input type="text" className="form-control" ref="dic" defaultValue={customer.dic} /></td></tr>
				<tr><td>Email:</td><td><input type="email" className="form-control" ref="email" defaultValue={customer.email} /></td></tr>
				<tr><td colSpan="2"><input type="submit" className="btn btn-primary" value="Uložit zákazníka" /></td></tr>
			</table>
			</form>
		);
	}

}
