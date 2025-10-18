import { Link } from "react-router-dom";
import "../css/list-page.css";
import useRequestList from "./hooks/useRequestList";

export default function RequestListBase({ apiEndpoint, detailPathBase, title }) {
    const { records, loading, activeTab, setSearchParams } = useRequestList(apiEndpoint);

    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, "0");
        const dd = String(date.getDate()).padStart(2, "0");
        return `${yyyy}/${mm}/${dd}`;
    };

    return (
        <div className="list-page">
            <div className="list-container">
                <div className="list-header">
                    <h2 className="list-title">{title}</h2>
                    <div className="list-tab-box">
                        <button
                            className={`list-tab-button ${activeTab === "pending" ? "active" : ""}`}
                            onClick={() =>
                                activeTab !== "pending" && setSearchParams({ status: "pending" })
                            }
                        >
                            承認待ち
                        </button>
                        <button
                            className={`list-tab-button ${activeTab === "approved" ? "active" : ""}`}
                            onClick={() =>
                                activeTab !== "approved" && setSearchParams({ status: "approved" })
                            }
                        >
                            承認済み
                        </button>
                    </div>
                </div>

                {loading ? (
                    <p className="list-loading">読み込み中...</p>
                ) : (
                    <table className="list-table">
                        <thead>
                            <tr className="list-row list-row-head">
                                <th className="list-cell">状態</th>
                                <th className="list-cell">名前</th>
                                <th className="list-cell">対象日時</th>
                                <th className="list-cell">申請理由</th>
                                <th className="list-cell">申請日時</th>
                                <th className="list-cell">詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            {records.map((r) => (
                                <tr key={r.id} className="list-row">
                                    <td className="list-cell">
                                        {r.status === "pending" ? "承認待ち" : "承認済み"}
                                    </td>
                                    <td className="list-cell">{r.user.name}</td>
                                    <td className="list-cell">{formatDate(r.work_date)}</td>
                                    <td className="list-cell">{r.remarks}</td>
                                    <td className="list-cell">{formatDate(r.submitted_at)}</td>
                                    <td className="list-cell">
                                        <Link
                                            className="list-detail-link"
                                            to={`${detailPathBase}/${r.id}`}
                                        >
                                            詳細
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
}
