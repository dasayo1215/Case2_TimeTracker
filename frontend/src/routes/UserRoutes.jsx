import { Route } from 'react-router-dom';
import GuestRoute from '../components/GuestRoute';
import ProtectedRoute from '../components/ProtectedRoute';

import Register from '../pages/user/Register';
import UserLogin from '../pages/user/Login';
import AttendanceForm from '../pages/user/AttendanceForm';
import AttendanceList from '../pages/user/AttendanceList';
import AttendanceDetail from '../pages/user/AttendanceDetail';
import EmailVerificationNotice from '../pages/user/EmailVerificationNotice';

export function UserRoutes() {
    return (
        <>
            {/* ゲスト可 */}
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
            <Route
                path="/email/verify/notice"
                element={
                    <GuestRoute>
                        <EmailVerificationNotice />
                    </GuestRoute>
                }
            />

            {/* 要ログイン */}
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
        </>
    );
}
