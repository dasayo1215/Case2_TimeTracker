import { useEffect, useState } from "react";
import axios from "../../../axios";
import { toHHMM, addSeconds } from "../utils/timeFormat";

function normalizeRecord(data, loginUser, id) {
    return {
        user_name:
            data?.user?.name ??
            data?.user_name ??
            loginUser.name ??
            "（ユーザー名）",
        user_id: data?.user_id ?? data?.user?.id ?? loginUser.id ?? null,
        date: data?.date ?? data?.work_date ?? id ?? "",
        clock_in: toHHMM(data?.clock_in),
        clock_out: toHHMM(data?.clock_out),
        remarks: data?.remarks ?? "",
        breakTimes: (data?.break_times ?? []).map((b) => ({
            break_start: toHHMM(b?.break_start),
            break_end: toHHMM(b?.break_end),
        })),
        status: data?.status ?? "normal",
    };
}

function buildPayload(record) {
    const breaks = Array.isArray(record.breakTimes)
        ? record.breakTimes
            .filter((b) => b.break_start || b.break_end)
            .map((b) => ({
                break_start: addSeconds(b.break_start),
                break_end: addSeconds(b.break_end),
            }))
        : [];

    return {
        user_id: record.user_id,
        date: record.date,
        clock_in: addSeconds(record.clock_in),
        clock_out: addSeconds(record.clock_out),
        remarks: record.remarks || "",
        breakTimes: breaks,
    };
}

export default function useAttendanceDetail({ id, apiBase }) {
    const [record, setRecord] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState([]);

    useEffect(() => {
        const fetchData = async () => {
            try {
                await axios.get("/sanctum/csrf-cookie");
                const userRes = await axios.get(
                    apiBase.includes("/admin") ? "/api/admin/user" : "/api/user"
                );
                const loginUser = userRes.data;

                const url = apiBase.includes("/admin")
                    ? `${apiBase}/attendance/${id}`
                    : `${apiBase}/attendance/detail/${id}`;
                const res = await axios.get(url).catch(() => null);
                if (!res?.data) return setRecord(null);

                setRecord(normalizeRecord(res.data, loginUser, id));
            } catch (e) {
                console.error("詳細取得失敗:", e);
                setRecord(null);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [apiBase, id]);

    const handleChange = (field, value) =>
        setRecord((prev) => ({ ...prev, [field]: value }));

    const handleBreakChange = (i, field, val) =>
        setRecord((prev) => {
            const list = [...(prev.breakTimes ?? [])];
            if (!list[i]) list[i] = { break_start: "", break_end: "" };
            list[i][field] = val;
            return { ...prev, breakTimes: list };
        });

    const handleSubmit = async () => {
        if (!record) return;
        setSubmitting(true);
        setErrors([]);
        try {
            await axios.get("/sanctum/csrf-cookie");
            const payload = buildPayload(record);
            await axios.post(`${apiBase}/attendance/update-or-create`, payload);
            setRecord((p) => ({
                ...p,
                status: apiBase.includes("/admin") ? "approved" : "pending",
            }));
            alert(
                apiBase.includes("/admin")
                    ? "勤怠データを更新しました！"
                    : "修正申請を送信しました！"
            );
        } catch (err) {
            console.error("申請エラー:", err);
            if (err.response?.status === 422)
                setErrors(Object.values(err.response.data.errors).flat());
            else setErrors(["予期しないエラーが発生しました"]);
        } finally {
            setSubmitting(false);
        }
    };

    return {
        record,
        setRecord,
        loading,
        submitting,
        errors,
        handleChange,
        handleBreakChange,
        handleSubmit,
    };
}
