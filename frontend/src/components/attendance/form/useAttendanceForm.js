import { useState, useEffect } from 'react';
import axios from '../../../axios';

export default function useAttendanceForm() {
    const [status, setStatus] = useState(null);
    const [loading, setLoading] = useState(true);
    const [currentTime, setCurrentTime] = useState('');

    useEffect(() => {
        const fetchStatus = async () => {
            try {
                await axios.get('/sanctum/csrf-cookie');
                const res = await axios.get('/api/attendance/status');
                setStatus(res.data.status);
            } catch (error) {
                console.error('状態取得失敗:', error);
                setStatus('取得失敗');
            } finally {
                setLoading(false);
            }
        };
        fetchStatus();

        const updateClock = () => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            setCurrentTime(`${hours}:${minutes}`);
        };
        updateClock();
        const timer = setInterval(updateClock, 1000);
        return () => clearInterval(timer);
    }, []);

    const today = new Date();
    const dateStr = today.toLocaleDateString('ja-JP', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
    const weekday = ['日', '月', '火', '水', '木', '金', '土'][today.getDay()];

    const handleClock = async (action, nextStatus, e) => {
        if (e?.preventDefault) e.preventDefault();
        try {
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/api/attendance/clock', { action });
            setStatus(nextStatus);
        } catch (error) {
            console.error('打刻失敗:', error);
            alert('打刻に失敗しました');
        }
    };

    return { status, loading, currentTime, dateStr, weekday, handleClock };
}
