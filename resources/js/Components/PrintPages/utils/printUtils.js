export function printWithoutDialog(printFrame, content) {
    const iframe = printFrame.value;
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <style>
            @media print {
                @page { margin-left: 3rem; margin-right:3rem; margin-top:1rem; }
                header, footer { display: none !important; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
        ${content}
    `);

    iframe.contentWindow.focus();
    setTimeout(() => {
        iframe.contentWindow.print();
    }, 200);
}
