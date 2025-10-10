import { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useSearchParams } from 'react-router-dom';
import { FaRegCalendarAlt } from 'react-icons/fa';
import DatePicker from 'react-datepicker';
import { ja } from 'date-fns/locale';
import 'react-datepicker/dist/react-datepicker.css';
import '../../css/list-page.css';

export default function AdminAttendanceList() {
	const [records, setRecords] = useState([]);
	const [loading, setLoading] = useState(true);
	const [selectedDate, setSelectedDate] = useState(null);
	const [searchParams, setSearchParams] = useSearchParams();

	// 初期化：クエリがあれば採用、なければ今日の日付をセット（→後でクエリにも反映される）
	useEffect(() => {
		const queryDate = searchParams.get('date');
		if (queryDate) {
			setSelectedDate(new Date(queryDate));
		} else {
			setSelectedDate(new Date());
		}
	}, []);

	// selectedDate が変わるたびにデータ取得
	useEffect(() => {
		if (!selectedDate) return;
		const fetchData = async () => {
			try {
				setLoading(true);
				await axios.get('/sanctum/csrf-cookie');
				const dateStr = selectedDate.toISOString().slice(0, 10);
				const res = await axios.get(`/api/admin/attendance/list?date=${dateStr}`);
				setRecords(res.data);
			} catch (error) {
				console.error('勤怠一覧の取得に失敗しました:', error);
			} finally {
				setLoading(false);
			}
		};
		fetchData();
	}, [selectedDate]);

	// selectedDate が変わったらクエリにも反映
	useEffect(() => {
		if (!selectedDate) return;
		const newDateStr = selectedDate.toISOString().slice(0, 10);
		const currentQuery = searchParams.get('date');
		if (currentQuery !== newDateStr) {
			setSearchParams({ date: newDateStr });
		}
	}, [selectedDate]);

	// 戻る／進む対応（URLクエリ → stateへ反映）
	useEffect(() => {
		const queryDate = searchParams.get('date');
		if (!queryDate) return;
		const queryObj = new Date(queryDate);
		if (
			!selectedDate ||
			queryObj.toISOString().slice(0, 10) !== selectedDate.toISOString().slice(0, 10)
		) {
			setSelectedDate(queryObj);
		}
	}, [searchParams]);

	// --- 日付操作・フォーマット関数 ---
	const formatDateHeader = (date) => {
		const y = date.getFullYear();
		const m = date.getMonth() + 1;
		const d = date.getDate();
		return `${y}年${m}月${d}日の勤怠`;
	};

	const formatTime = (t) => (t ? t.substring(0, 5) : '');
	const formatDuration = (min) => {
		if (min == null) return '';
		const h = Math.floor(min / 60);
		const m = min % 60;
		return `${h}:${String(m).padStart(2, '0')}`;
	};

	const handlePrevDay = () => {
		const newDate = new Date(selectedDate);
		newDate.setDate(selectedDate.getDate() - 1);
		setSelectedDate(newDate);
	};

	const handleNextDay = () => {
		const newDate = new Date(selectedDate);
		newDate.setDate(selectedDate.getDate() + 1);
		setSelectedDate(newDate);
	};

	// --- JSX ---
	return (
		<div className="list-page">
			<div className="list-container">
				<div className="list-header">
					<h2 className="list-title">
						{selectedDate ? formatDateHeader(selectedDate) : '読み込み中...'}
					</h2>
					<div className="list-nav-box">
						<button className="list-nav-btn gray" onClick={handlePrevDay}>
							← 前日
						</button>

						<div
							className="list-nav-center"
							onClick={() => document.querySelector('.list-nav-input')?.focus()}
						>
							<FaRegCalendarAlt className="list-nav-calendar-icon" />
							{selectedDate && (
								<DatePicker
									selected={selectedDate}
									onChange={(date) => setSelectedDate(date)}
									dateFormat="yyyy/MM/dd"
									locale={ja}
									className="list-nav-input"
								/>
							)}
						</div>

						<button className="list-nav-btn gray" onClick={handleNextDay}>
							翌日 →
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
								<th className="list-cell">名前</th>
								<th className="list-cell">出勤</th>
								<th className="list-cell">退勤</th>
								<th className="list-cell">休憩</th>
								<th className="list-cell">合計</th>
								<th className="list-cell">詳細</th>
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
										<td className="list-cell">{record.user?.name}</td>
										<td className="list-cell">{formatTime(record.clock_in)}</td>
										<td className="list-cell">
											{formatTime(record.clock_out)}
										</td>
										<td className="list-cell">
											{formatDuration(record.break_minutes)}
										</td>
										<td className="list-cell">
											{formatDuration(record.total_minutes)}
										</td>
										<td className="list-cell">
											<Link
												className="list-detail-link"
												to={`/admin/attendance/${record.id}`}
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
