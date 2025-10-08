import RequestListBase from '../../components/RequestListBase';

export default function RequestList() {
	return (
		<RequestListBase
			apiEndpoint="/api/attendance/requests"
			detailPathBase="/attendance/detail"
			title="申請一覧"
		/>
	);
}
