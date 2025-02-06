<?php

namespace App\Traits;

/**
 * Trait ApiResponse
 *
 * Trait ini menyediakan metode untuk membangun respons API yang konsisten.
 * Menggunakan metode ini akan meningkatkan Developer Experience (DX)
 * dengan memberikan struktur yang jelas untuk respons sukses dan error.
 */
trait ApiResponse
{
    /**
     * Menghasilkan respons sukses dalam format JSON.
     *
     * @param string $message Pesan yang menjelaskan hasil operasi.
     * @param array $data Data tambahan yang ingin disertakan dalam respons.
     * @param int $code Kode status HTTP (default: 200).
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message, array $data = [], int $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Menghasilkan respons error dalam format JSON.
     *
     * @param string $message Pesan yang menjelaskan kesalahan.
     * @param int $code Kode status HTTP (default: 500).
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /**
     * Menghasilkan respons error validasi dalam format JSON.
     *
     * @param array $errors Daftar kesalahan validasi.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse(array $errors)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Data validasi tidak valid',
            'errors' => $errors
        ], 422);
    }
}
