import { Navigate, useLocation } from "react-router-dom";
import { useEffect } from "react";
import { useAuth } from "../contexts/AuthContext";

export default function ProtectedRoute({ children, allowedRoles = [] }) {
    const { user, loading } = useAuth();
    const location = useLocation();

    // ユーザーのロールをセッションに保存（変更時のみ）
    useEffect(() => {
        if (user?.role) {
            sessionStorage.setItem("lastRole", user.role);
        }
    }, [user?.role]);

    // --- 認証中 ---
    if (loading) {
        return (
            <div
                style={{
                    textAlign: "center",
                    marginTop: "100px",
                    color: "#555",
                }}
            >
                認証情報を確認中です...
            </div>
        );
    }

    // --- 未ログイン時 ---
    if (!user) {
        const lastRole = sessionStorage.getItem("lastRole");
        const isAdminPath = location.pathname.startsWith("/admin");
        const redirectPath =
            lastRole === "admin" || isAdminPath ? "/admin/login" : "/login";

        return (
            <Navigate to={redirectPath} replace state={{ from: location }} />
        );
    }

    // --- ロール不一致時 ---
    if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        const redirectPath =
            user.role === "admin" ? "/admin/attendance/list" : "/attendance";
        return <Navigate to={redirectPath} replace />;
    }

    // --- 通常時 ---
    return children;
}
