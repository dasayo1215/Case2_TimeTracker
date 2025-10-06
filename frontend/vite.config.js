import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
	plugins: [react()],
	server: {
		port: 5173,
		proxy: {
			'/api': {
				target: 'http://localhost',
				changeOrigin: true,
				secure: false,
			},
			'/sanctum': {
				target: 'http://localhost',
				changeOrigin: true,
				secure: false,
			},
		},
		historyApiFallback: true,
	},
});
