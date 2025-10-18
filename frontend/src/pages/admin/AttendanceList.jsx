import useAdminAttendanceList from '../../components/attendance/list/admin/useAdminAttendanceList';
import AdminAttendanceListBase from '../../components/attendance/list/admin/AdminAttendanceListBase';

export default function AdminAttendanceList() {
    const { records, loading, selectedDate, setSelectedDate } =
        useAdminAttendanceList();

    return (
        <AdminAttendanceListBase
            records={records}
            loading={loading}
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
        />
    );
}
