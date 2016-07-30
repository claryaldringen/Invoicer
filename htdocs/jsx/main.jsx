
class InvoiceForm extends React.Component {

	constructor(props) {
		super(props);
		this.state = {key: 0, text: 'výzvu k platbě', invoices: [], preinvoices: []};
	}

	handleChange(event) {
		if(event.target.value == 1) {
			this.setState({text: 'výzvu k platbě'})
		} else {
			this.setState({text: 'fakturu'})
		}
	}

	handleItemListChange(element) {
		this.props.items = [];
		for(var i = 0; i < element.state.items.length; i++) {
			var item = element.state.items[i];
			if(item.name != null && item.name != '') this.props.items.push(item);
		}
	}

	handleCustomerChange(customerInput) {
		var customers = customerInput.state.customers;
		for(var i = 0; i < customers.length; i++) {
			var customer = customers[i];
			if(customer.id == customerInput.props.customerId) {
				this.setState({email: customer.email});
				this.props.customerId = customer.id;
				break;
			}
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
		var data = {
			customerId: this.props.customerId,
			issueDate: React.findDOMNode(this.refs.issueDate).value,
			paymentDate: React.findDOMNode(this.refs.paymentDate).value,
			type: React.findDOMNode(this.refs.type).value,
			items: this.props.items,
			send: false
		};
		return data;
	}

	loadInvoicesFromServer() {
		$.ajax({
			url: 'api/invoices',
			method: 'get',
			dataType: 'json',
			success: (data) => {
				this.setState({invoices: data});
			},
			error: (xhr, status, err) => {
				console.log(err);
			}
		});
	}

	loadPreInvoicesFromServer() {
		$.ajax({
			url: 'api/preinvoices',
			method: 'get',
			dataType: 'json',
			success: (data) => {
				this.setState({preinvoices: data});
			},
			error: (xhr, status, err) => {
				console.log(err);
			}
		});
	}

	componentDidMount() {
		this.loadInvoicesFromServer();
		this.loadPreInvoicesFromServer();
	}

	handleSaveClick() {
		if(confirm('Opravdu vytvořit?')) {
			this.sendToServer('api/invoices', this.getData(), 'post');
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
		var id = invoice.props.data.variable_symbol_id ? invoice.props.data.variable_symbol_id : invoice.props.data.id;
		this.sendToServer('api/invoices/' + id, {}, 'delete');
	}

	render() {

		var date = new Date();
		var issueDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
		date.setDate(date.getDate() + 14);
		var paymentDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();

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
												<select className="form-control" onChange={this.handleChange.bind(this)} ref="type">
													<option value="1">Výzva k platbě</option>
													<option value="2">Faktura</option>
												</select>
											</div>
										</td>
									</tr>
									<tr><td>Odběratel:</td><td><CustomerInput onChange={this.handleCustomerChange.bind(this)} source="api/customers" /></td></tr>
									<tr><td>Datum vystavení: </td><td><div className="col-md-10"><input className="form-control" type="text" ref="issueDate" defaultValue={issueDate} /></div></td></tr>
									<tr><td>Datum splatnosti: </td><td><div className="col-md-10"><input className="form-control" type="text" ref="paymentDate" defaultValue={paymentDate} /></div></td></tr>
									<tr><th colSpan="2">Položky</th></tr>
									<tr><td colSpan="2"><ItemList onChange={this.handleItemListChange.bind(this)}/></td></tr>
									<tr>
										<td><button className="btn btn-primary" onClick={this.handleSaveClick.bind(this)}>Uložit {this.state.text}</button></td>
										<td><button className="btn btn-danger" onClick={this.handleSendClick.bind(this)}>Uložit {this.state.text} a odeslat na {this.state.email}</button></td>
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
							<InvoiceList data={this.state.invoices} invoice={true} onDelete={this.handleDelete.bind(this)} />
						</div>
					</div>
				</div>
				<div style={{float: 'left'}} className="col-md-6">
					<div className="panel panel-danger">
						<div className="panel-heading">Nezaplacené výzvy k platbě</div>
						<div className="panel-body">
							<InvoiceList data={this.state.preinvoices} onDelete={this.handleDelete.bind(this)} />
						</div>
					</div>
				</div>
			</div>
			);
	}
}

document.addEventListener('DOMContentLoaded', function(){
	React.render(<InvoiceForm />, document.getElementById('invoice'));
});