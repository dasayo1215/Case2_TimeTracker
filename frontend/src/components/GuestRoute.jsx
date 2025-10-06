import { Navigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function GuestRoute({ children }) {
	const { user, loading } = useAuth();

    if (loading) return null;

	if (user) {
		if (user.role === 'admin') {
			return <Navigate to="/admin/attendance/list" />;
		}
		return <Navigate to="/attendance" />;
	}

	return children;
}

