import { useEffect, useState } from 'react';
import axios from '../axios';
import { Link, useSearchParams } from 'react-router-dom';
import '../css/list-page.css';

export default function RequestListBase({ apiEndpoint, detailPathBase, title }) {
	const [records, setRecords] = useState([]);
	const [loading, setLoading] = useState(true);
	const [searchParams, setSearchParams] = useSearchParams();
    const [activeTab, setActiveTab] = useState(searchParams.get('status') || 'pending');

	useEffect(() => {
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const res = await axios.get(`${apiEndpoint}?status=${activeTab}`);
				setRecords(Array.isArray(res.data.records) ? res.data.records : []);
			} catch (error) {
				console.error('申請一覧の取得に失敗しました:', error);
			} finally {
				setLoading(false);
			}
		};
        fetchData();
    }, [activeTab, apiEndpoint]);

    useEffect(() => {
        const urlStatus = searchParams.get('status') || 'pending';
        if (urlStatus !== activeTab) {
            setActiveTab(urlStatus);
        }
    }, [searchParams]);

	const formatDate = (dateStr) => {
		const date = new Date(dateStr);
		const yyyy = date.getFullYear();
		const mm = String(date.getMonth() + 1).padStart(2, '0');
		const dd = String(date.getDate()).padStart(2, '0');
		return `${yyyy}/${mm}/${dd}`;
	};

	return (
		<div className="list-page">
			<div className="list-container">
				<div className="list-header">
					<h2 className="list-title">{title}</h2>
					<div className="list-tab-box">
						<button
							className={`list-tab-button ${activeTab === 'pending' ? 'active' : ''}`}
                            onClick={() => {
                                if (activeTab !== 'pending') {
                                    setSearchParams({ status: 'pending' });
                                }
                            }}
						>
							承認待ち
						</button>
						<button
							className={`list-tab-button ${
								activeTab === 'approved' ? 'active' : ''
							}`}
                            onClick={() => {
                                if (activeTab !== 'approved') {
                                    setSearchParams({ status: 'approved' });
                                }
                            }}
						>
							承認済み
						</button>
					</div>
				</div>

				{loading ? (
					<p className="list-loading">読み込み中...</p>
				) : (
					<table className="list-table">
						<thead>
							<tr className="list-row list-row-head">
								<th className="list-cell">状態</th>
								<th className="list-cell">名前</th>
								<th className="list-cell">対象日時</th>
								<th className="list-cell">申請理由</th>
								<th className="list-cell">申請日時</th>
								<th className="list-cell">詳細</th>
							</tr>
						</thead>
						<tbody>
							{records.map((r) => (
								<tr key={r.id} className="list-row">
									<td className="list-cell">
										{r.status === 'pending' ? '承認待ち' : '承認済み'}
									</td>
									<td className="list-cell">{r.user.name}</td>
									<td className="list-cell">{formatDate(r.work_date)}</td>
									<td className="list-cell">{r.remarks}</td>
									<td className="list-cell">{formatDate(r.submitted_at)}</td>
									<td className="list-cell">
										<Link
											className="list-detail-link"
											to={`${detailPathBase}/${r.id}`}
										>
											詳細
										</Link>
									</td>
								</tr>
							))}
						</tbody>
					</table>
				)}
			</div>
		</div>
	);
}
