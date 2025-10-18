import { Route } from 'react-router-dom';
import GuestRoute from '../components/GuestRoute';
import ProtectedRoute from '../components/ProtectedRoute';

import AdminLogin from '../pages/admin/Login';
import AdminAttendanceList from '../pages/admin/AttendanceList';
import AdminAttendanceDetail from '../pages/admin/AttendanceDetail';
import StaffList from '../pages/admin/StaffList';
import StaffAttendanceList from '../pages/admin/StaffAttendanceList';
import RequestApprove from '../pages/admin/RequestApprove';

export function AdminRoutes() {
    return (
        <>
            {/* ゲスト可 */}
            <Route
                path="/admin/login"
                element={
                    <GuestRoute>
                        <AdminLogin />
                    </GuestRoute>
                }
            />

            {/* 要ログイン */}
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
            <Route
                path="/admin/staff/list"
                element={
                    <ProtectedRoute allowedRoles={['admin']}>
                        <StaffList />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/admin/attendance/staff/:id"
                element={
                    <ProtectedRoute allowedRoles={['admin']}>
                        <StaffAttendanceList />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/stamp_correction_request/approve/:attendance_correct_request_id"
                element={
                    <ProtectedRoute allowedRoles={['admin']}>
                        <RequestApprove />
                    </ProtectedRoute>
                }
            />
        </>
    );
}
