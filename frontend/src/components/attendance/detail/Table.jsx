import { formatDate } from "../utils/timeFormat";
import NameRow from "./rows/NameRow";
import DateRow from "./rows/DateRow";
import TimeRow from "./rows/TimeRow";
import RemarksRow from "./rows/RemarksRow";
import BreakRows from "./BreakRows";

export default function Table({
    record,
    mode,
    errors,
    handleChange,
    handleBreakChange,
}) {
    return (
        <>
            <table className="detail-table">
                <tbody>
                    <NameRow record={record} />
                    <DateRow dateText={formatDate(record.date)} />
                    <TimeRow
                        record={record}
                        mode={mode}
                        handleChange={handleChange}
                    />
                    <BreakRows
                        record={record}
                        mode={mode}
                        handleBreakChange={handleBreakChange}
                    />
                    <RemarksRow
                        record={record}
                        mode={mode}
                        handleChange={handleChange}
                    />
                </tbody>
            </table>

            {errors.length > 0 && (
                <div className="detail-errors">
                    <ul className="detail-errors-list">
                        {errors.map((msg, i) => (
                            <li key={i} className="detail-errors-item">
                                {msg}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </>
    );
}
