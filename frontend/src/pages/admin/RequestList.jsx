import RequestListBase from '../../components/RequestListBase';

export default function AdminRequestList() {
	return (
		<RequestListBase
			apiEndpoint="/api/admin/attendance/requests"
			detailPathBase="/stamp_correction_request/approve"
			title="申請一覧"
		/>
	);
}
