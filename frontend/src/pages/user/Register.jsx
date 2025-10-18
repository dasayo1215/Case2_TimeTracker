import '../../css/auth-form.css';
import useRegisterForm from '../../components/hooks/useRegisterForm';

export default function Register() {
    const {
        name, setName,
        email, setEmail,
        password, setPassword,
        passwordConfirmation, setPasswordConfirmation,
        errors, handleSubmit,
    } = useRegisterForm();

    return (
        <div className="auth-form">
            <h2 className="auth-form-heading">会員登録</h2>

            <form className="auth-form-body" onSubmit={handleSubmit}>
                <label className="auth-form-label" htmlFor="name">名前</label>
                <input
                    className="auth-form-input"
                    id="name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
                <p className="auth-form-error">{errors.name?.[0]}</p>

                <label className="auth-form-label" htmlFor="email">メールアドレス</label>
                <input
                    className="auth-form-input"
                    id="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                />
                <p className="auth-form-error">{errors.email?.[0]}</p>

                <label className="auth-form-label" htmlFor="password">パスワード</label>
                <input
                    className="auth-form-input"
                    type="password"
                    id="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                />
                <p className="auth-form-error">{errors.password?.[0]}</p>

                <label className="auth-form-label" htmlFor="password_confirmation">パスワード確認</label>
                <input
                    className="auth-form-input"
                    type="password"
                    id="password_confirmation"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                />
                <p className="auth-form-error">{errors.password_confirmation?.[0]}</p>

                <input className="auth-form-submit" type="submit" value="登録する" />
            </form>

            <a className="auth-form-link" href="/login">ログインはこちら</a>
        </div>
    );
}
