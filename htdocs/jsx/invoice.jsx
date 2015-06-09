
class Invoice extends React.Component {

	handleDeleteClick() {
		if(confirm('Opravdu odstranit?')) {
			this.props.onDelete(this)
		}
	}

	render() {

		var row = this.props.data;

		var deleteIcon = null;
		if(this.props.index == 0) {
			deleteIcon = <img src="/images/cross.png" title="Odstranit" onClick={this.handleDeleteClick.bind(this)} />
		}

		return(
			<tr key={row.id}>
				<td><b>{row.id}</b><br />{row.issue_date}<br />{row.payment_date}</td>
				<td><b>{row.name}</b><br />{row.street} {row.number}<br />{row.post_code} {row.city}<br />IČ: {row.ico}<br />DIČ: {row.dic}</td>
				<td>
					<a href={"/homepage/invoice/" + row.variable_symbol_id} target="_blank"><img src="/images/page_white_acrobat.png" title="Stáhnout PDF" /></a>
					{deleteIcon}
				</td>
			</tr>
		);
	}
}