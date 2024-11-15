<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreGeneratedProductsRequest
 *
 * This class handles the validation of the request data for generating products.
 * It extends the FormRequest class provided by Laravel, which allows for
 * validation and authorization of incoming requests.
 */
class StoreGeneratedProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Currently, authorization is denied.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'po'    => [
                'required', // Nomor PO harus diisi
                'string', // Nomor PO harus berupa string
                'max:20', // Maksimal 20 karakter
                'min:10', // Minimal 10 karakter
            ],
            'obc'   => [
                'required', // Nomor OBC harus diisi
                'string', // Nomor OBC harus berupa string
                'max:9', // Maksimal 9 karakter
                'min:7', // Minimal 7 karakter
                'regex:/^[A-Za-z]{3}/', // Format harus diawali dengan 3 huruf
            ],
            'team'  => 'required|integer|min:1|exists:workstation,id', // Tim harus ada dan valid
            'seri'  => 'required|integer|min:1|max:5', // Seri harus antara 1 dan 5
            'produk'    => 'required|string|max:4', // Produk harus diisi dan maksimal 4 karakter
            'jml_rim'   => 'required|integer|min:1', // Jumlah rim harus diisi dan minimal 1
            'inschiet'  => 'nullable|integer|min:0', // Inschiet boleh kosong, minimal 0
            'start_rim' => 'required|integer|min:1', // Start rim harus diisi dan minimal 1
            'end_rim'   => [
                'required', // End rim harus diisi
                'integer', // End rim harus berupa integer
                'gte:start_rim', // End rim harus lebih besar atau sama dengan start rim
                function ($attribute, $value, $fail) {
                    $maxRim = ceil($this->jml_lembar / 500); // Hitung jumlah rim maksimum
                    $requestedRims = $value - $this->start_rim + 1; // Hitung jumlah rim yang diminta

                    if ($requestedRims > $maxRim) {
                        $fail("Range rim ({$requestedRims}) melebihi jumlah rim yang diizinkan ({$maxRim})."); // Pesan kesalahan jika melebihi
                    }
                },
            ],
            'jml_lembar' => 'required|integer|min:1', // Jumlah lembar harus diisi dan minimal 1
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'po.required' => 'Nomor PO harus diisi.',
            'po.string' => 'Nomor PO harus berupa teks.',
            'po.max' => 'Nomor PO maksimal 20 karakter.',
            'po.min' => 'Nomor PO minimal 10 karakter.',
            'obc.required' => 'Nomor OBC harus diisi.',
            'obc.string' => 'Nomor OBC harus berupa teks.',
            'obc.max' => 'Nomor OBC maksimal 9 karakter.',
            'obc.min' => 'Nomor OBC minimal 7 karakter.',
            'obc.regex' => 'Format OBC tidak valid. Harus diawali dengan 3 huruf.',
            'team.required' => 'Tim harus diisi.',
            'team.integer' => 'Tim harus berupa angka.',
            'team.min' => 'Tim harus minimal 1.',
            'seri.required' => 'Seri harus diisi.',
            'seri.integer' => 'Seri harus berupa angka.',
            'seri.min' => 'Seri harus minimal 1.',
            'seri.max' => 'Seri tidak boleh lebih dari 5.',
            'produk.required' => 'Produk harus diisi.',
            'produk.string' => 'Produk harus berupa string.',
            'produk.max' => 'Produk tidak boleh lebih dari 4 karakter.',
            'jml_rim.required' => 'Jumlah rim harus diisi.',
            'jml_rim.integer' => 'Jumlah rim harus berupa angka.',
            'jml_rim.min' => 'Jumlah rim harus minimal 1.',
            'inschiet.integer' => 'Inschiet harus berupa angka.',
            'inschiet.min' => 'Inschiet tidak boleh kurang dari 0.',
            'start_rim.required' => 'Start rim harus diisi.',
            'start_rim.integer' => 'Start rim harus berupa angka.',
            'start_rim.min' => 'Start rim harus minimal 1.',
            'end_rim.required' => 'End rim harus diisi.',
            'end_rim.integer' => 'End rim harus berupa angka.',
            'end_rim.gte' => 'End rim harus lebih besar atau sama dengan start rim.',
            'jml_lembar.required' => 'Jumlah lembar harus diisi.',
            'jml_lembar.integer' => 'Jumlah lembar harus berupa angka.',
            'jml_lembar.min' => 'Jumlah lembar harus minimal 1.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before validation occurs. It ensures that numeric values
     * are properly cast to integers.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Ensure numeric values are properly cast
        $this->merge([
            'jml_rim' => (int) $this->jml_rim,
            'jml_lembar' => (int) $this->jml_lembar,
            'start_rim' => (int) $this->start_rim,
            'end_rim' => (int) $this->end_rim,
            'inschiet' => (int) ($this->inschiet ?? 0),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422)); // Mengembalikan respons JSON jika validasi gagal
    }
}
