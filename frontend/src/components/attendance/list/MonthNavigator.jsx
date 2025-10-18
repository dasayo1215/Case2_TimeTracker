import { FaRegCalendarAlt } from "react-icons/fa";
import DatePicker from "react-datepicker";
import { ja } from "date-fns/locale";
import "react-datepicker/dist/react-datepicker.css";

export default function MonthNavigator({ y, m, setMonth }) {
    const handlePrevMonth = () => {
        const newDate = new Date(y, m - 2);
        setMonth(
            `${newDate.getFullYear()}/${String(newDate.getMonth() + 1).padStart(
                2,
                "0"
            )}`
        );
    };

    const handleNextMonth = () => {
        const newDate = new Date(y, m);
        setMonth(
            `${newDate.getFullYear()}/${String(newDate.getMonth() + 1).padStart(
                2,
                "0"
            )}`
        );
    };

    return (
        <div className="list-nav-box">
            <button className="list-nav-btn gray" onClick={handlePrevMonth}>
                ← 前月
            </button>
            <div
                className="list-nav-center"
                onClick={() =>
                    document.querySelector(".list-nav-input")?.focus()
                }
            >
                <FaRegCalendarAlt className="list-nav-calendar-icon" />
                <DatePicker
                    selected={new Date(y, m - 1)}
                    onChange={(date) =>
                        setMonth(
                            `${date.getFullYear()}/${String(
                                date.getMonth() + 1
                            ).padStart(2, "0")}`
                        )
                    }
                    dateFormat="yyyy/MM"
                    showMonthYearPicker
                    locale={ja}
                    className="list-nav-input"
                />
            </div>
            <button className="list-nav-btn gray" onClick={handleNextMonth}>
                翌月 →
            </button>
        </div>
    );
}
