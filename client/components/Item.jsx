
import React from 'react'

export default class Item extends React.Component {

	handleFocus() {
		this.props.addItem(this.props.index);
	}

	handleCountChange(element) {
		this.props.setItem({count: element.target.value, name: this.props.name, price: this.props.price}, this.props.index);
	}

	handleNameChange(element) {
		this.props.setItem({count: this.props.count, name: element.target.value, price: this.props.price}, this.props.index);
	}

	handlePriceChange(element) {
		this.props.setItem({count: this.props.count, name: this.props.name, price: element.target.value}, this.props.index);
	}

	render() {

		return(
			<tr onFocus={this.handleFocus.bind(this)}>
				<td><input type="number" className="form-control" style={{width: 72}} onChange={this.handleCountChange.bind(this)} value={this.props.count} /></td>
				<td><input type="text" className="form-control" style={{width: 384}} onChange={this.handleNameChange.bind(this)} value={this.props.name} /></td>
				<td><input type="number" className="form-control" style={{width: 96}} onChange={this.handlePriceChange.bind(this)} value={this.props.price} /></td>
				<td><input type="number" className="form-control" style={{width: 128}} value={this.props.total} /></td>
			</tr>
		);
	}
}
