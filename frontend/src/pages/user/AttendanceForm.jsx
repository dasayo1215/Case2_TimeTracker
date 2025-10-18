import ClockDisplay from '../../components/attendance/form/ClockDisplay';
import AttendanceButtons from '../../components/attendance/form/AttendanceButtons';
import useAttendanceForm from '../../components/attendance/form/useAttendanceForm';
import '../../css/attendance-form.css';

export default function AttendanceForm() {
    const { status, loading, currentTime, dateStr, weekday, handleClock } = useAttendanceForm();

    if (loading) {
        return (
            <div className="attendance-page">
                <div className="attendance-container">
                    <p className="attendance-loading">読み込み中...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="attendance-page">
            <h2 className="attendance-status">{status}</h2>
            <ClockDisplay dateStr={dateStr} weekday={weekday} time={currentTime} />
            <div className="attendance-buttons">
                <AttendanceButtons status={status} handleClock={handleClock} />
            </div>
        </div>
    );
}
