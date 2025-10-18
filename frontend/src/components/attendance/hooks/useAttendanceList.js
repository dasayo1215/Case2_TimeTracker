import { useEffect, useState } from "react";
import axios from "../../../axios";
import { useSearchParams } from "react-router-dom";

export default function useAttendanceList(apiEndpoint, title, onLoaded) {
    const [records, setRecords] = useState([]);
    const [month, setMonth] = useState(null);
    const [loading, setLoading] = useState(true);
    const [searchParams, setSearchParams] = useSearchParams();

    // 初期設定
    useEffect(() => {
        if (!month) {
            const today = new Date();
            const thisMonth = `${today.getFullYear()}/${String(
                today.getMonth() + 1
            ).padStart(2, "0")}`;
            setMonth(thisMonth);
        }
    }, []);

    // URL反映
    useEffect(() => {
        if (!month) return;
        const queryMonth = searchParams.get("month")?.replace("-", "/");
        if (queryMonth !== month) {
            const isInitial = !searchParams.get("month");
            setSearchParams(
                { month: month.replace("/", "-") },
                { replace: isInitial }
            );
        }
    }, [month]);

    // 戻る/進む対応
    useEffect(() => {
        const queryMonth = searchParams.get("month");
        if (queryMonth) {
            const normalized = queryMonth.replace("-", "/");
            if (normalized !== month) setMonth(normalized);
        }
    }, [searchParams]);

    // データ取得
    useEffect(() => {
        if (!month) return;
        const fetchData = async () => {
            try {
                setLoading(true);
                await axios.get("/sanctum/csrf-cookie");
                const param = month ? `?month=${month.replace("/", "-")}` : "";
                const res = await axios.get(`${apiEndpoint}${param}`);
                setRecords(res.data.records || []);
                if (onLoaded && res.data.staff) onLoaded(res.data.staff);
            } catch (error) {
                console.error(`${title}の取得に失敗しました:`, error);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [month, apiEndpoint]);

    return { records, month, setMonth, loading };
}
