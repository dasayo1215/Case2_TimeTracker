import { useEffect, useState } from 'react';
import axios from '../../../../axios';
import { useSearchParams } from 'react-router-dom';

export default function useAdminAttendanceList() {
    const [records, setRecords] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedDate, setSelectedDate] = useState(null);
    const [searchParams, setSearchParams] = useSearchParams();

    // 初期化：URLクエリ優先
    useEffect(() => {
        const queryDate = searchParams.get('date');
        setSelectedDate(queryDate ? new Date(queryDate) : new Date());
    }, []);

    // データ取得
    useEffect(() => {
        if (!selectedDate) return;
        const fetchData = async () => {
            try {
                setLoading(true);
                await axios.get('/sanctum/csrf-cookie');
                const dateStr = selectedDate.toISOString().slice(0, 10);
                const res = await axios.get(`/api/admin/attendance/list?date=${dateStr}`);
                setRecords(res.data);
            } catch (err) {
                console.error('勤怠一覧の取得に失敗しました:', err);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [selectedDate]);

    // クエリ反映
    useEffect(() => {
        if (!selectedDate) return;
        const dateStr = selectedDate.toISOString().slice(0, 10);
        const current = searchParams.get('date');
        if (current !== dateStr) {
            const isInitial = !searchParams.get('date');
            setSearchParams({ date: dateStr }, { replace: isInitial });
        }
    }, [selectedDate]);

    // 戻る／進む対応
    useEffect(() => {
        const queryDate = searchParams.get('date');
        if (!queryDate) return;
        const newDate = new Date(queryDate);
        if (
            !selectedDate ||
            newDate.toISOString().slice(0, 10) !== selectedDate.toISOString().slice(0, 10)
        ) {
            setSelectedDate(newDate);
        }
    }, [searchParams]);

    return { records, loading, selectedDate, setSelectedDate };
}
