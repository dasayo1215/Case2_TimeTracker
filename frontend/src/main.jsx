import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import './css/base.css';
import App from './App.jsx';
import { AuthProvider } from './contexts/AuthContext';
import axios from './axios';

// axios を全体に適用（Cookie送受信を有効化）
axios.defaults.withCredentials = true;

createRoot(document.getElementById('root')).render(
	<StrictMode>
		<AuthProvider>
			<App />
		</AuthProvider>
	</StrictMode>
);
