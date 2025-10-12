import { useEffect, useState } from 'react';
import axios from 'axios';
import '../css/attendance-detail.css';

export default function AttendanceDetailView({ id, apiBase, mode = 'normal' }) {
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

				// ユーザー情報を取得
				const userRes = await axios.get(
					apiBase.includes('/admin') ? '/api/admin/user' : '/api/user'
				);
				const loginUser = userRes.data;

				// 勤怠詳細データを取得
				let res = null;
				try {
					res = await axios.get(
						apiBase.includes('/admin')
							? `${apiBase}/attendance/${id}`
							: `${apiBase}/attendance/detail/${id}`
					);
				} catch {
					// 404などの場合は無視
				}

				if (res?.data) {
					const data = res.data;
					const userName =
						data?.user?.name ??
						data?.user_name ??
						loginUser.name ??
						'（ユーザー名）';

					setRecord({
						user_name: userName,
						user_id: data?.user_id ?? data?.user?.id ?? loginUser.id ?? null,
						date: data?.date ?? data?.work_date ?? id ?? '',
						clock_in: toHHMM(data?.clock_in),
						clock_out: toHHMM(data?.clock_out),
						remarks: data?.remarks ?? '',
						breakTimes: (data?.break_times ?? []).map((b) => ({
							break_start: toHHMM(b?.break_start),
							break_end: toHHMM(b?.break_end),
						})),
						status: data?.status ?? 'normal',
					});
				} else {
					setRecord(null);
					return;
				}
			} catch (err) {
				console.error('詳細取得失敗:', err);
				setRecord(null);
			} finally {
				setLoading(false);
			}
		};

		fetchData();
	}, [apiBase, id]);

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
				user_id: record.user_id,
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

			const url = `${apiBase}/attendance/update-or-create/${id}`;

			await axios.post(url, payload);

			if (apiBase.includes('/admin')) {
				setRecord((prev) => ({ ...prev, status: 'approved' }));
				alert('勤怠データを更新しました！');
			} else {
				setRecord((prev) => ({ ...prev, status: 'pending' }));
				alert('修正申請を送信しました！');
			}
		} catch (err) {
			console.error('申請エラー:', err);
			if (err.response?.status === 422) {
				const flatMessages = Object.values(err.response.data.errors).flat();
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
										{(mode === 'user' && record.status === 'pending') ||
										mode === 'approval' ? (
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

										const isReadOnly =
											(mode === 'user' && record.status === 'pending') ||
											mode === 'approval';

										return (
											<tr key={i} className="detail-row">
												<th className="detail-cell-head">{label}</th>
												<td className="detail-cell">
													{isReadOnly ? (
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
										{(mode === 'user' && record.status === 'pending') ||
										mode === 'approval' ? (
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

						{/* === ボタンエリア === */}
						{(mode === 'user' || mode === 'admin') && (
							<>
								{/* 一般ユーザーは normal / approved のときだけ修正可能 */}
								{(mode === 'admin' ||
									['normal', 'approved'].includes(record.status)) && (
									<div className="detail-btn-box">
										<button
											type="button"
											className="detail-btn"
											onClick={handleSubmit}
											disabled={submitting}
										>
											{submitting ? '送信中…' : '修正'}
										</button>
									</div>
								)}

								{/* 一般ユーザーで pending のときだけ非表示メッセージ */}
								{mode === 'user' && record.status === 'pending' && (
									<p className="detail-pending-msg">
										＊承認待ちのため修正はできません。
									</p>
								)}
							</>
						)}

						{/* === 承認モード === */}
						{mode === 'approval' && (
							<div className="detail-btn-box">
								{record.status === 'pending' ? (
									<button
										type="button"
										className="detail-btn"
										onClick={async () => {
											try {
												await axios.post(
													`${apiBase}/attendance/approve/${id}`
												);
												setRecord((prev) => ({
													...prev,
													status: 'approved',
												}));
												alert('承認が完了しました！');
											} catch (err) {
												console.error('承認エラー:', err);
												alert('承認に失敗しました。');
											}
										}}
									>
										承認
									</button>
								) : (
									<button
										type="button"
										className="detail-btn detail-btn-disabled"
										disabled
									>
										承認済み
									</button>
								)}
							</div>
						)}
					</>
				)}
			</div>
		</div>
	);
}
