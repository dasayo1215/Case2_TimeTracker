import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { FaRegCalendarAlt } from 'react-icons/fa';
import DatePicker from 'react-datepicker';
import { ja } from 'date-fns/locale';
import 'react-datepicker/dist/react-datepicker.css';
import '../../css/list-page.css';

export default function AttendanceList() {
	const getCurrentMonth = () => {
		const now = new Date();
		return `${now.getFullYear()}/${String(now.getMonth() + 1).padStart(2, '0')}`;
	};

	const [records, setRecords] = useState([]);
	const [month, setMonth] = useState(getCurrentMonth());
	const [loading, setLoading] = useState(true);
	const [selectedDate, setSelectedDate] = useState(new Date());

	useEffect(() => {
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const apiMonth = month.replace('/', '-');
				const res = await axios.get(`/api/attendance/list?month=${apiMonth}`);
				setRecords(res.data);
			} catch (error) {
				console.error('勤怠一覧の取得に失敗しました:', error);
			} finally {
				setLoading(false);
			}
		};
		fetchData();
	}, [month]);

	const [y, m] = month.split('/').map((v) => parseInt(v, 10));
	const getDaysInMonth = (year, month) => {
		const lastDay = new Date(year, month, 0).getDate();
		return Array.from({ length: lastDay }, (_, i) => i + 1);
	};
	const days = getDaysInMonth(y, m);

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
		const m = min % 60;
		return `${h}:${String(m).padStart(2, '0')}`;
	};

	const handlePrevMonth = () => {
		const newDate = new Date(y, m - 2);
		updateMonth(newDate);
	};
	const handleNextMonth = () => {
		const newDate = new Date(y, m);
		updateMonth(newDate);
	};
	const updateMonth = (date) => {
		setSelectedDate(date);
		setMonth(`${date.getFullYear()}/${String(date.getMonth() + 1).padStart(2, '0')}`);
	};

	return (
		<div className="list-page">
			<div className="list-container">
				<div className="list-header">
					<h2 className="list-title">勤怠一覧</h2>
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
								selected={selectedDate}
								onChange={(date) => updateMonth(date)}
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

				{/* ===== テーブル ===== */}
				{loading ? (
					<p className="list-loading">読み込み中...</p>
				) : (
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
										<td className="list-cell">{formatTime(record?.clock_in)}</td>
										<td className="list-cell">{formatTime(record?.clock_out)}</td>
										<td className="list-cell">
											{formatDuration(record?.break_minutes)}
										</td>
										<td className="list-cell">
											{formatDuration(record?.total_minutes)}
										</td>
										<td className="list-cell">
											<Link
												className="list-detail-link"
												to={`/attendance/detail/${detailId}`}
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
