import { Link, useNavigate } from 'react-router-dom';
import axios from '../../axios';
import logo from '/logo.svg';
import { useAuth } from '../../contexts/AuthContext';

export default function AdminHeader() {
	const navigate = useNavigate();
	const { setUser } = useAuth();

	const handleLogout = async () => {
		try {
			await axios.post('/api/admin/logout');
			setUser(null);
			// ログアウトしたらログイン画面へ遷移
			navigate('/admin/login');
		} catch (err) {
			console.error('ログアウト失敗:', err);
		}
	};

	return (
		<header className="header">
			<div className="header-wrapper">
				<h1 className="sr-only">Case2_TimeTracker</h1>
				<img className="header-logo-img" src={logo} alt="ロゴ" />
				<ul className="header-nav">
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/admin/attendance/list">
							勤怠一覧
						</Link>
					</li>
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/admin/staff/list">
							スタッフ一覧
						</Link>
					</li>
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/stamp_correction_request/list">
							申請一覧
						</Link>
					</li>
					<li className="header-nav-item">
						<button
							className="header-nav-link header-nav-button"
							onClick={handleLogout}
						>
							ログアウト
						</button>
					</li>
				</ul>
			</div>
		</header>
	);
}
