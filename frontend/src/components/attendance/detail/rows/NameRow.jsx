export default function NameRow({ record }) {
    return (
        <tr className="detail-row">
            <th className="detail-cell-head">名前</th>
            <td className="detail-cell">{record.user_name}</td>
        </tr>
    );
}
