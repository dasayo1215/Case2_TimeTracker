import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function ProtectedRoute({ children, allowedRoles = [] }) {
	const { user, loading } = useAuth();

	if (loading) return null; // ローディング中は表示を止める

	// 未ログインならログインページへ
	if (!user) {
		return <Navigate to="/login" replace />;
	}

	// ロール制限（user / admin）をチェック
	if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
		// roleが許可されていない場合
		if (user.role === 'admin') {
			return <Navigate to="/admin/attendance/list" replace />;
		}
		return <Navigate to="/attendance" replace />;
	}

	// OKなら子コンポーネントをそのまま表示
	return children;
}
