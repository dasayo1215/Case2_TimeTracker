import { useParams } from 'react-router-dom';
import DetailView from "../../components/attendance/detail/View";

export default function AttendanceDetail() {
	const { id } = useParams();
	return <DetailView id={id} apiBase="/api" mode="user" />;
}
