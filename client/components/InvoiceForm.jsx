
import React from 'react';
import { findDOMNode } from 'react-dom';
import { get, delete as del, post } from 'axios';

import CustomerInput from '../containers/CustomerInput';
import ItemList from '../containers/ItemList';
import InvoiceList from './InvoiceList';

export default class InvoiceForm extends React.Component {

	constructor(props) {
		super(props);
		this.state = {key: 0, text: 'výzvu k platbě', type: 1, invoices: [], preinvoices: [], cyclic: [], showDate: false};
	}

	handleChange(event) {
		if(event.target.value == 1) {
			this.setState({text: 'výzvu k platbě', type: 1})
		} else {
			this.setState({text: 'fakturu', type: 2})
		}
	}

	sendToServer(url, data, method) {
		$.ajax({
			url: url,
			method: method,
			dataType: 'json',
			data: data,
			success: (data) => {
				var key = this.state.key;
				key++;
				this.setState({key: key, invoices: data.invoices, preinvoices: data.preinvoices});
			},
			error: (xhr, status, err) => {
				console.log(err);
			}
		});
	}

	getData() {

		let items = this.props.items.filter( (item) => {
			return !isNaN(parseFloat(item.price)) && isFinite(item.price) && item.name;
		});

		var data = {
			customerId: this.props.customerId,
			issueDate: findDOMNode(this.refs.issueDate).value,
			paymentDate: findDOMNode(this.refs.paymentDate).value,
			type: findDOMNode(this.refs.type).value,
			items: items,
			send: false
		};
		return data;
	}

	load(url, type) {
		get(url).then((response) => {
			if(response.status == 200) {
				this.setState({[type]: response.data});
			}
		});
		return this;
	}

	componentDidMount() {
		this.load('api/invoices', 'invoices').load('api/preinvoices', 'preinvoices').load('api/cyclic', 'cyclic');
	}

	handleSaveClick() {
		if(confirm('Opravdu vytvořit?')) {
			this.sendToServer('api/invoices', this.getData(), 'post');
		}
	}

	handleItemsClick() {
		if(confirm('Opravdu vytvořit?')) {
			this.sendToServer('api/items', this.getData(), 'post');
		}
	}

	handleSendClick() {
		if(confirm('Opravdu odeslat?')) {
			var data = this.getData();
			data.send = true;
			this.sendToServer('api/invoices', data, 'post');
		}
	}

	handleDelete(invoice) {
		const id = invoice.props.data.variable_symbol_id ? invoice.props.data.variable_symbol_id : invoice.props.data.id;
		this.sendToServer('api/invoices/' + id, {}, 'delete');
	}

	handleDeleteCyclic(invoice) {
		const id = invoice.props.data.variable_symbol_id ? invoice.props.data.variable_symbol_id : invoice.props.data.id;
		del('api/cyclic/' + id).then( (result) => {
			if(result.status == 200) {
				this.setState({cyclic: result.data});
			}
		})
	}

	handleCycle(invoice) {
		this.variableSymbol = invoice.props.data.variable_symbol_id ? invoice.props.data.variable_symbol_id : invoice.props.data.id;
		this.setState({showDate: true});
	}

	handleCloseDialog() {
		this.setState({showDate: false});
	}

	handleSaveCyclicClick() {
		post('api/cyclic/' + this.variableSymbol, {'next_check': findDOMNode(this.refs.check).value}).then( (result) => {
			if(result.status == 200) {
				this.setState({cyclic: result.data, showDate: false});
			}
		})
	}

	render() {

		let date = new Date();
		const issueDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
		date.setDate(date.getDate() + 14);
		const paymentDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();

		let dialog = null;
		if(this.state.showDate) {
			dialog =
				<div className="modal" style={{display: 'block'}}>
					<div className="modal-dialog">
						<div className="modal-content">
							<div className="modal-header">
								<button type="button" className="close" onClick={this.handleCloseDialog.bind(this)}>&times;</button>
								<h4 className="modal-title">Příští odeslání výzvy</h4>
							</div>
							<div className="modal-body">
								<input type="date" ref="check" className="form-control" />
							</div>
							<div className="modal-footer">
								<button type="button" className="btn btn-default" onClick={this.handleSaveCyclicClick.bind(this)}>Uložit</button>
							</div>
						</div>

					</div>
				</div>
		}

		return(
			<div key={this.state.key} className="invoice container-fluid" >
				<div className="col-lg-6 col-md-12">
					<div className="panel panel-primary">
						<div className="panel-heading">Nová faktura/výzva k platbě</div>
						<div className="panel-body">
							<table className="form-group table">
								<thead className="thead-default">
									<tr><th colSpan="2">Obecné</th></tr>
								</thead>
								<tbody>
									<tr>
										<td>Typ: </td>
										<td>
											<div className="col-md-10">
												<select className="form-control" onChange={this.handleChange.bind(this)} value={this.state.type} ref="type">
													<option value="1">Výzva k platbě</option>
													<option value="2">Faktura</option>
												</select>
											</div>
										</td>
									</tr>
									<tr><td>Odběratel:</td><td><CustomerInput /></td></tr>
									<tr><td>Datum vystavení: </td><td><div className="col-md-10"><input className="form-control" type="text" ref="issueDate" defaultValue={issueDate} /></div></td></tr>
									<tr><td>Datum splatnosti: </td><td><div className="col-md-10"><input className="form-control" type="text" ref="paymentDate" defaultValue={paymentDate} /></div></td></tr>
									<tr><th colSpan="2">Položky</th></tr>
									<tr><td colSpan="2"><ItemList/></td></tr>
									<tr>
										<td colSpan="2">
											<button className="btn btn-primary" onClick={this.handleSaveClick.bind(this)}>Uložit {this.state.text}</button>
											&nbsp;&nbsp;&nbsp;&nbsp;
											<button className="btn btn-default" onClick={this.handleItemsClick.bind(this)}>Přidat k příští platbě</button>
											&nbsp;&nbsp;&nbsp;&nbsp;
											<button className="btn btn-danger" onClick={this.handleSendClick.bind(this)}>Odeslat {this.state.text} na {this.props.customer.email}</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div style={{float: 'right'}} className="col-md-6">
					<div className="panel panel-success">
						<div className="panel-heading">Vystavené faktury</div>
						<div className="panel-body">
							<InvoiceList data={this.state.invoices} invoice={true} onCycle={this.handleCycle.bind(this)} onDelete={this.handleDelete.bind(this)} />
						</div>
					</div>
				</div>
				<div style={{float: 'left'}} className="col-md-6">
					<div className="panel panel-danger">
						<div className="panel-heading">Nezaplacené výzvy k platbě</div>
						<div className="panel-body">
							<InvoiceList data={this.state.preinvoices} onCycle={this.handleCycle.bind(this)} onDelete={this.handleDelete.bind(this)} />
						</div>
					</div>
				</div>
				<div style={{float: 'left'}} className="col-md-6">
					<div className="panel panel-default">
						<div className="panel-heading">Opakující se výzvy k platbě</div>
						<div className="panel-body">
							<InvoiceList data={this.state.cyclic} cyclic={true} onDelete={this.handleDeleteCyclic.bind(this)} />
						</div>
					</div>
				</div>
				{dialog}
			</div>
			);
	}
}
