import axios from "../../../axios";

export default async function handleExportCsv(apiEndpoint, month) {
    try {
        const param = month ? `?month=${month.replace("/", "-")}` : "";
        const res = await axios.get(`${apiEndpoint}/export${param}`, {
            responseType: "blob",
        });

        // ファイル名を抽出
        const disposition = res.headers["content-disposition"];
        const match =
            disposition?.match(/filename\*=(?:UTF-8''|)([^;]+)/i) ||
            disposition?.match(/filename="?([^"]+)"?/i);
        const filename = match?.[1]
            ? decodeURIComponent(match[1].trim().replace(/['"]/g, ""))
            : "勤怠.csv";

        // CSV保存
        const blob = new Blob([res.data], { type: "text/csv;charset=utf-8;" });
        const link = Object.assign(document.createElement("a"), {
            href: URL.createObjectURL(blob),
            download: filename,
        });
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch {
        alert("CSV出力に失敗しました");
    }
}
