import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { useAuth } from './contexts/AuthContext';
import GuestHeader from './components/headers/GuestHeader';
import AdminHeader from './components/headers/AdminHeader';
import UserHeader from './components/headers/UserHeader';
import './css/header.css';

// ページコンポーネント（画面用）
import Register from './pages/user/Register';
import UserLogin from './pages/user/Login';
// import AttendanceForm from './pages/user/AttendanceForm';
// import AttendanceList from './pages/user/AttendanceList';
// import AttendanceDetail from './pages/user/AttendanceDetail';
// import RequestList from './pages/user/RequestList';

import AdminLogin from './pages/admin/Login';
// import AdminAttendanceList from './pages/admin/AttendanceList';
// import AdminAttendanceDetail from './pages/admin/AttendanceDetail';
// import StaffList from './pages/admin/StaffList';
// import StaffAttendanceList from './pages/admin/StaffAttendanceList';
// import AdminRequestList from './pages/admin/RequestList';
// import ApproveRequest from './pages/admin/RequestApprove';

function App() {
	const { user } = useAuth();
	const role = user ? user.role : 'guest';

	return (
		<BrowserRouter>
			{role === 'guest' && <GuestHeader />}
			{role === 'admin' && <AdminHeader />}
			{role === 'user' && <UserHeader />}

			<Routes>
				{/* 一般ユーザー */}
				<Route path="/register" element={<Register />} />
				<Route path="/login" element={<UserLogin />} />
				{/* <Route path="/attendance" element={<AttendanceForm />} /> */}
				{/* <Route path="/attendance/list" element={<AttendanceList />} /> */}
				{/* <Route path="/attendance/detail/:id" element={<AttendanceDetail />} /> */}
				{/* <Route path="/stamp_correction_request/list" element={<RequestList />} /> */}

				{/* 管理者 */}
				<Route path="/admin/login" element={<AdminLogin />} />
				{/* <Route path="/admin/attendance/list" element={<AdminAttendanceList />} /> */}
				{/* <Route path="/admin/attendance/:id" element={<AdminAttendanceDetail />} /> */}
				{/* <Route path="/admin/staff/list" element={<StaffList />} /> */}
				{/* <Route path="/admin/attendance/staff/:id" element={<StaffAttendanceList />} /> */}
				{/* <Route path="/admin/stamp_correction_request/list" element={<AdminRequestList />} /> */}
				{/* <Route
					path="/admin/stamp_correction_request/approve/:attendance_correct_request_id"
					element={<ApproveRequest />}
				/> */}
			</Routes>
		</BrowserRouter>
	);
}

export default App;
