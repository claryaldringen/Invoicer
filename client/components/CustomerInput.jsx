
import React from 'react'

import CustomerEditForm from '../containers/CustomerEditForm'

export default class CustomerInput extends React.Component {

	componentDidMount() {
		this.props.loadFromServer();
	}

	handleChange(event) {
		this.props.setEditForm(event.target.value == 0);
		this.props.setCustomer(event.target.value)
	}

	handleClick(event) {
		this.props.setEditForm(!this.props.showEditForm);
	}

	render() {
		let options = this.props.customers.map(function(customer, key){
			return <option key={key} value={customer.id}>{customer.name}</option>;
		});

		let editForm = null;
		var text = 'Upravit';
		if(this.props.showEditForm) {
			editForm = <CustomerEditForm/>;
			text = 'Zavřít'
		}

		var buttonStyle = {display: 'block'};
		if(this.props.customerId == 0) {
			buttonStyle = {display: 'none'};
		}

		return(
			<div className="col-md-10">
				<select className="form-control" onChange={this.handleChange.bind(this)} value={this.props.customerId}>
					{options}
					<option value="0" key={this.props.customers.length}>Nový zákazník</option>
				</select>
				<button style={buttonStyle} className="btn btn-default" onClick={this.handleClick.bind(this)}>{text}</button>
				{editForm}
			</div>
		);
	}

}
