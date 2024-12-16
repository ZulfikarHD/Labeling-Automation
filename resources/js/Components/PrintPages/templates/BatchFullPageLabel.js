import { formatDate, formatTime } from "../utils/dateUtils";

export function batchFullPageLabel(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = "",
    jml_label
) {
    const date = new Date();
    const tgl = formatDate(date);
    const time = formatTime(date);
    const p2 = periksa2 == "" ? "" : periksa2;

    const contentPrint = `<!DOCTYPE html>
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
            border-width: 1px;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
            border-style: solid;
            border-top: 0px;
            border-left: 0px;
            border-right: 0px;
            width: 100%;
        }

        .flexBetween {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 2px;
        }

        .text-content {
            border-style: solid;
            height: 100%;
            margin: 0;
            padding: 0.25rem;
        }

        .p-content {
            font-size: 12px;
            font-family: sans-serif;
            text-align: center;
            margin: 0;
            text-transform: uppercase;
            width: 100%;
        }

        .grid-container {
            display: table;
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
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
            border: 1px solid black;
            padding: 0.15rem;
            height: 1.5rem;
            vertical-align: middle;
            text-align: center;
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
    <div style='page-break-after:avoid; width: 100%; height: 100%; border-style: solid;'>
        <div class="wrapper">
            <!-- Header -->
            <div class="sectionWrapper" style="flex: 0 0 auto; padding: 0.25rem; padding-bottom: 0.25rem;">
                <img src="/img/logo_peruri.svg"
                    style="width: 100%; max-height: 40px; object-fit: contain; margin-bottom: 0.25rem; filter: grayscale(100%);" />
                <h1 style="margin: 0.125rem 0; font-size: 14px; font-weight: 800; width: 100%; padding-top: 0.25rem; text-align: center;">PERUM PERCETAKAN UANG RI</h1>
            </div>

            <!-- Title -->
            <div class="sectionWrapper" style="flex: 0 0 auto; padding-bottom: 0.25rem;">
                <h1 style="text-align: center; font-weight: 300; margin: 0; font-size: 13px; width: 100%; font-family: sans-serif; line-height: 1.5;">LABEL KONTROL
                    <BR>PRODUKSI PITA CUKAI</h1>
            </div>

            <!-- Notes -->
            <div class="sectionWrapper" style="flex: 0 0 auto; padding-bottom: 0.2rem; padding-top: 0.2rem;">
                <p style="text-align: center; margin: 0; font-family: arial; line-height: 1.3; font-size: 9px; width: 100%; white-space: normal; word-wrap: break-word; overflow: hidden;">
                    Apabila dalam kemasan ini terdapat kekurangan jumlah isi, cacat karena proses produksi Pita Cukai, untuk permintaan penggantiannya harap kartu kontrol ini dan etiket pengemasannya disertakan
                </p>
            </div>

            <!-- Detail Label -->
            <div class="sectionWrapper" style="flex: 1 1 auto; margin-top: -0.25rem;">
                <table class="table-container" style="border: 0px !important;">
                    <!-- Header -->
                    <tr>
                        <td class="grid-wrapper" style="width: 25%">
                            <p class="p-content" style="font-weight: 600;">PROSES</p>
                        </td>
                        <td class="grid-wrapper" colspan="2" style="width: 50%">
                            <p class="p-content" style="font-weight: 600;">PETUGAS</p>
                        </td>
                        <td class="grid-wrapper" style="width: 25%">
                            <p class="p-content" style="font-weight: 600;">ACC</p>
                        </td>
                    </tr>

                    <!-- Row 1 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">PERIKSA</p>
                        </td>
                        <td class="grid-wrapper" colspan="1">
                            <div class="flex justify-center" style="padding-left: 0.25rem; padding-right: 0.25rem;">
                                <div>
                                    <strong class="p-content" style="font-size: 1rem; color: ${color};">${periksa1}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="grid-wrapper" colspan="1">
                            <div class="flex justify-center" style="padding-left: 0.25rem; padding-right: 0.25rem;">
                                <div>
                                    <strong class="p-content" style="font-size: 1rem; color: ${color};">${p2}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="grid-wrapper">
                            <p class="p-content"></p>
                        </td>
                    </tr>

                    <!-- Row 2 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">HITUNG</p>
                        </td>
                        <td class="grid-wrapper" colspan="3">
                            <p class="p-content"></p>
                        </td>
                    </tr>

                    <!-- Row 3 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">KEMAS</p>
                        </td>
                        <td class="grid-wrapper" colspan="2">
                            <div class="flex justify-between" style="padding-left: 0.25rem; padding-right: 0.25rem;">
                                <div>
                                    <strong class="p-content" style="font-size: 0.7rem;"></strong>
                                </div>
                                <div>
                                    <strong class="p-content" style="font-size: 0.7rem;"></strong>
                                </div>
                            </div>
                        </td>
                        <td class="grid-wrapper">
                            <p class="p-content"></p>
                        </td>
                    </tr>

                    <!-- Row 4 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">TANGGAL</p>
                        </td>
                        <td class="grid-wrapper" colspan="2">
                            <p class="p-content" style="font-weight: 600;"> OBC</p>
                        </td>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">ISI <br> KEMASAN</p>
                        </td>
                    </tr>

                    <!-- Row 5 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-size: 0.6rem;">${tgl}</p>
                        </td>
                        <td class="grid-wrapper" colspan="2">
                            <p class="p-content" style="font-size: 1rem; font-weight: 600; color: ${color};">${obc}</p>
                        </td>
                        <td class="grid-wrapper">
                            <p class="p-content">500 Lbr</p>
                        </td>
                    </tr>

                    <!-- Row 6 -->
                    <tr>
                        <td class="grid-wrapper">
                            <p class="p-content" style="font-weight: 600;">CATATAN</p>
                        </td>
                        <td class="grid-wrapper" colspan="3">
                            <p class="p-content" style="color: ${color};">${time}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
`;

    let printPage = "";
    for (let print = 0; print < jml_label; print++) {
        printPage += `<body>
            <span style="color:white">${print}</span>
            ${contentPrint}
            <div style="page-break-after: always;"></div>
        </body>`;
    }

    return printPage;
}
