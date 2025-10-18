import { Link } from "react-router-dom";

export default function RecordTable({ days, y, m, records, detailPathBase }) {
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        const weekday = ["日", "月", "火", "水", "木", "金", "土"][
            date.getDay()
        ];
        return `${mm}/${dd}(${weekday})`;
    };

    const formatTime = (t) => (t ? t.substring(0, 5) : "");
    const formatDuration = (min) => {
        if (min == null) return "";
        const h = Math.floor(min / 60);
        const mm = String(min % 60).padStart(2, "0");
        return `${h}:${mm}`;
    };

    return (
        <table className="list-table">
            <thead>
                <tr className="list-row list-row-head">
                    <th className="list-cell">日付</th>
                    <th className="list-cell">出勤</th>
                    <th className="list-cell">退勤</th>
                    <th className="list-cell">休憩</th>
                    <th className="list-cell">合計</th>
                    <th className="list-cell">詳細</th>
                </tr>
            </thead>
            <tbody>
                {days.map((day) => {
                    const dateStr = `${y}-${String(m).padStart(
                        2,
                        "0"
                    )}-${String(day).padStart(2, "0")}`;
                    const record = records.find((r) =>
                        r.date.startsWith(dateStr)
                    );
                    const detailId = record?.id ?? dateStr;

                    return (
                        <tr key={day} className="list-row">
                            <td className="list-cell">{formatDate(dateStr)}</td>
                            <td className="list-cell">
                                {formatTime(record?.clock_in)}
                            </td>
                            <td className="list-cell">
                                {formatTime(record?.clock_out)}
                            </td>
                            <td className="list-cell">
                                {formatDuration(record?.break_minutes)}
                            </td>
                            <td className="list-cell">
                                {formatDuration(record?.total_minutes)}
                            </td>
                            <td className="list-cell">
                                {record ? (
                                    <Link
                                        className="list-detail-link"
                                        to={`${detailPathBase}/${detailId}`}
                                    >
                                        詳細
                                    </Link>
                                ) : (
                                    ""
                                )}
                            </td>
                        </tr>
                    );
                })}
            </tbody>
        </table>
    );
}
