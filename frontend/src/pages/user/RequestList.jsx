import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import '../../css/list-page.css';

export default function RequestList() {
	const [records, setRecords] = useState([]);
	const [loading, setLoading] = useState(true);
	// セッションに保存されたタブ状態を復元（なければ 'pending'）
	const [activeTab, setActiveTab] = useState(sessionStorage.getItem('requestTab') || 'pending');

	useEffect(() => {
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const res = await axios.get(`/api/attendance/requests?status=${activeTab}`);
				setRecords(res.data);
			} catch (error) {
				console.error('申請一覧の取得に失敗しました:', error);
			} finally {
				setLoading(false);
			}
		};
		fetchData();

		// タブ状態をセッションに保存
		sessionStorage.setItem('requestTab', activeTab);
	}, [activeTab]);

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
					<h2 className="list-title">申請一覧</h2>
					<div className="list-tab-box">
						<button
							className={`list-tab-button ${activeTab === 'pending' ? 'active' : ''}`}
							onClick={() => setActiveTab('pending')}
						>
							承認待ち
						</button>
						<button
							className={`list-tab-button ${
								activeTab === 'approved' ? 'active' : ''
							}`}
							onClick={() => setActiveTab('approved')}
						>
							承認済み
						</button>
					</div>
				</div>

				{/* ===== テーブル ===== */}
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
							{records.map((r) => {
								return (
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
												to={`/attendance/detail/${r.id}`}
											>
												詳細
											</Link>
										</td>
									</tr>
								);
							})}
						</tbody>
					</table>
				)}
			</div>
		</div>
	);
}
