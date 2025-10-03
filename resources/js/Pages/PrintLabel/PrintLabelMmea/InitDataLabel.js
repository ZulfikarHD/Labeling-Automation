import { fetchQcData } from "./ApiServices";

export function initOrderSpec(dataOrder) {

    // Check Nomor PO Exist
    if (!dataOrder.no_po || dataOrder.no_po < 3) {
        resetOrderSpec();
        return;
    }

    // Wrap Specification For Label
    const specOrder = {
        no_po: dataOrder.no_po,
        produk: dataOrder.jenis,
        no_obc: dataOrder.no_obc,
        jml_lbr: dataOrder.rencet,
        no_plat: "-",
    }

    return specOrder;
}

export async function initDataQc(dataOrder) {
    // Hitung Jumlah Label Pada Order
    const jml_label = Math.ceil(dataOrder.rencet / 300);

    // Hitung Banyaknya Jumlah Kemas Rim Terakhir Pada Order
    const last_jml_kemas = dataOrder.rencet !== 1500 && 
                           dataOrder.jml_order % 300 == 0 ? 
                           300 : dataOrder.jml_order % 300;

    // Ambil Data QC Jika Ada di Database
    const data_qc = await fetchQcData(dataOrder.no_po);

    // Initialize Form QC
    const form_qc = {
        no_po: dataOrder.no_po,
        no_rim: {},
        periksa1: {},
        periksa2: {},
        jml_kemas: {},
        jml_label: jml_label,
    };

    // ------------------------------------------ //
    // Check Apakah Sudah Ada Data QC Di Database //
    // ------------------------------------------ //

    // Isi Field Pertama Untuk Form QC
    if (typeof data_qc[0] !== 'undefined') {
        form_qc['periksa1']['np_1'] = data_qc[0]['periksa1'];
        form_qc['periksa2']['np_1'] = data_qc[0]['periksa2'];
        form_qc['jml_kemas']['no_1'] = data_qc[0]['lbr_kemas'];
        form_qc['no_rim']['no_1'] = 1;
    } else {
        form_qc['periksa1']['np_1'] = "";
        form_qc['periksa2']['np_1'] = "";
        form_qc['jml_kemas']['no_1'] = jml_label > 1 ? 300 : dataOrder.jml_order;
        form_qc['no_rim']['no_1'] = 1;
    }

    // Isi Filed Kedua dan Seterusnya
    for (let i = 2; i < jml_label; i++) {
        if (typeof data_qc[i - 1] !== 'undefined') {
            form_qc['periksa1'][`np_${i}`] = data_qc[i - 1]['periksa1'] ?? "";
            form_qc['periksa2'][`np_${i}`] = data_qc[i - 1]['periksa2'] ?? "";
            form_qc['jml_kemas'][`no_${i}`] = data_qc[i - 1]['lbr_kemas'];
            form_qc['no_rim'][`no_${i}`] = i;
        } else {
            form_qc['periksa1'][`np_${i}`] = "";
            form_qc['periksa2'][`np_${i}`] = "";
            form_qc['jml_kemas'][`no_${i}`] = 300;
            form_qc['no_rim'][`no_${i}`] = i;
        }
    }

    // Field Terakhir
    if (typeof data_qc[jml_label - 1] !== 'undefined') {
        form_qc['periksa1'][`np_${jml_label}`] = data_qc[jml_label - 1]['periksa1'];
        form_qc['periksa2'][`np_${jml_label}`] = data_qc[jml_label - 1]['periksa2'];
        form_qc['jml_kemas'][`no_${jml_label}`] = last_jml_kemas;
        form_qc['no_rim'][`no_${jml_label}`] = jml_label;
    } else {
        form_qc['periksa1'][`np_${jml_label}`] = "";
        form_qc['periksa2'][`np_${jml_label}`] = "";
        form_qc['jml_kemas'][`no_${jml_label}`] = last_jml_kemas;
        form_qc['no_rim'][`no_${jml_label}`] = jml_label;
    }

    return form_qc;
}

export function resetOrderSpec() {
    return {
        no_obc: "-",
        produk: "-",
        no_plat: "-",
        jml_lbr: 0,
    }
}