import { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import '../../css/auth-form.css';
import { useAuth } from '../../contexts/AuthContext';

export default function Register() {
	const [name, setName] = useState('');
	const [email, setEmail] = useState('');
	const [password, setPassword] = useState('');
	const [passwordConfirmation, setPasswordConfirmation] = useState('');
	const [errors, setErrors] = useState({});
	const navigate = useNavigate();
	const { setUser } = useAuth();

	const handleSubmit = async (e) => {
		e.preventDefault();
		setErrors({});

		try {
			// CSRF Cookie を取得
			await axios.get('/sanctum/csrf-cookie');

			// 登録リクエスト
			const res = await axios.post('/api/register', {
				name,
				email,
				password,
				password_confirmation: passwordConfirmation,
			});

			localStorage.setItem('registerEmail', email);
			setUser(null);
			navigate('/email/verify/notice');
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
			<h2 className="auth-form-heading">会員登録</h2>

			<form className="auth-form-body" onSubmit={handleSubmit}>
				<label className="auth-form-label" htmlFor="name">
					名前
				</label>
				<input
					className="auth-form-input"
					type="text"
					id="name"
					value={name}
					onChange={(e) => setName(e.target.value)}
				/>
				<p className="auth-form-error">{errors.name && errors.name[0]}</p>

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

				<label className="auth-form-label" htmlFor="password_confirmation">
					パスワード確認
				</label>
				<input
					className="auth-form-input"
					type="password"
					id="password_confirmation"
					value={passwordConfirmation}
					onChange={(e) => setPasswordConfirmation(e.target.value)}
				/>
				<p className="auth-form-error">
					{errors.password_confirmation && errors.password_confirmation[0]}
				</p>

				<input className="auth-form-submit" type="submit" value="登録する" />
			</form>

			<a className="auth-form-link" href="/login">
				ログインはこちら
			</a>
		</div>
	);
}
