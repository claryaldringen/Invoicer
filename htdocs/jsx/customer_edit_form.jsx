
class CustomerEditForm extends React.Component {

	handleSubmit(event) {
		event.preventDefault();

		this.props.customer.name = React.findDOMNode(this.refs.name).value;
		this.props.customer.street = React.findDOMNode(this.refs.street).value;
		this.props.customer.number = React.findDOMNode(this.refs.number).value;
		this.props.customer.city = React.findDOMNode(this.refs.city).value;
		this.props.customer.post_code = React.findDOMNode(this.refs.post_code).value;
		this.props.customer.ico = React.findDOMNode(this.refs.ico).value;
		this.props.customer.dic = React.findDOMNode(this.refs.dic).value;
		this.props.customer.email = React.findDOMNode(this.refs.email).value;

		var method = 'post';
		if(this.props.customer.id > 0) method = 'put';

		$.ajax({
			url: 'api/customers',
			method: method,
			dataType: 'json',
			data: this.props.customer,
			success: (data) => {
				this.props.customer.id = data.id;
				this.props.onSubmit(this);
			},
			error: (xhr, status, err) => {
				console.log(err);
			}
		});
	}

	render() {
		var customer = this.props.customer;

		return(
			<form onSubmit={this.handleSubmit.bind(this)}>
			<table>
				<tr><td>Jméno:</td><td><input type="text" ref="name" defaultValue={customer.name} /></td></tr>
				<tr><td>
					Ulice:</td><td><input type="text" ref="street" defaultValue={customer.street} />
					č.p.:<input type="text" ref="number" defaultValue={customer.number} /></td></tr>
				<tr><td>Město:</td><td><input type="text" ref="city" defaultValue={customer.city}/></td></tr>
				<tr><td>PSČ:</td><td><input type="text" ref="post_code" defaultValue={customer.post_code} /></td></tr>
				<tr><td>IČ:</td><td><input type="text" ref="ico" defaultValue={customer.ico} /></td></tr>
				<tr><td>DIČ:</td><td><input type="text" ref="dic" defaultValue={customer.dic} /></td></tr>
				<tr><td>Email:</td><td><input type="email" ref="email" defaultValue={customer.email} /></td></tr>
				<tr><td colSpan="2"><input type="submit" value="Uložit zákazníka" /></td></tr>
			</table>
			</form>
		);
	}

}
