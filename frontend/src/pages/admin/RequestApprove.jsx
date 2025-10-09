import { useParams } from 'react-router-dom';
import AttendanceDetailView from '../../components/AttendanceDetailView';

export default function RequestApprove() {
	const { attendance_correct_request_id } = useParams();
	return (
		<AttendanceDetailView
			id={attendance_correct_request_id}
			apiBase="/api/admin"
			mode="approval"
		/>
	);
}
