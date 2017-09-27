
import React from 'react';

export default class Items extends React.Component {

    render() {

        var rows = [];
        var total = 0;
        for(let i = 0; i < this.props.data.length; i++) {
            let item = this.props.data[i];
            rows.push(<tr key={i}><td>{item.count}x</td><td>{item.name}</td><td>{item.count*item.price}&nbsp;Kč</td></tr>);
            total += item.count*item.price;
        }

        return(
            <table className="table" style={{fontSize: 10}}>
                <tbody>
                {rows}
                </tbody>
                <tfoot>
                    <td colSpan="2"><b>Cena celkem:</b></td><td><b>{total}&nbsp;Kč</b></td>
                </tfoot>
            </table>
        );
    }
}