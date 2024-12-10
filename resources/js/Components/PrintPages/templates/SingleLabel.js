import { formatDate, formatTime } from '../utils/dateUtils';

export function labelPage(obc, noRim = "", color, sisiran = "", periksa1, periksa2 = "") {
    const date = new Date();
    const tgl = formatDate(date);
    const time = formatTime(date);
    const p2 = periksa2 == "" ? "" : "/" + periksa2;

    return `<!DOCTYPE html>
        <html>
            <head></head>
            <body>
                <!-- Your existing single label template -->
            </body>
        </html>`;
}
