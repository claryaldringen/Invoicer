
import React from 'react'

import Item from '../containers/Item';

export default class ItemList extends React.Component {

	render() {

		let items = [];
		for(let i = 0; i < this.props.count; i++) {
			items.push(<Item key={'item_' + i} index={i} />);
		}

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
						<th colSpan="3">Celková cena:</th><th>{this.props.total}</th>
					</tr>
				</tbody>
			</table>
		);
	}
}
