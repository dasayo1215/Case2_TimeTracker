import { useParams } from 'react-router-dom';
import DetailView from "../../components/attendance/detail/View";


export default function AdminAttendanceDetail() {
	const { id } = useParams();
	return <DetailView id={id} apiBase="/api/admin" mode="admin" />;
}
