import { BrowserRouter, Routes, Route } from 'react-router-dom';
// useAuth を単体で使うのではなく、AuthProvider もインポート
import { AuthProvider, useAuth } from './contexts/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import GuestRoute from './components/GuestRoute';

import GuestHeader from './components/headers/GuestHeader';
import AdminHeader from './components/headers/AdminHeader';
import UserHeader from './components/headers/UserHeader';
import './css/header.css';

// ページコンポーネント（画面用）
import Register from './pages/user/Register';
import UserLogin from './pages/user/Login';
import AttendanceForm from './pages/user/AttendanceForm';
import AttendanceList from './pages/user/AttendanceList';
import AttendanceDetail from './pages/user/AttendanceDetail';
import RequestList from './pages/user/RequestList';

import AdminLogin from './pages/admin/Login';
import AdminAttendanceList from './pages/admin/AttendanceList';
import AdminAttendanceDetail from './pages/admin/AttendanceDetail';
// import StaffList from './pages/admin/StaffList';
// import StaffAttendanceList from './pages/admin/StaffAttendanceList';
// import AdminRequestList from './pages/admin/RequestList';
// import ApproveRequest from './pages/admin/RequestApprove';

/**
 * Layoutコンポーネント
 * → AuthContextのuser情報を使ってヘッダー切り替えやルーティングを行う
 */
function Layout() {
	const { user } = useAuth(); // 現在のユーザー情報（未ログインなら null）
	const role = user ? user.role : 'guest'; // 未ログイン時は 'guest'

	return (
		<>
			{/* ログイン状態に応じてヘッダー切り替え */}
			{role === 'guest' && <GuestHeader />}
			{role === 'admin' && <AdminHeader />}
			{role === 'user' && <UserHeader />}

			{/* ★ デバッグ用：ログイン中ユーザーを表示 */}
			{/* <div
				style={{
					marginTop: '50px',
					background: '#ff0000ff',
					padding: '6px 10px',
					fontSize: '14px',
					color: '#333',
				}}
			>
				{user ? (
					<>
						ログイン中ユーザー：<strong>{user.name}</strong>（{user.role}）
					</>
				) : (
					<span>未ログイン（guest）</span>
				)}
			</div> */}

			<Routes>
				{/* -------------------------- */}
				{/* 一般ユーザー（ゲスト可） */}
				{/* -------------------------- */}
				<Route
					path="/register"
					element={
						<GuestRoute>
							<Register />
						</GuestRoute>
					}
				/>
				<Route
					path="/login"
					element={
						<GuestRoute>
							<UserLogin />
						</GuestRoute>
					}
				/>

				{/* -------------------------- */}
				{/* 一般ユーザー専用（要ログイン） */}
				{/* -------------------------- */}
				<Route
					path="/attendance"
					element={
						<ProtectedRoute allowedRoles={['user']}>
							<AttendanceForm />
						</ProtectedRoute>
					}
				/>
				<Route
					path="/attendance/list"
					element={
						<ProtectedRoute allowedRoles={['user']}>
							<AttendanceList />
						</ProtectedRoute>
					}
				/>
				<Route
					path="/attendance/detail/:id"
					element={
						<ProtectedRoute allowedRoles={['user']}>
							<AttendanceDetail />
						</ProtectedRoute>
					}
				/>
				<Route
					path="/stamp_correction_request/list"
					element={
						<ProtectedRoute allowedRoles={['user']}>
							<RequestList />
						</ProtectedRoute>
					}
				/>

				{/* -------------------------- */}
				{/* 管理者（ゲスト可） */}
				{/* -------------------------- */}
				<Route
					path="/admin/login"
					element={
						<GuestRoute>
							<AdminLogin />
						</GuestRoute>
					}
				/>

				{/* -------------------------- */}
				{/* 管理者専用（要ログイン） */}
				{/* -------------------------- */}
				<Route
					path="/admin/attendance/list"
					element={
						<ProtectedRoute allowedRoles={['admin']}>
							<AdminAttendanceList />
						</ProtectedRoute>
					}
				/>
				<Route
					path="/admin/attendance/:id"
					element={
						<ProtectedRoute allowedRoles={['admin']}>
							<AdminAttendanceDetail />
						</ProtectedRoute>
					}
				/>
				{/* <Route path="/admin/staff/list" element={<StaffList />} /> */}
				{/* <Route path="/admin/attendance/staff/:id" element={<StaffAttendanceList />} /> */}
				{/* <Route path="/admin/stamp_correction_request/list" element={<AdminRequestList />} /> */}
				{/* <Route
					path="/admin/stamp_correction_request/approve/:attendance_correct_request_id"
					element={<ApproveRequest />}
				/> */}
			</Routes>
		</>
	);
}

/**
 * Appコンポーネント
 * → 全体を AuthProvider で包むことで、アプリ全体でログイン状態を共有できる
 */
function App() {
	return (
		<AuthProvider>
			<BrowserRouter>
				<Layout />
			</BrowserRouter>
		</AuthProvider>
	);
}

export default App;
