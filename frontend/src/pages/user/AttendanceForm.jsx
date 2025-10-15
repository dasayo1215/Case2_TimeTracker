import { useState, useEffect } from 'react';
import axios from '../../axios';
import '../../css/attendance-form.css';

export default function AttendanceForm() {
	const [status, setStatus] = useState(null); // ← 初期値を null に変更
	const [loading, setLoading] = useState(true); // ← ローディング状態を追加
	const [currentTime, setCurrentTime] = useState('');

	useEffect(() => {
		const fetchStatus = async () => {
			try {
				await axios.get('/sanctum/csrf-cookie');
				const res = await axios.get('/api/attendance/status');
				setStatus(res.data.status);
			} catch (error) {
				console.error('状態取得失敗:', error);
				setStatus('取得失敗');
			} finally {
				setLoading(false); // ← 読み込み完了後にfalse
			}
		};

		fetchStatus();

		const updateClock = () => {
			const now = new Date();
			const hours = String(now.getHours()).padStart(2, '0');
			const minutes = String(now.getMinutes()).padStart(2, '0');
			setCurrentTime(`${hours}:${minutes}`);
		};

		updateClock();
		const timer = setInterval(updateClock, 1000);
		return () => clearInterval(timer);
	}, []);

	const today = new Date();
	const dateStr = today.toLocaleDateString('ja-JP', {
		year: 'numeric',
		month: 'long',
		day: 'numeric',
	});
	const weekday = ['日', '月', '火', '水', '木', '金', '土'][today.getDay()];

	const handleClock = async (action, nextStatus, e) => {
		if (e && e.preventDefault) e.preventDefault();
		try {
			await axios.get('/sanctum/csrf-cookie');
			await axios.post('/api/attendance/clock', { action });
			setStatus(nextStatus);
		} catch (error) {
			console.error('打刻失敗:', error);
			alert('打刻に失敗しました');
		}
	};

	const renderButtons = () => {
		switch (status) {
			case '勤務外':
				return (
					<button
						type="button"
						className="attendance-btn"
						onClick={(e) => handleClock('clock_in', '出勤中', e)}
					>
						出勤
					</button>
				);
			case '出勤中':
				return (
					<div className="attendance-btn-group">
						<button
							type="button"
							className="attendance-btn"
							onClick={(e) => handleClock('clock_out', '退勤済', e)}
						>
							退勤
						</button>
						<button
							type="button"
							className="attendance-btn secondary"
							onClick={(e) => handleClock('break_start', '休憩中', e)}
						>
							休憩入
						</button>
					</div>
				);
			case '休憩中':
				return (
					<button
						type="button"
						className="attendance-btn secondary"
						onClick={(e) => handleClock('break_end', '出勤中', e)}
					>
						休憩戻
					</button>
				);
			case '退勤済':
				return <p className="attendance-message">お疲れ様でした。</p>;
			case '取得失敗':
				return <p className="attendance-message error">状態を取得できませんでした。</p>;
			default:
				return null;
		}
	};

	// ✅ ローディング中の表示
	if (loading) {
		return (
			<div className="attendance-page">
				<div className="attendance-container">
					<p className="attendance-loading">読み込み中...</p>
				</div>
			</div>
		);
	}

	return (
		<div className="attendance-page">
			<h2 className="attendance-status">{status}</h2>
			<p className="attendance-date">
				{dateStr}({weekday})
			</p>
			<p className="attendance-time">{currentTime}</p>
			<div className="attendance-buttons">{renderButtons()}</div>
		</div>
	);
}
