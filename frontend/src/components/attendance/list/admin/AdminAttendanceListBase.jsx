import { FaRegCalendarAlt } from 'react-icons/fa';
import DatePicker from 'react-datepicker';
import { ja } from 'date-fns/locale';
import 'react-datepicker/dist/react-datepicker.css';
import '../../../../css/list-page.css';
import AdminAttendanceTable from './AdminAttendanceTable';

export default function AdminAttendanceListBase({
    records,
    loading,
    selectedDate,
    setSelectedDate,
}) {
    const formatDateHeader = (date) => {
        const y = date.getFullYear();
        const m = date.getMonth() + 1;
        const d = date.getDate();
        return `${y}年${m}月${d}日の勤怠`;
    };

    const handlePrevDay = () => {
        const newDate = new Date(selectedDate);
        newDate.setDate(selectedDate.getDate() - 1);
        setSelectedDate(newDate);
    };

    const handleNextDay = () => {
        const newDate = new Date(selectedDate);
        newDate.setDate(selectedDate.getDate() + 1);
        setSelectedDate(newDate);
    };

    return (
        <div className="list-page">
            <div className="list-container">
                <div className="list-header">
                    <h2 className="list-title">
                        {selectedDate ? formatDateHeader(selectedDate) : '読み込み中...'}
                    </h2>

                    <div className="list-nav-box">
                        <button className="list-nav-btn gray" onClick={handlePrevDay}>
                            ← 前日
                        </button>

                        <div
                            className="list-nav-center"
                            onClick={() =>
                                document.querySelector('.list-nav-input')?.focus()
                            }
                        >
                            <FaRegCalendarAlt className="list-nav-calendar-icon" />
                            {selectedDate && (
                                <DatePicker
                                    selected={selectedDate}
                                    onChange={(date) => setSelectedDate(date)}
                                    dateFormat="yyyy/MM/dd"
                                    locale={ja}
                                    className="list-nav-input"
                                />
                            )}
                        </div>

                        <button className="list-nav-btn gray" onClick={handleNextDay}>
                            翌日 →
                        </button>
                    </div>
                </div>

                <AdminAttendanceTable records={records} loading={loading} />
            </div>
        </div>
    );
}
