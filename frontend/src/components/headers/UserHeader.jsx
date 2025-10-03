import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import logo from '/logo.svg';

export default function UserHeader() {
	const navigate = useNavigate();

	const handleLogout = async () => {
		try {
			await axios.post('/api/logout');
			navigate('/login'); // 一般ユーザーは /login へ
		} catch (err) {
			console.error('ログアウト失敗:', err);
		}
	};

	return (
		<header className="header">
			<div className="header-wrapper">
				<h1 className="sr-only">Case2_TimeTracker</h1>
				<Link className="header-logo" to="/">
					<img className="header-logo-img" src={logo} alt="ロゴ" />
				</Link>
				<ul className="header-nav">
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/user/attendance">
							勤怠
						</Link>
					</li>
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/user/attendances">
							勤怠一覧
						</Link>
					</li>
					<li className="header-nav-item">
						<Link className="header-nav-link" to="/user/requests">
							申請
						</Link>
					</li>
					<li className="header-nav-item">
						<button className="header-nav-link" onClick={handleLogout}>
							ログアウト
						</button>
					</li>
				</ul>
			</div>
		</header>
	);
}
