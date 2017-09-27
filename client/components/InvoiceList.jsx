
import React from 'react';

import Invoice from './Invoice';

export default class InvoiceList extends React.Component {

	handleDelete(invoice) {
		this.props.onDelete(invoice);
	}

	handleCycle(invoice) {
		this.props.onCycle(invoice)
	}

	render() {

		var invoices = this.props.data.map(function(invoice, key) {
			return(<Invoice key={key} index={key} data={invoice} invoice={this.props.invoice} cyclic={this.props.cyclic} onCycle={this.handleCycle.bind(this)} onDelete={this.handleDelete.bind(this)} />);
		}.bind(this));

		return (
			<table className="table table-striped table-bordered">
				<tbody>
					{invoices}
				</tbody>
			</table>
		);
	}
}