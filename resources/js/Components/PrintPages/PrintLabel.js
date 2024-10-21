function detectBrowser() {
    const userAgent = navigator.userAgent;

    if (userAgent.includes("Chrome") && !userAgent.includes("Edg")) {
        return "Chrome";
    } else if (userAgent.includes("Firefox")) {
        return "Firefox";
    } else if (userAgent.includes("Safari") && !userAgent.includes("Chrome")) {
        return "Safari";
    } else if (userAgent.includes("Edg")) {
        return "Edge";
    } else if (userAgent.includes("Opera") || userAgent.includes("OPR")) {
        return "Opera";
    } else {
        return "Unknown";
    }
}

// Full Page Label
export function fullPageLabel(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = ""
) {
    let date = new Date(); // Tanggal saat ini

    const topMargin = detectBrowser() !== "Firefox" ? "-354px" : "-236px";

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

<head>
    <style>
        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
        }

        .sectionWrapper {
            display: flex;
            border-width: 2px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
            align-items: center;
            gap: 4px;
        }

        .text-content {
            border-style: solid;
            height: 100%;
            margin-top: 0px;
            margin-bottom: 0px;
            padding-left: 8px;
            padding-right: 8px;
        }

        .p-content {
            font-size: x-large;
            font-family: sans-serif;
            text-align: center;
            margin-top: 0;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        .grid-wrapper {
            border-width: 2px;
            border-style: solid;
            padding-left: 8px;
            padding-right: 8px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body style="scale:0.5; margin-top: ${topMargin}">
    <div style='page-break-after:avoid; width: 100%; height: fit-content; border-style: solid;'>
        <div class="wrapper">
            <!-- Header -->
            <div class="sectionWrapper">
                <img src="/img/peruri lama.png" style="width: 60px; margin-top: 4px;" />
                <img src="/img/text-peruri.png" style="width: 110px; margin-top: 8px;" />
                <h1 style="margin-top: 10px; margin-bottom: 5px;">PERUM PERCETAKAN UANG RI</h1>
            </div>

            <!-- Title -->
            <div class="sectionWrapper">
                <h1 style="text-align: center; font-weight: 500; margin-top: -10px; margin-bottom: 6px;">LABEL KONTROL
                    <BR>PRODUKSI PITA CUKAI</h3>
            </div>

            <!-- Notes -->
            <div class="sectionWrapper">
                <p style="text-align: center; margin-top: 0px; font-family: arial; line-height: 1.5rem;">
                    Apabila dalam kemasan ini terdapat kekurangan jumlah isi, cacat <br>
                    karena proses produksi Pita Cukai, untuk permintaan penggantiannya <br>
                    harap kartu kontrol ini dan etiket pengemasannya disertakan
                </p>
            </div>
            <div class="sectionWrapper"></div>

            <!-- Detail Label -->
            <div class="sectionWrapper">
                <!-- Header -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: -24px;">
                    <div class="grid-wrapper" style="border-top: 0; border-left: 0; border-bottom: 0;">
                        <p class="p-content">
                            Tanggal</p>
                    </div>
                    <div class="grid-wrapper"
                        style="border-top: 0; border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <p class="p-content"  style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            OBC</p>
                    </div>
                    <div class="grid-wrapper" style="border-top: 0; border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content"  style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ISI <br> Kemasan</p>
                    </div>
                </div>

                <!-- 2 Row -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper" style="border-width: 2px; border-left: 0; border-bottom: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            ${tgl}
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1rem; padding-bottom: 1rem; font-size: 2.5rem; color: ${color};">
                            ${obc}
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            500 LBR
                        </p>
                    </div>
                </div>

                <!-- Row 3 -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper" style="border-width: 2px; border-left: 0; border-bottom: 0;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            proses
                        </strong>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            petugas
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            acc
                        </strong>
                    </div>
                </div>

                <!-- Periksa -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0; justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            periksa
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1rem; padding-bottom: 1rem; font-size: 2rem;">
                            ${periksa1} ${p2}
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Hitung -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Hitung
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; grid-column: span 2;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Kemas -->
                <div
                    style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0; width: 67%;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Kemas
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; border-top: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                    <div class="grid-wrapper"
                        style=" border-left: 0; border-bottom: 0; border-right: 0; border-top: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Catatan -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Catatan
                        </p>
                    </div>
                    <div class="grid-wrapper"
                        style="border-left: 0; border-bottom: 0; grid-column: span 3; border-right: 0; justify-content:start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem; padding-left: 0.5rem; color:${color};">
                            ${noRim} ${sisiran} ${time}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

`;

    return printPage;
}

// Full Page Label
export function batchFullPageLabel(
    obc,
    noRim = "",
    color,
    sisiran = "",
    periksa1,
    periksa2 = "",
    jml_label,
) {
    let date = new Date(); // Tanggal saat ini

    const topMargin = detectBrowser() !== "Firefox" ? "-354px" : "-236px";

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

    let contentPage = `<!DOCTYPE html>
<html>

<head>
    <style>
        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
        }

        .sectionWrapper {
            display: flex;
            border-width: 2px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
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
            align-items: center;
            gap: 4px;
        }

        .text-content {
            border-style: solid;
            height: 100%;
            margin-top: 0px;
            margin-bottom: 0px;
            padding-left: 8px;
            padding-right: 8px;
        }

        .p-content {
            font-size: x-large;
            font-family: sans-serif;
            text-align: center;
            margin-top: 0;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        .grid-wrapper {
            border-width: 2px;
            border-style: solid;
            padding-left: 8px;
            padding-right: 8px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    .break-before { page-break-before: always; }
    .section { margin-top: ${topMargin}; }
    </style>
</head>

<body style="scale:0.5; margin-top: ${topMargin}">
    <div style='width: 100%; height: fit-content; border-style: solid;'>
        <div class="wrapper">
            <!-- Header -->
            <div class="sectionWrapper">
                <img src="/img/peruri lama.png" style="width: 60px; margin-top: 4px;" />
                <img src="/img/text-peruri.png" style="width: 110px; margin-top: 8px;" />
                <h1 style="margin-top: 10px; margin-bottom: 5px;">PERUM PERCETAKAN UANG RI</h1>
            </div>

            <!-- Title -->
            <div class="sectionWrapper">
                <h1 style="text-align: center; font-weight: 500; margin-top: -10px; margin-bottom: 6px;">LABEL KONTROL
                    <BR>PRODUKSI PITA CUKAI</h3>
            </div>

            <!-- Notes -->
            <div class="sectionWrapper">
                <p style="text-align: center; margin-top: 0px; font-family: arial; line-height: 1.5rem;">
                    Apabila dalam kemasan ini terdapat kekurangan jumlah isi, cacat <br>
                    karena proses produksi Pita Cukai, untuk permintaan penggantiannya <br>
                    harap kartu kontrol ini dan etiket pengemasannya disertakan
                </p>
            </div>
            <div class="sectionWrapper"></div>

            <!-- Detail Label -->
            <div class="sectionWrapper">
                <!-- Header -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: -24px;">
                    <div class="grid-wrapper" style="border-top: 0; border-left: 0; border-bottom: 0;">
                        <p class="p-content">
                            Tanggal</p>
                    </div>
                    <div class="grid-wrapper"
                        style="border-top: 0; border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <p class="p-content"  style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            OBC</p>
                    </div>
                    <div class="grid-wrapper" style="border-top: 0; border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content"  style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            ISI <br> Kemasan</p>
                    </div>
                </div>

                <!-- 2 Row -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper" style="border-width: 2px; border-left: 0; border-bottom: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            ${tgl}
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1rem; padding-bottom: 1rem; font-size: 2.5rem; color: ${color};">
                            ${obc}
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            500 LBR
                        </p>
                    </div>
                </div>

                <!-- Row 3 -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper" style="border-width: 2px; border-left: 0; border-bottom: 0;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            proses
                        </strong>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            petugas
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <strong class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            acc
                        </strong>
                    </div>
                </div>

                <!-- Periksa -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0; justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            periksa
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; grid-column: span 2;">
                        <strong class="p-content" style="padding-top: 1rem; padding-bottom: 1rem; font-size: 2rem;">
                            ${periksa1} ${p2}
                        </strong>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-bottom: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Hitung -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Hitung
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; grid-column: span 2;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                    <div class="grid-wrapper" style=" border-left: 0; border-right: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Kemas -->
                <div
                    style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0; width: 67%;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Kemas
                        </p>
                    </div>
                    <div class="grid-wrapper" style="border-left: 0; border-bottom: 0; border-top: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                    <div class="grid-wrapper"
                        style=" border-left: 0; border-bottom: 0; border-right: 0; border-top: 0;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">

                        </p>
                    </div>
                </div>

                <!-- Catatan -->
                <div
                    style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); width: 100%; justify-content: center; align-items: center; margin-top: 0px;">
                    <div class="grid-wrapper"
                        style="border-width: 2px; border-left: 0; border-bottom: 0;justify-content: start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
                            Catatan
                        </p>
                    </div>
                    <div class="grid-wrapper"
                        style="border-left: 0; border-bottom: 0; grid-column: span 3; border-right: 0; justify-content:start;">
                        <p class="p-content" style="padding-top: 1.5rem; padding-bottom: 1.5rem; padding-left: 0.5rem; color:${color};">
                            ${noRim} ${sisiran} ${time}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="break-before section" style="margin-top:-500px;"></div>
</body>

</html>

`;

    let printPage = "";

    for (let print = 0; print < jml_label; print++) {
        printPage += `<span style="color:white">${print}</span>${contentPage}`;
    }

    return printPage;
}

// Single Label Page
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

// Batch Lable Page
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
                                <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500; color:${color}"> <span style="font-size:12px; margin-left:8px">${
        noRim ?? ""
    }${sisiran ?? ""}${time}</span></h1>
                            </div>
                        </div>
                        <div style="page-break-after:always;"></div>`;

    let printPage = "";
    for (let print = 0; print < jml_label; print++) {
        printPage += `<body><span style="color:white">${print}</span>${contentPrint}</body>`;
    }

    return printPage;
}

// Full Page Prin Label
