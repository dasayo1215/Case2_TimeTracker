export const toHHMM = (timeStr) => {
    if (!timeStr) return "";
    const parts = timeStr.split(":");
    return parts.length >= 2
        ? `${parts[0].padStart(2, "0")}:${parts[1].padStart(2, "0")}`
        : "";
};

export const addSeconds = (timeStr) => (timeStr ? `${timeStr}:00` : null);

export const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return `${d.getFullYear()}年　${d.getMonth() + 1}月${d.getDate()}日`;
};
