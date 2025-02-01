import { formatDate, formatTime } from "../utils/dateUtils";

export function singleLabel(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = "",
    lembar = lembar ?? 500
) {
    const date = new Date();
    const tgl = formatDate(date);
    const time = formatTime(date);
    const p2 = periksa2 == "" ? "" : periksa2;

    return `<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            width: 9cm;
            height: 12cm;
            margin: 0 auto;
            font-size: 12px;
            overflow: hidden;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: stretch;
            height: 100%;
            gap: 0.25rem;
            overflow: hidden;
        }

        .sectionWrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            width: 100%;
        }

        .table-container {
            display: table;
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 0.5rem;
        }

        .grid-wrapper {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .p-content {
            font-size: 12px;
            font-family: sans-serif;
            text-align: center;
            margin: 0;
            text-transform: uppercase;
            width: 100%;
        }

        .flex {
            display: flex;
            flex: 1;
        }

        .justify-center {
            justify-content: center;
        }

        .justify-between {
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div style='width: 100%; height: 100%; margin-top: 1.1rem;'>
        <div class="wrapper">
            <div class="sectionWrapper" style="flex: 0 0 auto; padding: 0.25rem; padding-bottom: 0.25rem;">
            </div>

            <div class="sectionWrapper">
                <table class="table-container">
                    <tr>
                        <td class="grid-wrapper" style="width: 25%"></td>
                        <td class="grid-wrapper" colspan="2" style="width: 50%"></td>
                        <td class="grid-wrapper" style="width: 25%"></td>
                    </tr>
                    <tr style="height: 9.5rem;">
                        <td class="grid-wrapper"></td>
                        <td class="grid-wrapper" colspan="1">
                            <div class="flex justify-center">
                                <div>
                                    <strong class="p-content" style="font-size: 1rem; color: ${color};">${periksa1}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="grid-wrapper" colspan="1">
                            <div class="flex justify-center">
                                <div>
                                    <strong class="p-content" style="font-size: 1.5rem; color: ${color};">${p2}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="grid-wrapper"></td>
                    </tr>
                    <tr style="height: 8rem;">
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-size: 0.8rem; font-weight: 300;">${tgl}</p>
                        </td>
                        <td class="grid-wrapper" colspan="2">
                            <p class="p-content" style="font-size: 1.4rem; font-weight: 600; color: ${color};">${obc}</p>
                        </td>
                        <td class="grid-wrapper" style="font-size: 0.8rem; font-family: sans-serif; font-weight: 300;">${lembar} Lbr</td>
                    </tr>
                    <tr>
                        <td class="grid-wrapper"></td>
                        <td class="grid-wrapper" colspan="3">
                            <p class="p-content" style="color: ${color}; margin-top: -2rem;">${noRim} ${sisiran} - ${time}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>`;
}
