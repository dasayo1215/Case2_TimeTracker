import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function ProtectedRoute({ children, allowedRoles = [] }) {
	const { user, loading } = useAuth();
	const location = useLocation();

	// ロールをセッションに記憶（ログアウト後も一瞬だけ使える）
	if (user?.role) {
		sessionStorage.setItem('lastRole', user.role);
	}

	// AuthContextがまだ初期化中なら、何もリダイレクトせず待つ
	if (loading) {
		return (
			<div style={{ textAlign: 'center', marginTop: '100px', color: '#555' }}>
				認証情報を確認中です...
			</div>
		);
	}

	// --- 未ログイン時 ---
	if (!user) {
		const lastRole = sessionStorage.getItem('lastRole');
		const isAdminPath = location.pathname.startsWith('/admin');
		let redirectPath = '/login';

		if (lastRole === 'admin' || isAdminPath) {
			redirectPath = '/admin/login';
		}

		return <Navigate to={redirectPath} replace state={{ from: location }} />;
	}

	// --- ロール不一致時 ---
	if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
		if (user.role === 'admin') {
			return <Navigate to="/admin/attendance/list" replace />;
		}
		return <Navigate to="/attendance" replace />;
	}

	// --- 通常時 ---
	return children;
}
