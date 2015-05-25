
class ItemList extends React.Component {

	constructor(props) {
		super(props);
		this.state = {items: [{count: 1, name: '', price: ''}], total: 0};
	}

	handleFocus(element) {
		if(element.props.index == this.state.items.length-1) {
			var items = this.state.items;
			items.push({count: 1, name: '', price: ''});
			this.setState({items: items});
		}
	}

	handleChange(element) {
		var total = 0
		for(var i = 0; i < this.state.items.length; i++) {
			var item = this.state.items[i];
			total += item.count*item.price;
		}
		this.setState({total: total});
		this.props.onChange(this);
	}

	render() {
		var items = this.state.items.map(function(item, key){
			return <Item item={item} key={key} index={key} onFocus={this.handleFocus.bind(this)} onChange={this.handleChange.bind(this)}/>;
		}.bind(this));

		return(
			<table>
				<thead>
					<tr>
						<th>Množství</th>
						<th>Název položky</th>
						<th>Cena 1ks</th>
						<th>Cena celkem</th>
					</tr>
				</thead>
				<tbody>
					{items}
					<tr>
						<th colSpan="3">Celková cena:</th><th>{this.state.total}</th>
					</tr>
				</tbody>
			</table>
		);
	}
}
