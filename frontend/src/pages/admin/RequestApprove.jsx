import { useParams } from 'react-router-dom';
import DetailView from "../../components/attendance/detail/View";

export default function RequestApprove() {
	const { attendance_correct_request_id } = useParams();
	return (
		<DetailView
			id={attendance_correct_request_id}
			apiBase="/api/admin"
			mode="approval"
		/>
	);
}
