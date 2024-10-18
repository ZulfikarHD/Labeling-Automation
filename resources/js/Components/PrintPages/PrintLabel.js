export function labelPage(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = ""
) {
    let date = new Date(); // Tanggal saat ini

    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    let tgl = `${date.getDate()}-${
        months[date.getMonth()]
    }-${date.getFullYear()}`;

    let time = `${date.getHours()} : ${date.getMinutes()}`;

    let p2 = periksa2 == "" ? "" : "/" + periksa2;

    let printPage = `<!DOCTYPE html>
        <html>
            <head></head>
            <body>
                <div style='page-break-after:avoid; width:100%; height:fit-content;'>
                    <div style="margin-top:19.5vh; margin-left:18.5vh">
                        <span style="font-weight:600; text-align:center;">${tgl}</span>
                        <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${color}">${obc}</h1>
                    </div>
                    <div style="margin-top:0.75rem; margin-left:16vh">
                        <h1 style="font-size: 20px; line-height: 32px; margin-left:155px; margin-right:auto; font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${periksa1} ${p2}</h1>
                    </div>
                    <div style="margin-top:47.5px; margin-left:13vh">
                        <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500; color:${color}">${
        noRim ?? ""
    } ${
        sisiran ?? ""
    } <span style="font-size:12px; margin-left:8px">${time}</span></h1>
                    </div>
                </div>
            </body>
        </html>`;

    return printPage;
}

export function batchLabelPage(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = "",
    jml_label,
) {
    let date = new Date(); // Tanggal saat ini

    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    let tgl = `${date.getDate()}-${
        months[date.getMonth()]
    }-${date.getFullYear()}`;

    let time = `${date.getHours()} : ${date.getMinutes()}`;

    let p2 = periksa2 == "" ? "" : " / " + periksa2;

    let contentPrint = `<div style='width:100%;'>
                            <div style="margin-top:178px; margin-left:180px">
                                <span style="font-weight:600; text-align:center;">${tgl}</span>
                                <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${color}">${obc}</h1>
                            </div>
                            <div style="margin-top:0.75rem; margin-left:16vh">
                                <h1 style="font-size: 20px; line-height: 32px; margin-left:140px; margin-right:auto; font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${periksa1}${p2}</h1>
                            </div>
                            <div style="margin-top:47.5px; margin-left:13vh">
                                <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500; color:${color}"> <span style="font-size:12px; margin-left:8px">${noRim ?? ""}${sisiran ?? ""}${time}</span></h1>
                            </div>
                        </div>
                        <div style="page-break-after:always;"></div>`;

    let printPage = "";
    for (let print = 0; print < jml_label; print++) {
        printPage += `<body><span style="color:white">${print}</span>${contentPrint}</body>`;
    }

    return printPage;
}
