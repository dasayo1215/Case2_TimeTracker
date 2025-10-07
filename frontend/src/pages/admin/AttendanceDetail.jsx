import { useParams } from 'react-router-dom';
import AttendanceDetailView from '../../components/AttendanceDetailView';

export default function AdminAttendanceDetail() {
	const { id } = useParams();
	return <AttendanceDetailView id={id} apiBase="/api/admin" />;
}
