
class InvoiceList extends React.Component {

	handleDelete(invoice) {
		this.props.onDelete(invoice);
	}

	render() {

		var invoices = this.props.data.map(function(invoice, key) {
			return(<Invoice key={key} index={key} data={invoice} onDelete={this.handleDelete.bind(this)} />);
		}.bind(this));

		return (
			<table>
				<tbody>
					{invoices}
				</tbody>
			</table>
		);
	}
}