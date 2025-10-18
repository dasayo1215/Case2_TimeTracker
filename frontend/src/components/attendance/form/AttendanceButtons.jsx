export default function AttendanceButtons({ status, handleClock }) {
    switch (status) {
        case '勤務外':
            return (
                <button
                    type="button"
                    className="attendance-btn"
                    onClick={(e) => handleClock('clock_in', '出勤中', e)}
                >
                    出勤
                </button>
            );
        case '出勤中':
            return (
                <div className="attendance-btn-group">
                    <button
                        type="button"
                        className="attendance-btn"
                        onClick={(e) => handleClock('clock_out', '退勤済', e)}
                    >
                        退勤
                    </button>
                    <button
                        type="button"
                        className="attendance-btn secondary"
                        onClick={(e) => handleClock('break_start', '休憩中', e)}
                    >
                        休憩入
                    </button>
                </div>
            );
        case '休憩中':
            return (
                <button
                    type="button"
                    className="attendance-btn secondary"
                    onClick={(e) => handleClock('break_end', '出勤中', e)}
                >
                    休憩戻
                </button>
            );
        case '退勤済':
            return <p className="attendance-message">お疲れ様でした。</p>;
        case '取得失敗':
            return <p className="attendance-message error">状態を取得できませんでした。</p>;
        default:
            return null;
    }
}
