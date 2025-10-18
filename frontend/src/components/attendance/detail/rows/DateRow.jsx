export default function DateRow({ dateText }) {
    return (
        <tr className="detail-row">
            <th className="detail-cell-head">日付</th>
            <td className="detail-cell">{dateText}</td>
        </tr>
    );
}
