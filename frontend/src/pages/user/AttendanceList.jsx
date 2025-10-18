import AttendanceListBase from "../../components/attendance/list/AttendanceListBase";

export default function AttendanceList() {
    return (
        <AttendanceListBase
            apiEndpoint="/api/attendance/list"
            title="勤怠一覧"
            detailPathBase="/attendance/detail"
        />
    );
}
