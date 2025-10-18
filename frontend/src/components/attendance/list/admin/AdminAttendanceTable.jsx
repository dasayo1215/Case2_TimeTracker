import { Link } from 'react-router-dom';

export default function AdminAttendanceTable({ records, loading }) {
    const formatTime = (t) => (t ? t.substring(0, 5) : '');
    const formatDuration = (min) => {
        if (min == null) return '';
        const h = Math.floor(min / 60);
        const m = min % 60;
        return `${h}:${String(m).padStart(2, '0')}`;
    };

    if (loading) {
        return <p className="list-loading">読み込み中...</p>;
    }

    if (records.length === 0) {
        return (
            <table className="list-table">
                <tbody>
                    <tr>
                        <td colSpan="6" className="list-cell">
                            データがありません
                        </td>
                    </tr>
                </tbody>
            </table>
        );
    }

    return (
        <table className="list-table">
            <thead>
                <tr className="list-row list-row-head">
                    <th className="list-cell">名前</th>
                    <th className="list-cell">出勤</th>
                    <th className="list-cell">退勤</th>
                    <th className="list-cell">休憩</th>
                    <th className="list-cell">合計</th>
                    <th className="list-cell">詳細</th>
                </tr>
            </thead>
            <tbody>
                {records.map((r) => (
                    <tr key={r.id} className="list-row">
                        <td className="list-cell">{r.user?.name}</td>
                        <td className="list-cell">{formatTime(r.clock_in)}</td>
                        <td className="list-cell">{formatTime(r.clock_out)}</td>
                        <td className="list-cell">{formatDuration(r.break_minutes)}</td>
                        <td className="list-cell">{formatDuration(r.total_minutes)}</td>
                        <td className="list-cell">
                            <Link
                                className="list-detail-link"
                                to={`/admin/attendance/${r.id}`}
                            >
                                詳細
                            </Link>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
