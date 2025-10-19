export default function RemarksRow({ record, mode, handleChange }) {
    const isReadOnly =
        ((mode === "user" || mode === "admin") && record.status === "pending") ||
        mode === "approval";

    return (
        <tr className="detail-row">
            <th className="detail-cell-head">備考</th>
            <td className="detail-cell">
                {isReadOnly ? (
                    <p>{record.remarks || "（なし）"}</p>
                ) : (
                    <textarea
                        className="detail-textarea"
                        value={record.remarks || ""}
                        onChange={(e) =>
                            handleChange("remarks", e.target.value)
                        }
                    ></textarea>
                )}
            </td>
        </tr>
    );
}
