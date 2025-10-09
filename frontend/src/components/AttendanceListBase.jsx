import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useSearchParams } from 'react-router-dom';
import { FaRegCalendarAlt } from 'react-icons/fa';
import DatePicker from 'react-datepicker';
import { ja } from 'date-fns/locale';
import 'react-datepicker/dist/react-datepicker.css';
import '../css/list-page.css';

export default function AttendanceListBase({
	apiEndpoint,
	title,
	detailPathBase,
	onLoaded,
	showCsv = false,
}) {
	const [records, setRecords] = useState([]);
	const [month, setMonth] = useState(null); // ← 初期は null（指定なし）
	const [loading, setLoading] = useState(true);

	const [searchParams, setSearchParams] = useSearchParams();

	// ✅ 初回マウント時のみ、monthを今月に設定
	useEffect(() => {
		if (!month) {
			const today = new Date();
			const thisMonth = `${today.getFullYear()}/${String(today.getMonth() + 1).padStart(
				2,
				'0'
			)}`;
			setMonth(thisMonth);
		}
	}, []);

	// ✅ monthが変わったとき、URLに反映（初期ロード時は除外）
	useEffect(() => {
		if (!month) return;
		const queryMonth = searchParams.get('month')?.replace('-', '/');
		if (queryMonth !== month) {
			setSearchParams({ month: month.replace('/', '-') });
		}
	}, [month]);

	// ✅ クエリが変わったとき → 戻る／進む対応
	useEffect(() => {
		const queryMonth = searchParams.get('month');
		if (queryMonth) {
			const normalized = queryMonth.replace('-', '/');
			if (normalized !== month) setMonth(normalized);
		}
	}, [searchParams]);

	// ✅ データ取得
	useEffect(() => {
		if (!month) return;
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const param = month ? `?month=${month.replace('/', '-')}` : '';
				const res = await axios.get(`${apiEndpoint}${param}`);
				setRecords(res.data.records || []);
				if (onLoaded && res.data.staff) onLoaded(res.data.staff);
			} catch (error) {
				console.error(`${title}の取得に失敗しました:`, error);
			} finally {
				setLoading(false);
			}
		};
		fetchData();
	}, [month, apiEndpoint]);

	// ✅ 表示用
	const [y, m] = month ? month.split('/').map((v) => parseInt(v, 10)) : [null, null];
	const getDaysInMonth = (year, month) => {
		const lastDay = new Date(year, month, 0).getDate();
		return Array.from({ length: lastDay }, (_, i) => i + 1);
	};
	const days = y && m ? getDaysInMonth(y, m) : [];

	const formatDate = (dateStr) => {
		const date = new Date(dateStr);
		const mm = String(date.getMonth() + 1).padStart(2, '0');
		const dd = String(date.getDate()).padStart(2, '0');
		const weekday = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()];
		return `${mm}/${dd}(${weekday})`;
	};

	const formatTime = (t) => (t ? t.substring(0, 5) : '');
	const formatDuration = (min) => {
		if (min == null) return '';
		const h = Math.floor(min / 60);
		const mm = String(min % 60).padStart(2, '0');
		return `${h}:${mm}`;
	};

	const handlePrevMonth = () => {
		const newDate = new Date(y, m - 2);
		setMonth(`${newDate.getFullYear()}/${String(newDate.getMonth() + 1).padStart(2, '0')}`);
	};
	const handleNextMonth = () => {
		const newDate = new Date(y, m);
		setMonth(`${newDate.getFullYear()}/${String(newDate.getMonth() + 1).padStart(2, '0')}`);
	};

	const handleExportCSV = async () => {
		try {
			const param = month ? `?month=${month.replace('/', '-')}` : '';
			const res = await axios.get(`${apiEndpoint}/export${param}`, {
				responseType: 'blob',
			});
			const blob = new Blob([res.data], { type: 'text/csv;charset=utf-8;' });
			const url = window.URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = url;
			let filename = '勤怠.csv';
			const disposition = res.headers['content-disposition'];
			if (disposition) {
				const match = disposition.match(/filename\*?=['"]?UTF-8''?([^;"']+)/i);
				if (match) filename = decodeURIComponent(match[1]);
			}
			link.download = filename;
			document.body.appendChild(link);
			link.click();
			link.remove();
		} catch (error) {
			console.error('CSV出力に失敗しました:', error);
			alert('CSV出力に失敗しました');
		}
	};

	return (
		<div className="list-page">
			<div className="list-container">
				<div className="list-header">
					<h2 className="list-title">{title}</h2>
					<div className="list-nav-box">
						<button className="list-nav-btn gray" onClick={handlePrevMonth}>
							← 前月
						</button>

						<div
							className="list-nav-center"
							onClick={() => document.querySelector('.list-nav-input')?.focus()}
						>
							<FaRegCalendarAlt className="list-nav-calendar-icon" />
							<DatePicker
								selected={new Date(y, m - 1)}
								onChange={(date) =>
									setMonth(
										`${date.getFullYear()}/${String(
											date.getMonth() + 1
										).padStart(2, '0')}`
									)
								}
								dateFormat="yyyy/MM"
								showMonthYearPicker
								locale={ja}
								className="list-nav-input"
							/>
						</div>

						<button className="list-nav-btn gray" onClick={handleNextMonth}>
							翌月 →
						</button>
					</div>
				</div>

				{/* --- メイン部分 --- */}
				{loading ? (
					<p className="list-loading">読み込み中...</p>
				) : (
					<>
						<table className="list-table">
							<thead>
								<tr className="list-row list-row-head">
									<th className="list-cell">日付</th>
									<th className="list-cell">出勤</th>
									<th className="list-cell">退勤</th>
									<th className="list-cell">休憩</th>
									<th className="list-cell">合計</th>
									<th className="list-cell">詳細</th>
								</tr>
							</thead>
							<tbody>
								{days.map((day) => {
									const dateStr = `${y}-${String(m).padStart(2, '0')}-${String(
										day
									).padStart(2, '0')}`;
									const record = records.find((r) => r.date.startsWith(dateStr));
									const detailId = record?.id ?? dateStr;

									return (
										<tr key={day} className="list-row">
											<td className="list-cell">{formatDate(dateStr)}</td>
											<td className="list-cell">
												{formatTime(record?.clock_in)}
											</td>
											<td className="list-cell">
												{formatTime(record?.clock_out)}
											</td>
											<td className="list-cell">
												{formatDuration(record?.break_minutes)}
											</td>
											<td className="list-cell">
												{formatDuration(record?.total_minutes)}
											</td>
											<td className="list-cell">
												{record ? (
													<Link
														className="list-detail-link"
														to={`${detailPathBase}/${detailId}`}
													>
														詳細
													</Link>
												) : (
													'' // データがない日は空欄
												)}
											</td>
										</tr>
									);
								})}
							</tbody>
						</table>

						{/* CSV出力ボタン */}
						{showCsv && (
							<div className="csv-button-container">
								<button className="csv-export-btn" onClick={handleExportCSV}>
									CSV出力
								</button>
							</div>
						)}
					</>
				)}
			</div>
		</div>
	);
}
