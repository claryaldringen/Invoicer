
class CustomerInput extends React.Component {

	constructor(props) {
		super(props);
		this.state = {
			customers: [],
			showEditForm: false
		}
	}

	loadFromServer() {
		$.ajax({
			url: this.props.source,
			method: 'get',
			dataType: 'json',
			success: (data) => {
				this.setState({customers: data, customerId: data[0].id});
				this.props.customerId = data[0].id;
				this.props.onChange(this);
			},
			error: (xhr, status, err) => {
				console.log(err);
			}
		});
	}

	componentDidMount() {
		this.loadFromServer();
	}

	handleChange(event) {
		var show;
		if(event.target.value == 0) {
			show = true
		} else {
			show = false
		}

		this.setState({showEditForm: show, customerId: event.target.value});
		this.props.customerId = event.target.value;
		this.props.onChange(this);
	}

	handleClick(event) {
		if(this.state.showEditForm) {
			this.setState({showEditForm: false});
		} else {
			this.setState({showEditForm: true});
		}
	}

	handleEditFormSubmit(editForm) {
		var customerId = editForm.props.customer.id;
		if(customerId > 0) {
			var customers = this.state.customers;
			customers.push(editForm.props.customer);
			this.setState({customers: customers, customerId: editForm.props.customer.id});
			this.setState({showEditForm: false});
		}
	}

	render() {
		var options = this.state.customers.map(function(customer, key){
			return <option key={key} value={customer.id}>{customer.name}</option>;
		});

		var customer = {id: 0};
		for(var i = 0; i < this.state.customers.length; i++) {
			if(this.state.customers[i].id == this.state.customerId) {
				customer = this.state.customers[i];
				break;
			}
		}

		var editForm;
		var text = 'Upravit';
		if(this.state.showEditForm) {
			editForm = <CustomerEditForm onSubmit={this.handleEditFormSubmit.bind(this)} customer={customer}/>;
			text = 'Zavřít'
		}

		var buttonStyle = {display: 'block'};
		if(this.state.customerId == 0) {
			buttonStyle = {display: 'none'};
		}

		return(
			<div>
				<select onChange={this.handleChange.bind(this)} value={this.state.customerId}>
					{options}
					<option value="0" key={this.state.customers.length}>Nový zákazník</option>
				</select>
				<button style={buttonStyle} onClick={this.handleClick.bind(this)}>{text}</button>
				{editForm}
			</div>
		);
	}

}
