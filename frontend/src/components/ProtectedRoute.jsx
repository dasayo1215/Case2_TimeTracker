import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function ProtectedRoute({ children, allowedRoles = [] }) {
	const { user, loading } = useAuth();
	const location = useLocation();

	if (loading) return null;

	// --- 未ログイン時 ---
	if (!user) {
		// ロール制限が admin の場合は /admin/login へ
		const shouldGoAdminLogin = allowedRoles.includes('admin');
		return <Navigate to={shouldGoAdminLogin ? '/admin/login' : '/login'} replace />;
	}

	// --- ロール不一致時 ---
	if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
		if (user.role === 'admin') {
			return <Navigate to="/admin/attendance/list" replace />;
		}
		return <Navigate to="/attendance" replace />;
	}

	return children;
}
