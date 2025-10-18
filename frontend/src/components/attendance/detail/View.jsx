import "../../../css/attendance-detail.css";
import Body from "./Body";
import useAttendanceDetail from "../hooks/useAttendanceDetail";

export default function View({ id, apiBase, mode = "normal" }) {
    const {
        record,
        setRecord,
        loading,
        submitting,
        errors,
        handleChange,
        handleBreakChange,
        handleSubmit,
    } = useAttendanceDetail({ id, apiBase });

    if (loading) return <p className="detail-loading">読み込み中...</p>;
    if (!record)
        return <p className="detail-error">データが見つかりません。</p>;

    return (
        <div className="attendance-detail">
            <div className="detail-container">
                <h2 className="detail-title">勤怠詳細</h2>
                <Body
                    record={record}
                    mode={mode}
                    apiBase={apiBase}
                    id={id}
                    errors={errors}
                    submitting={submitting}
                    setRecord={setRecord}
                    handleChange={handleChange}
                    handleBreakChange={handleBreakChange}
                    handleSubmit={handleSubmit}
                />
            </div>
        </div>
    );
}
