import { Route } from 'react-router-dom';
import ProtectedRoute from '../components/ProtectedRoute';
import RequestListSelector from '../pages/RequestListSelector';

export function CommonRoutes() {
    return (
        <>
            {/* 共通URL（admin / user 両方許可） */}
            <Route
                path="/stamp_correction_request/list"
                element={
                    <ProtectedRoute allowedRoles={['admin', 'user']}>
                        <RequestListSelector />
                    </ProtectedRoute>
                }
            />
        </>
    );
}
