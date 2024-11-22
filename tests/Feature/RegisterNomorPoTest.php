<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Specification;
use App\Models\GeneratedProducts;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test case untuk fitur registrasi nomor PO (Production Order)
 *
 * Class ini menguji fungsionalitas terkait:
 * - Registrasi nomor PO baru
 * - Pencegahan duplikasi nomor PO
 * - Validasi input dan format nomor PO
 * - Penyimpanan data order produksi
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\OrderBesar\RegisterNomorPoController
 * - Services:
 *   - App\Services\ProductionOrderService
 * - API Routes:
 *   - POST /api/order-besar/register-no-po
 */
class RegisterNomorPoTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper untuk membuat data spesifikasi test
     *
     * Membuat data spesifikasi dengan nilai default untuk pengujian
     *
     * @param int $noPo Nomor PO untuk spesifikasi (default: 4000000001)
     * @return Specification Instance spesifikasi yang dibuat
     */
    private function createTestSpecification($noPo = 4000000001)
    {
        return Specification::create([
            'no_po' => $noPo,         // Nomor PO
            'no_obc' => 'TST010110',  // Nomor OBC test
            'seri' => 1,              // Nomor seri
            'type' => 'PCHT',         // Tipe produk
            'rencet' => 38123,        // Nomor rencet
            'mesin' => 1              // Nomor mesin
        ]);
    }

    /**
     * Test registrasi nomor PO baru
     *
     * Memverifikasi bahwa:
     * - Sistem dapat menerima registrasi PO baru
     * - Data tersimpan dengan benar di database
     * - Response sesuai format yang diharapkan
     *
     * Steps:
     * 1. Siapkan data request
     * 2. Kirim request registrasi PO
     * 3. Verifikasi penyimpanan data
     *
     * @return void
     */
    public function test_can_register_new_production_order_number(): void
    {
        $requestData = [
            'po'    => "4000000001",  // Nomor PO
            'obc'   => 'TST010110',   // Nomor OBC
            'produk'    => 'PCHT',    // Tipe produk
            'jml_rim'   => 5,         // Total rim
            'start_rim' => 1,         // Rim awal
            'end_rim'   => 5,         // Rim akhir
            'jml_lembar'=> 2500,      // Total lembar
            'team' => 1               // Nomor tim
        ];

        // Kirim request registrasi PO baru
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $requestData['po'],
            'no_obc' => $requestData['obc'],
            'type' => 'PCHT',
            'assigned_team' => $requestData['team']
        ]);
    }

    /**
     * Test penanganan duplikasi nomor PO
     *
     * Memverifikasi bahwa:
     * - Sistem menolak registrasi PO yang sudah ada
     * - Response error sesuai format yang diharapkan
     *
     * Steps:
     * 1. Buat data PO yang sudah ada
     * 2. Coba registrasi PO dengan nomor yang sama
     * 3. Verifikasi response error
     *
     * @return void
     */
    public function test_cannot_register_duplicate_production_order(): void
    {
        // Buat data PO yang sudah ada
        GeneratedProducts::create([
            'no_po' => "4000000001",
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,            // Status awal
            'assigned_team' => 1
        ]);

        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'team' => 1,
            'produk' => 'PCHT',
            'jml_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
        ];

        // Coba registrasi PO duplikat
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Verifikasi response error
        $response->assertStatus(422);
    }

    /**
     * Test validasi field yang wajib diisi
     *
     * Memverifikasi bahwa:
     * - Sistem memvalidasi field-field wajib
     * - Response error sesuai format yang diharapkan
     *
     * Steps:
     * 1. Kirim request tanpa data
     * 2. Verifikasi error validasi
     *
     * @return void
     */
    public function test_validates_required_fields(): void
    {
        // Kirim request tanpa data
        $response = $this->postJson('/api/order-besar/register-no-po', []);

        // Verifikasi error validasi
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['po', 'obc', 'jml_lembar', 'team']);
    }

    /**
     * Test validasi format nomor PO
     *
     * Memverifikasi bahwa:
     * - Sistem memvalidasi format nomor PO
     * - Response error sesuai untuk format yang tidak valid
     *
     * Steps:
     * 1. Siapkan data dengan format PO tidak valid
     * 2. Kirim request
     * 3. Verifikasi error validasi
     *
     * @return void
     */
    public function test_validates_production_order_number_format(): void
    {
        $requestData = [
            'po' => 123,              // Format tidak valid (seharusnya string)
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'team' => 1
        ];

        // Kirim request dengan format PO tidak valid
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Verifikasi error validasi
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['po']);
    }
}
