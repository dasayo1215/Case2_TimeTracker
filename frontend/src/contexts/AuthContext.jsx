import { createContext, useContext, useState, useEffect } from 'react';
import axios from '../axios';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
	const [user, setUser] = useState(null);
	const [loading, setLoading] = useState(true);

	axios.defaults.withCredentials = true;

	useEffect(() => {
		const fetchUser = async () => {
			try {
				await axios.get('/sanctum/csrf-cookie');

				const pathname = window.location.pathname;
				let res;

				// まず admin 側を優先チェック
				if (
					pathname.startsWith('/admin') ||
					pathname.startsWith('/stamp_correction_request')
				) {
					try {
						res = await axios.get('/api/admin/user');
					} catch (adminError) {
						// 管理者未ログインなら一般ユーザーで再試行
						if (adminError.response?.status === 401) {
							res = await axios.get('/api/user');
						} else {
							throw adminError;
						}
					}
				} else {
					// 一般ユーザー側
					res = await axios.get('/api/user');
				}

				setUser(res.data);
			} catch (error) {
				// 未ログイン or セッション切れ → 明示的に user を null に
				if (error.response?.status === 401) {
					setUser(null);
				} else {
					console.error('ユーザー情報取得エラー:', error);
					setUser(null);
				}
			} finally {
				setLoading(false);
			}
		};

		fetchUser();
	}, []);

	return (
		<AuthContext.Provider value={{ user, setUser, loading }}>{children}</AuthContext.Provider>
	);
};

export const useAuth = () => useContext(AuthContext);
