import { useState, useEffect, useRef } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import axios from "../../axios";
import { useAuth } from "../../contexts/AuthContext";

export default function useLoginForm(isAdmin = false) {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const location = useLocation();
    const { setUser } = useAuth();
    const hasShownAlert = useRef(false);

    useEffect(() => {
        const params = new URLSearchParams(location.search);
        if (params.get("verified") === "1" && !hasShownAlert.current) {
            hasShownAlert.current = true;
            alert("メール認証が完了しました！");
            localStorage.removeItem("registerEmail");
            localStorage.removeItem("pending_verification_email");
        }
    }, [location]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});

        try {
            await axios.get("/sanctum/csrf-cookie");

            const url = isAdmin ? "/api/admin/login" : "/api/login";
            const res = await axios.post(url, { email, password });

            setUser(res.data.user);

            navigate(isAdmin ? "/admin/attendance/list" : "/attendance");
        } catch (err) {
            const status = err.response?.status;

            if (
                !isAdmin &&
                status === 403 &&
                err.response?.data?.need_verification
            ) {
                localStorage.setItem("pending_verification_email", email);
                alert(
                    "メールアドレスの認証が完了していません。\n認証メールをご確認ください。"
                );
                navigate("/email/verify/notice");
                return;
            }

            if (status === 422) {
                setErrors(err.response.data.errors || {});
            } else {
                alert("サーバーエラーが発生しました");
            }
        }
    };

    return {
        email,
        setEmail,
        password,
        setPassword,
        errors,
        handleSubmit,
    };
}
