import { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
	const [user, setUser] = useState(null);
	const [loading, setLoading] = useState(true);

	// Cookie を常に送信する設定
	axios.defaults.withCredentials = true;

	useEffect(() => {
		const fetchUser = async () => {
			try {
				// Sanctum の CSRF Cookie を先に取得
				await axios.get('/sanctum/csrf-cookie');

				const isAdminPath = window.location.pathname.startsWith('/admin');
				const url = isAdminPath ? '/api/admin/user' : '/api/user';

				// 現在ログイン中のユーザー情報を取得
				const res = await axios.get(url);
				setUser(res.data);
			} catch (error) {
				setUser(null);
			} finally {
				setLoading(false);
			}
		};

		fetchUser();
	}, []);

	return (
		<AuthContext.Provider value={{ user, setUser, loading }}>
			{!loading && children}
		</AuthContext.Provider>
	);
};

export const useAuth = () => useContext(AuthContext);
