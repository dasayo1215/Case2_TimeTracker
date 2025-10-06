import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import '../css/auth-form.css';
import { useAuth } from '../contexts/AuthContext';

export default function LoginForm({ isAdmin = false }) {
	const [email, setEmail] = useState('');
	const [password, setPassword] = useState('');
	const [errors, setErrors] = useState({});
	const navigate = useNavigate();
	const { setUser } = useAuth();

	const handleSubmit = async (e) => {
		e.preventDefault();
		setErrors({});

		try {
			// 1. CSRF Cookie を取得
			await axios.get('/sanctum/csrf-cookie');

			// 2. ログインリクエスト
			const url = isAdmin ? '/api/admin/login' : '/api/login';
			const res = await axios.post(url, { email, password });

			// 3. ユーザー情報を Context に保存
			setUser(res.data.user);

			// 4. 成功時リダイレクト先を分岐
			navigate(isAdmin ? '/admin/attendance/list' : '/attendance');
		} catch (err) {
			if (err.response?.status === 422) {
				setErrors(err.response.data.errors || {});
			} else {
				alert('サーバーエラーが発生しました');
			}
		}
	};

	return (
		<div className="auth-form">
			<h2 className="auth-form-heading">{isAdmin ? '管理者ログイン' : 'ログイン'}</h2>

			<form className="auth-form-body" onSubmit={handleSubmit}>
				<label className="auth-form-label" htmlFor="email">
					メールアドレス
				</label>
				<input
					className="auth-form-input"
					type="text"
					id="email"
					value={email}
					onChange={(e) => setEmail(e.target.value)}
				/>
				<p className="auth-form-error">{errors.email && errors.email[0]}</p>

				<label className="auth-form-label" htmlFor="password">
					パスワード
				</label>
				<input
					className="auth-form-input"
					type="password"
					id="password"
					value={password}
					onChange={(e) => setPassword(e.target.value)}
				/>
				<p className="auth-form-error">{errors.password && errors.password[0]}</p>

				<input
					className="auth-form-submit"
					type="submit"
					value={isAdmin ? '管理者ログインする' : 'ログインする'}
				/>
			</form>

			{!isAdmin && (
				<a className="auth-form-link" href="/register">
					会員登録はこちら
				</a>
			)}
		</div>
	);
}
