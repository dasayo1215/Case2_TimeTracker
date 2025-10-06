import { useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import '../../css/attendance-detail.css';

export default function AttendanceDetail() {
	const { id } = useParams();
	const [record, setRecord] = useState(null);
	const [loading, setLoading] = useState(true);
	const [submitting, setSubmitting] = useState(false);
	const [errors, setErrors] = useState([]);

	// 秒を削除（"HH:MM" に整形）
	const toHHMM = (timeStr) => {
		if (!timeStr) return '';
		const parts = timeStr.split(':');
		return parts.length >= 2 ? `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}` : '';
	};

	// 秒を追加
	const addSeconds = (timeStr) => (timeStr ? `${timeStr}:00` : null);

	// 日付フォーマット
	const formatDate = (dateStr) => {
		if (!dateStr) return '';
		const d = new Date(dateStr);
		return `${d.getFullYear()}年　${d.getMonth() + 1}月${d.getDate()}日`;
	};

	// ===== 初回ロード =====
	useEffect(() => {
		const fetchData = async () => {
			try {
				await axios.get('/sanctum/csrf-cookie');
				const res = await axios.get(`/api/attendance/detail/${id}`);

				setRecord({
					user_name: res.data?.user_name ?? '（ログインユーザー）',
					date: res.data?.date ?? '',
					clock_in: toHHMM(res.data?.clock_in),
					clock_out: toHHMM(res.data?.clock_out),
					remarks: res.data?.remarks ?? '',
					breakTimes: (res.data?.breakTimes ?? []).map((b) => ({
						break_start: toHHMM(b?.break_start),
						break_end: toHHMM(b?.break_end),
					})),
					status: res.data?.status ?? 'normal',
				});
			} catch (err) {
				console.error('詳細取得失敗:', err);
				setRecord(null);
			} finally {
				setLoading(false);
			}
		};
		fetchData();
	}, [id]);

	// ===== 入力変更 =====
	const handleChange = (field, value) => {
		setRecord((prev) => ({ ...prev, [field]: value }));
	};

	const handleBreakChange = (index, field, value) => {
		setRecord((prev) => {
			const list = [...(prev.breakTimes ?? [])];
			if (!list[index]) list[index] = { break_start: '', break_end: '' };
			list[index][field] = value;
			return { ...prev, breakTimes: list };
		});
	};

	// ===== 修正申請 =====
	const handleSubmit = async () => {
		if (!record) return;
		setSubmitting(true);
		setErrors([]);
		try {
			await axios.get('/sanctum/csrf-cookie');

			const payload = {
				date: record.date,
				clock_in: addSeconds(record.clock_in),
				clock_out: addSeconds(record.clock_out),
				remarks: record.remarks || '',
				breakTimes: (record.breakTimes ?? [])
					.filter((b) => b.break_start || b.break_end)
					.map((b) => ({
						break_start: addSeconds(b.break_start),
						break_end: addSeconds(b.break_end),
					})),
			};

			await axios.post(`/api/attendance/update-or-create/${id}`, payload);

			setRecord((prev) => ({ ...prev, status: 'pending' }));
			alert('修正申請を送信しました！（pending）');
		} catch (err) {
			console.error('申請エラー:', err);
			if (err.response?.status === 422) {
				const errorData = err.response.data.errors;
				const flatMessages = Object.values(errorData).flat();
				setErrors(flatMessages);
			} else {
				setErrors(['予期しないエラーが発生しました']);
			}
		} finally {
			setSubmitting(false);
		}
	};

	// ===== 表示 =====
	return (
		<div className="attendance-detail">
			<div className="detail-container">
				<h2 className="detail-title">勤怠詳細</h2>

				{loading && <p className="detail-loading">読み込み中...</p>}
				{!loading && !record && <p className="detail-error">データが見つかりません。</p>}

				{!loading && record && (
					<>
						<table className="detail-table">
							<tbody>
								<tr className="detail-row">
									<th className="detail-cell-head">名前</th>
									<td className="detail-cell">{record.user_name}</td>
								</tr>

								<tr className="detail-row">
									<th className="detail-cell-head">日付</th>
									<td className="detail-cell">{formatDate(record.date)}</td>
								</tr>

								{/* ===== 出勤・退勤 ===== */}
								<tr className="detail-row">
									<th className="detail-cell-head">出勤・退勤</th>
									<td className="detail-cell">
										{record.status === 'pending' ? (
											<div className="time-grid">
												<span className="time-text">
													{record.clock_in || '--:--'}
												</span>
												<span className="detail-separator">〜</span>
												<span className="time-text">
													{record.clock_out || '--:--'}
												</span>
											</div>
										) : (
											<div className="time-grid">
												<input
													className="detail-input"
													type="time"
													step="60"
													value={record.clock_in || ''}
													onChange={(e) =>
														handleChange('clock_in', e.target.value)
													}
												/>
												<span className="detail-separator">〜</span>
												<input
													className="detail-input"
													type="time"
													step="60"
													value={record.clock_out || ''}
													onChange={(e) =>
														handleChange('clock_out', e.target.value)
													}
												/>
											</div>
										)}
									</td>
								</tr>

								{/* ===== 休憩 ===== */}
								{(() => {
									const breaks = [...(record.breakTimes ?? [])];
									breaks.push({ break_start: '', break_end: '' });

									return breaks.map((b, i) => {
										const label = i === 0 ? '休憩' : `休憩${i + 1}`;
										const isLastEmpty =
											!b.break_start &&
											!b.break_end &&
											i === breaks.length - 1;

										return (
											<tr key={i} className="detail-row">
												<th className="detail-cell-head">{label}</th>
												<td className="detail-cell">
													{record.status === 'pending' ? (
														isLastEmpty ? (
															<div className="time-grid">
																<span className="time-text time-placeholder">
																	00:00
																</span>
																<span className="detail-separator">
																	〜
																</span>
																<span className="time-text time-placeholder">
																	00:00
																</span>
															</div>
														) : (
															<div className="time-grid">
																<span className="time-text">
																	{b.break_start || '--:--'}
																</span>
																<span className="detail-separator">
																	〜
																</span>
																<span className="time-text">
																	{b.break_end || '--:--'}
																</span>
															</div>
														)
													) : (
														<div className="time-grid">
															<input
																className="detail-input"
																type="time"
																step="60"
																value={b.break_start || ''}
																onChange={(e) =>
																	handleBreakChange(
																		i,
																		'break_start',
																		e.target.value
																	)
																}
															/>
															<span className="detail-separator">
																〜
															</span>
															<input
																className="detail-input"
																type="time"
																step="60"
																value={b.break_end || ''}
																onChange={(e) =>
																	handleBreakChange(
																		i,
																		'break_end',
																		e.target.value
																	)
																}
															/>
														</div>
													)}
												</td>
											</tr>
										);
									});
								})()}

								<tr className="detail-row">
									<th className="detail-cell-head">備考</th>
									<td className="detail-cell">
										{record.status === 'pending' ? (
											<p>{record.remarks || '（なし）'}</p>
										) : (
											<textarea
												className="detail-textarea"
												value={record.remarks || ''}
												onChange={(e) =>
													handleChange('remarks', e.target.value)
												}
											></textarea>
										)}
									</td>
								</tr>
							</tbody>
						</table>

						{/* バリデーションエラー */}
						{errors.length > 0 && (
							<div className="detail-errors">
								<ul className="detail-errors-list">
									{errors.map((msg, i) => (
										<li key={i} className="detail-errors-item">
											{msg}
										</li>
									))}
								</ul>
							</div>
						)}

						{/* ステータス別UI */}
						{record.status === 'normal' && (
							<div className="detail-btn-box">
								<button
									className="detail-btn"
									onClick={handleSubmit}
									disabled={submitting}
								>
									{submitting ? '送信中…' : '修正'}
								</button>
							</div>
						)}

						{record.status === 'pending' && (
							<p className="detail-pending-msg">＊承認待ちのため修正はできません。</p>
						)}
					</>
				)}
			</div>
		</div>
	);
}
