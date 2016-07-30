
class InvoiceList extends React.Component {

	handleDelete(invoice) {
		this.props.onDelete(invoice);
	}

	render() {

		var invoices = this.props.data.map(function(invoice, key) {
			return(<Invoice key={key} index={key} data={invoice} invoice={this.props.invoice} onDelete={this.handleDelete.bind(this)} />);
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