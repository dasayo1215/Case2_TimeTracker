import { useAuth } from '../contexts/AuthContext';
import RequestList from './user/RequestList';
import AdminRequestList from './admin/RequestList';

export default function RequestListSelector() {
	const { user, loading } = useAuth();

	if (loading) {
		return <div>Loading...</div>; // 読み込み中はまだauth判定しない
	}

	if (!user) {
		return <div>ログイン情報を確認中...</div>;
	}

	return user.role === 'admin' ? <AdminRequestList /> : <RequestList />;
}
