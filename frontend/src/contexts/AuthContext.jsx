import { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

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

				// 💡 まず admin 側を優先的に試す
				let res;
				try {
					if (
						pathname.startsWith('/admin') ||
						pathname.startsWith('/stamp_correction_request')
					) {
						res = await axios.get('/api/admin/user');
					} else {
						throw new Error('skip admin check');
					}
				} catch {
					// adminセッションでなければ一般ユーザー用を試す
					res = await axios.get('/api/user');
				}

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
		<AuthContext.Provider value={{ user, setUser, loading }}>{children}</AuthContext.Provider>
	);
};

export const useAuth = () => useContext(AuthContext);
