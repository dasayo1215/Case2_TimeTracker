export default function TimeRow({ record, mode, handleChange }) {
    const isReadOnly =
        ((mode === "user" || mode === "admin") && record.status === "pending") ||
        mode === "approval";

    return (
        <tr className="detail-row">
            <th className="detail-cell-head">出勤・退勤</th>
            <td className="detail-cell">
                {isReadOnly ? (
                    <div className="time-grid">
                        <span className="time-text">
                            {record.clock_in || "--:--"}
                        </span>
                        <span className="detail-separator">〜</span>
                        <span className="time-text">
                            {record.clock_out || "--:--"}
                        </span>
                    </div>
                ) : (
                    <div className="time-grid">
                        <input
                            className="detail-input"
                            type="time"
                            step="60"
                            value={record.clock_in || ""}
                            onChange={(e) =>
                                handleChange("clock_in", e.target.value)
                            }
                        />
                        <span className="detail-separator">〜</span>
                        <input
                            className="detail-input"
                            type="time"
                            step="60"
                            value={record.clock_out || ""}
                            onChange={(e) =>
                                handleChange("clock_out", e.target.value)
                            }
                        />
                    </div>
                )}
            </td>
        </tr>
    );
}
