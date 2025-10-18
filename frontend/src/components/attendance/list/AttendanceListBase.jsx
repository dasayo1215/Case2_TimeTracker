import "../../../css/list-page.css";
import useAttendanceList from "../hooks/useAttendanceList";
import MonthNavigator from "./MonthNavigator";
import RecordTable from "./RecordTable";
import handleExportCsv from "./handleExportCsv";

export default function AttendanceListBase({
    apiEndpoint,
    title,
    detailPathBase,
    onLoaded,
    showCsv = false,
}) {
    const { records, month, setMonth, loading } = useAttendanceList(
        apiEndpoint,
        title,
        onLoaded
    );
    const [y, m] = month?.split("/").map(Number) ?? [];

    const days =
        y && m
            ? Array.from(
                { length: new Date(y, m, 0).getDate() },
                (_, i) => i + 1
            ) : [];

    return (
        <div className="list-page">
            <div className="list-container">
                <div className="list-header">
                    <h2 className="list-title">{title}</h2>
                    {y && m && (
                        <MonthNavigator y={y} m={m} setMonth={setMonth} />
                    )}
                </div>

                {loading ? (
                    <p className="list-loading">読み込み中...</p>
                ) : (
                    <>
                        <RecordTable
                            days={days}
                            y={y}
                            m={m}
                            records={records}
                            detailPathBase={detailPathBase}
                        />
                        {showCsv && (
                            <div className="csv-button-container">
                                <button
                                    className="csv-export-btn"
                                    onClick={() =>
                                        handleExportCsv(apiEndpoint, month)
                                    }
                                >
                                    CSV出力
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}
