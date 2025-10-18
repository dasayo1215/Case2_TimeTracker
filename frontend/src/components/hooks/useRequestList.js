// src/components/hooks/useRequestList.js
import { useEffect, useState } from "react";
import axios from "../../axios";
import { useSearchParams } from "react-router-dom";

export default function useRequestList(apiEndpoint) {
    const [records, setRecords] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchParams, setSearchParams] = useSearchParams();
    const [activeTab, setActiveTab] = useState(
        searchParams.get("status") || "pending"
    );

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                await axios.get("/sanctum/csrf-cookie");
                const res = await axios.get(
                    `${apiEndpoint}?status=${activeTab}`
                );
                setRecords(
                    Array.isArray(res.data.records) ? res.data.records : []
                );
            } catch (error) {
                console.error("申請一覧の取得に失敗しました:", error);
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [activeTab, apiEndpoint]);

    useEffect(() => {
        const urlStatus = searchParams.get("status") || "pending";
        if (urlStatus !== activeTab) setActiveTab(urlStatus);
    }, [searchParams]);

    return { records, loading, activeTab, setActiveTab, setSearchParams };
}
