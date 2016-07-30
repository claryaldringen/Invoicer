
class Invoice extends React.Component {

	handleDeleteClick() {
		if(confirm('Opravdu odstranit?')) {
			this.props.onDelete(this)
		}
	}

	render() {

		var row = this.props.data;

		var deleteIcon = null;
		if(this.props.index == 0 || !this.props.invoice) {
			deleteIcon = <img src="/images/cross.png" style={{cursor: 'pointer'}} title="Odstranit" onClick={this.handleDeleteClick.bind(this)} />
		}

		var acrobatIcon = null
		if(this.props.invoice) {
			acrobatIcon = <a href={"/homepage/invoice/" + row.variable_symbol_id} target="_blank"><img src="/images/page_white_acrobat.png" title="Stáhnout PDF"/></a>
		}

		var issueDate = row.issue_date.date.split(' ')[0].split('-');
		issueDate = issueDate[2] + '.' + issueDate[1] + '.' + issueDate[0];

		var paymentDate = '';
		if(row.payment_date != null) {
			paymentDate = row.payment_date.date.split(' ')[0].split('-');
			paymentDate = paymentDate[2] + '.' + paymentDate[1] + '.' + paymentDate[0];
		}

		return(
			<tr key={row.id}>
				<td><b>Č. {row.id}</b><br />{issueDate}<br />{paymentDate}<br /><b>VS: {row.variable_symbol_id}</b></td>
				<td><b>{row.name}</b><br />{row.street} {row.number}<br />{row.post_code} {row.city}<br />IČ: {row.ico}<br />DIČ: {row.dic}</td>
				<td><Items data={this.props.data.items} /></td>
				<td>{acrobatIcon}{deleteIcon}</td>
			</tr>
		);
	}
}