import { Navigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";

export default function GuestRoute({ children }) {
    const { user, loading } = useAuth();

    if (loading) return null;
    if (!user) return children;

    const redirectPath =
        user.role === "admin" ? "/admin/attendance/list" : "/attendance";

    return <Navigate to={redirectPath} />;
}
