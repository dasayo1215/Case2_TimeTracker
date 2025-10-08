import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import 'react-datepicker/dist/react-datepicker.css';
import '../../css/list-page.css';

export default function AdminAttendanceList() {
	const [records, setRecords] = useState([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const res = await axios.get('/api/admin/staff/list');
				setRecords(res.data);
			} catch (error) {
				console.error('スタッフ一覧の取得に失敗しました:', error);
			} finally {
				setLoading(false);
			}
		};
		fetchData();
	}, []);

	return (
		<div className="list-page">
			<div className="list-container">
				<div className="list-header">
					<h2 className="list-title">スタッフ一覧</h2>
				</div>

				{/* ===== テーブル ===== */}
				{loading ? (
					<p className="list-loading">読み込み中...</p>
				) : (
					<table className="list-table">
						<thead>
							<tr className="list-row list-row-head">
								<th className="list-cell">名前</th>
								<th className="list-cell">メールアドレス</th>
								<th className="list-cell">月次勤怠</th>
							</tr>
						</thead>
						<tbody>
							{records.length === 0 ? (
								<tr>
									<td colSpan="6" className="list-cell">
										データがありません
									</td>
								</tr>
							) : (
								records.map((record) => (
									<tr key={record.id} className="list-row">
										<td className="list-cell">{record.name}</td>
										<td className="list-cell">{record.email}</td>
										<td className="list-cell">
											<Link
												className="list-detail-link"
												to={`/admin/attendance/staff/${record.id}`}
											>
												詳細
											</Link>
										</td>
									</tr>
								))
							)}
						</tbody>
					</table>
				)}
			</div>
		</div>
	);
}
