import axios from "axios";

export async function fetchDataOrder(nomorPo) {
    const response = await axios.get(`https://sirine.peruri.co.id/sirine/api/detail-order-mmea/${nomorPo}`);

    if (response.data == []) throw "Hubungi Admin Untuk Update Order MMEA";

    return response.data;
}

export async function storeLabelData(form) {
    const response = await axios.post('/api/print-label/mmea/store', form);
    return response.data;
}

export async function fetchQcData(nomorPo) {
    const response = await axios.get(`/api/print-label/mmea/qc-data/${nomorPo}`);
    return response.data;
}