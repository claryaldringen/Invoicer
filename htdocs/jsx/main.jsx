
class InvoiceForm extends React.Component {

	constructor(props) {
		super(props);
		this.state = {key: 0, text: 'výzvu k platbě'};
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

	sendToServer(data) {
		$.ajax({
			url: 'api/invoices',
			method: 'post',
			dataType: 'json',
			data: data,
			success: (data) => {
				var key = this.state.key;
				key++;
				this.setState({key: key});
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

	handleSaveClick() {
		this.sendToServer(this.getData());
	}

	handleSendClick() {
		var data = this.getData();
		data.send = true;
		this.sendToServer(data);
	}

	render() {

		var date = new Date();
		var issueDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();
		date.setDate(date.getDate() + 14);
		var paymentDate = date.getDate() + '.' + (date.getMonth()+1) + '.' + date.getFullYear();

		return(
			<div key={this.state.key} className="invoice">
				<table>
					<tr><th colSpan="2">Obecné</th></tr>
					<tr>
						<td>Typ: </td>
						<td>
							<select onChange={this.handleChange.bind(this)} ref="type">
								<option value="1">Výzva k platbě</option>
								<option value="2">Faktura</option>
							</select>
						</td>
					</tr>
					<tr><td>Odběratel:</td><td><CustomerInput onChange={this.handleCustomerChange.bind(this)} source="api/customers" /></td></tr>
					<tr><td>Datum vystavení: </td><td><input type="text" ref="issueDate" value={issueDate} /></td></tr>
					<tr><td>Datum splatnosti: </td><td><input type="text" ref="paymentDate" value={paymentDate} /></td></tr>
					<tr><th colSpan="2">Položky</th></tr>
					<tr><td colSpan="2"><ItemList onChange={this.handleItemListChange.bind(this)}/></td></tr>
					<tr>
						<td><button onClick={this.handleSaveClick.bind(this)}>Uložit {this.state.text}</button></td>
						<td><button onClick={this.handleSendClick.bind(this)}>Uložit {this.state.text} a odeslat na {this.state.email}</button></td>
					</tr>
				</table>
			</div>);
	}
}

document.addEventListener('DOMContentLoaded', function(){
	React.render(<InvoiceForm />, document.getElementById('invoice'));
});