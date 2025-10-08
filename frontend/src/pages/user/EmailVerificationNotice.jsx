import React, { useState } from 'react';
import axios from 'axios';
import '../../css/email-verification.css';

export default function EmailVerificationNotice() {
	const [message, setMessage] = useState('');

    const handleResend = async () => {
        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

            // ローカルストレージなどから登録時メールアドレスを取得して送信
            const email = localStorage.getItem('registerEmail');

            const res = await axios.post(
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

			<a href="#" onClick={handleResend} className="resend-link">
				認証メールを再送する
			</a>
		</div>
	);
}
