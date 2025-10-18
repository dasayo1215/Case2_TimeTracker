export default function BreakRows({ record, mode, handleBreakChange }) {
    const breaks = [
        ...(record.breakTimes ?? []),
        { break_start: "", break_end: "" },
    ];

    const isReadOnlyMode =
        (mode === "user" && record.status === "pending") || mode === "approval";

    const renderReadOnlyRow = (b, isLastEmpty) => (
        <div className="time-grid">
            {isLastEmpty ? (
                <>
                    <span className="time-text time-placeholder">00:00</span>
                    <span className="detail-separator">〜</span>
                    <span className="time-text time-placeholder">00:00</span>
                </>
            ) : (
                <>
                    <span className="time-text">
                        {b.break_start || "--:--"}
                    </span>
                    <span className="detail-separator">〜</span>
                    <span className="time-text">{b.break_end || "--:--"}</span>
                </>
            )}
        </div>
    );

    const renderEditableRow = (b, i) => (
        <div className="time-grid">
            <input
                className="detail-input"
                type="time"
                step="60"
                value={b.break_start || ""}
                onChange={(e) =>
                    handleBreakChange(i, "break_start", e.target.value)
                }
            />
            <span className="detail-separator">〜</span>
            <input
                className="detail-input"
                type="time"
                step="60"
                value={b.break_end || ""}
                onChange={(e) =>
                    handleBreakChange(i, "break_end", e.target.value)
                }
            />
        </div>
    );

    return (
        <>
            {breaks.map((b, i) => {
                const label = i === 0 ? "休憩" : `休憩${i + 1}`;
                const isLastEmpty =
                    !b.break_start && !b.break_end && i === breaks.length - 1;

                return (
                    <tr key={i} className="detail-row">
                        <th className="detail-cell-head">{label}</th>
                        <td className="detail-cell">
                            {isReadOnlyMode
                                ? renderReadOnlyRow(b, isLastEmpty)
                                : renderEditableRow(b, i)}
                        </td>
                    </tr>
                );
            })}
        </>
    );
}
