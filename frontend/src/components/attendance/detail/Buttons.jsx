import axios from "../../../axios";

export default function Buttons({
    mode,
    record,
    apiBase,
    id,
    submitting,
    handleSubmit,
    setRecord,
}) {
    return (
        <>
            {/* === ボタンエリア === */}
            {(mode === "user" || mode === "admin") && (
                <>
                    {(mode === "admin" ||
                        ["normal", "approved"].includes(record.status)) && (
                        <div className="detail-btn-box">
                            <button
                                type="button"
                                className="detail-btn"
                                onClick={handleSubmit}
                                disabled={submitting}
                            >
                                {submitting ? "送信中…" : "修正"}
                            </button>
                        </div>
                    )}

                    {mode === "user" && record.status === "pending" && (
                        <p className="detail-pending-msg">
                            ＊承認待ちのため修正はできません。
                        </p>
                    )}
                </>
            )}

            {/* === 承認モード === */}
            {mode === "approval" && (
                <div className="detail-btn-box">
                    {record.status === "pending" ? (
                        <button
                            type="button"
                            className="detail-btn"
                            onClick={async () => {
                                try {
                                    await axios.post(
                                        `${apiBase}/attendance/approve/${id}`
                                    );
                                    setRecord((prev) => ({
                                        ...prev,
                                        status: "approved",
                                    }));
                                    alert("承認が完了しました！");
                                } catch (err) {
                                    console.error("承認エラー:", err);
                                    alert("承認に失敗しました。");
                                }
                            }}
                        >
                            承認
                        </button>
                    ) : (
                        <button
                            type="button"
                            className="detail-btn detail-btn-disabled"
                            disabled
                        >
                            承認済み
                        </button>
                    )}
                </div>
            )}
        </>
    );
}
