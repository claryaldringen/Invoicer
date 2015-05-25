
class Item extends React.Component {

	constructor(props) {
		super(props);
		this.state = {
			count: props.item.count,
			price: props.item.price
		};
	}

	handleFocus(event) {
		this.props.onFocus(this)
	}

	handleChange() {
		this.props.onChange(this)
	}

	handleCountChange(element) {
		this.setState({count: element.target.value});
		this.props.item.count = element.target.value;
	}

	handleNameChange(element) {
		this.props.item.name = element.target.value;
	}

	handlePriceChange(element) {
		this.setState({price: element.target.value});
		this.props.item.price = element.target.value;
	}

	render() {
		var item = this.state;

		return(
			<tr onFocus={this.handleFocus.bind(this)} onChange={this.handleChange.bind(this)}>
				<td><input type="number" onChange={this.handleCountChange.bind(this)} value={this.state.count} /></td>
				<td><input type="text" onChange={this.handleNameChange.bind(this)} value={item.name} /></td>
				<td><input type="number" onChange={this.handlePriceChange.bind(this)} value={this.state.price} /></td>
				<td><input type="number" value={item.price * item.count} /></td>
			</tr>
		);
	}
}
