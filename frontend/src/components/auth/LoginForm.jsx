import "../css/auth-form.css";
import useLoginForm from "./hooks/useLoginForm";

export default function LoginForm({ isAdmin = false }) {
    const { email, setEmail, password, setPassword, errors, handleSubmit } =
        useLoginForm(isAdmin);

    return (
        <div className="auth-form">
            <h2 className="auth-form-heading">
                {isAdmin ? "管理者ログイン" : "ログイン"}
            </h2>

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
                <p className="auth-form-error">
                    {errors.email && errors.email[0]}
                </p>

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
                <p className="auth-form-error">
                    {errors.password && errors.password[0]}
                </p>

                <input
                    className="auth-form-submit"
                    type="submit"
                    value={isAdmin ? "管理者ログインする" : "ログインする"}
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
