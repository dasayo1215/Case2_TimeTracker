import axios from '../../axios';
import '../../css/email-verification.css';

export default function EmailVerificationNotice() {
	const handleResend = async () => {
		try {
			await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

			// 登録時 or 直前ログイン時のメールアドレスを優先的に取得
			const email =
				localStorage.getItem('registerEmail') ||
				localStorage.getItem('pending_verification_email');

			if (!email) {
				alert('メールアドレス情報が見つかりません。再度ログインをお試しください。');
				return;
			}

			await axios.post(
				'/api/email/verification-notification',
				{ email },
				{ withCredentials: true }
			);

			alert('認証メールを再送しました！');
		} catch (err) {
			console.error('再送に失敗しました:', err);
			alert('再送に失敗しました。もう一度お試しください。');
		}
	};

	return (
		<div className="email-verify-content">
			<p className="email-verify-text">
				登録していただいたメールアドレスに認証メールを送付しました。
				<br />
				メール認証を完了してください。
			</p>

			<a
				href="http://localhost:8025"
				target="_blank"
				rel="noopener noreferrer"
				className="verify-button"
			>
				認証はこちらから
			</a>

			<button onClick={handleResend} className="resend-link">
				認証メールを再送する
			</button>
		</div>
	);
}
