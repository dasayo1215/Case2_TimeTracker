import { useParams } from 'react-router-dom';
import AttendanceDetailView from '../../components/AttendanceDetailView';

export default function RequestApprove() {
	// 課題指定に合わせたURLパラメータ名
	const { attendance_correct_request_id } = useParams();

	// attendances.id として利用
	return (
		<AttendanceDetailView
			id={attendance_correct_request_id}
			apiBase="/api/admin"
			mode="approval"
		/>
	);
}
