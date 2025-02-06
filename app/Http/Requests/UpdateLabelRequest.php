<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * UpdateLabelRequest
 *
 * Class ini bertanggung jawab untuk validasi data yang diterima saat memperbarui label.
 */
class UpdateLabelRequest extends FormRequest
{
    /**
     * Mendapatkan aturan validasi untuk permintaan ini.
     *
     * @return array Aturan validasi yang diterapkan pada data permintaan.
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:generated_labels,id', // ID label yang harus ada di database
            'np_users' => 'nullable|string|max:4', // Nomor pengguna, maksimal 4 karakter
            'np_user_p2' => 'nullable|string|max:4', // Nomor pengguna kedua, maksimal 4 karakter
            'start' => 'nullable|date', // Tanggal mulai, format tanggal
            'finish' => 'nullable|date', // Tanggal selesai, format tanggal
            'team' => 'required|integer' // ID tim yang harus berupa integer
        ];
    }
}
