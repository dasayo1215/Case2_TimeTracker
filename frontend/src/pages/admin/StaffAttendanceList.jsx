import { useParams } from 'react-router-dom';
import { useState } from 'react';
import AttendanceListBase from "../../components/attendance/list/AttendanceListBase";

export default function StaffAttendanceList() {
	const { id } = useParams();
	const [staffName, setStaffName] = useState('');

	return (
		<AttendanceListBase
			apiEndpoint={`/api/admin/attendance/staff/${id}`}
			title={staffName ? `${staffName}さんの勤怠` : '勤怠一覧'}
			detailPathBase={'/admin/attendance'}
			onLoaded={(staff) => setStaffName(staff.name)}
			showCsv={true}
		/>
	);
}
