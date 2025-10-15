import axios from 'axios';

axios.defaults.withCredentials = true;

// ローカル or ngrok 自動判定
const apiBase = window.location.origin.includes('ngrok-free.app')
	? window.location.origin
	: 'http://localhost';

// ✅ ベースURLはドメインまで（/apiは付けない！）
axios.defaults.baseURL = `${apiBase}`;

// ✅ CSRFトークンを自動でヘッダーに付与
axios.interceptors.request.use((config) => {
	const xsrfToken = getCookieValue('XSRF-TOKEN');
	if (xsrfToken) {
		config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
	}
	return config;
});

function getCookieValue(name) {
	const value = `; ${document.cookie}`;
	const parts = value.split(`; ${name}=`);
	if (parts.length === 2) return parts.pop().split(';').shift();
}

// ✅ Sanctum用
export async function getCsrfCookie() {
	return axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

export default axios;
