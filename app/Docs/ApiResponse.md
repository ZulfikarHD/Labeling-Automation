# ApiResponse Trait

Trait ini menyediakan metode untuk membangun respons API yang konsisten. Menggunakan metode ini akan meningkatkan Developer Experience (DX) dengan memberikan struktur yang jelas untuk respons sukses dan error.

## Metode

### successResponse

Menghasilkan respons sukses dalam format JSON.

**Parameter:**
- `string $message`: Pesan yang menjelaskan hasil operasi.
- `array $data`: Data tambahan yang ingin disertakan dalam respons (default: `[]`).
- `int $code`: Kode status HTTP (default: `200`).

**Return:**
- `\Illuminate\Http\JsonResponse`: Respons sukses dalam format JSON.

### errorResponse

Menghasilkan respons error dalam format JSON.

**Parameter:**
- `string $message`: Pesan yang menjelaskan kesalahan.
- `int $code`: Kode status HTTP (default: `500`).

**Return:**
- `\Illuminate\Http\JsonResponse`: Respons error dalam format JSON.

### validationErrorResponse

Menghasilkan respons error validasi dalam format JSON.

**Parameter:**
- `array $errors`: Daftar kesalahan validasi.

**Return:**
- `\Illuminate\Http\JsonResponse`: Respons error validasi dalam format JSON.

Trait ini dirancang untuk digunakan dalam aplikasi Laravel untuk memastikan bahwa semua respons API mengikuti format yang konsisten dan mudah dipahami.


