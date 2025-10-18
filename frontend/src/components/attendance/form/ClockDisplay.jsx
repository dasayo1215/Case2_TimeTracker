export default function ClockDisplay({ dateStr, weekday, time }) {
    return (
        <>
            <p className="attendance-date">
                {dateStr}({weekday})
            </p>
            <p className="attendance-time">{time}</p>
        </>
    );
}
