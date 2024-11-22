<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Specification;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test case untuk fitur cetak label order kecil
 *
 * Class ini menguji fungsionalitas terkait:
 * - Pembuatan label untuk order kecil
 * - Penanganan duplikasi nomor PO
 * - Validasi input dan penyimpanan data
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\OrderKecil\CetakLabelController
 * - Services:
 *   - App\Services\PrintLabelService
 * - API Routes:
 *   - POST /api/order-kecil/cetak-label
 */
class CetakLabelOrderKecilTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper untuk membuat data spesifikasi test
     *
     * Membuat data spesifikasi dengan nilai default untuk pengujian
     *
     * @param int $noPo Nomor PO untuk spesifikasi
     * @return Specification Instance spesifikasi yang dibuat
     */
    private function createTestSpecification($noPo = 4000000001)
    {
        return Specification::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'seri' => 1,
            'type' => 'PCHT',
            'rencet' => 38123,
            'mesin' => 1
        ]);
    }

    /**
     * Test pembuatan label untuk order kecil
     *
     * Memverifikasi bahwa:
     * - Label dapat dibuat dengan sukses
     * - Data tersimpan dengan benar di database
     * - Response sesuai format yang diharapkan
     *
     * Steps:
     * 1. Buat user test
     * 2. Buat spesifikasi test
     * 3. Kirim request pembuatan label
     * 4. Verifikasi response dan data tersimpan
     *
     * @return void
     */
    public function test_can_create_label_for_small_order(): void
    {
        // Create a test user
        $user = Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password'  => Hash::make('Test123'),
        ]);

        // Create a test specification
        $this->createTestSpecification();

        // Prepare request data for label creation
        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'jml_label' => 5,
            'end_rim' => 5,
            'periksa1' => 'I444',
            'team' => 1,
            'produk'    => "PCHT",
            'jml_rim'   => 5,
            'start_rim' => 1,
            'end_rim'   => 5,
        ];

        // Send request with authentication
        $response = $this->actingAs($user)
            ->postJson('/api/order-kecil/cetak-label', $requestData);

        // Assert successful response
        $response->assertStatus(200);

        // Assert that the generated product data was stored correctly
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $requestData['po'],
            'no_obc' => $requestData['obc'],
            'assigned_team' => $requestData['team']
        ]);

        // Assert that the generated label data was stored correctly
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $requestData['po'],
            'np_users' => 'I444',
            'workstation' => $requestData['team']
        ]);
    }

    /**
     * Test penanganan duplikasi nomor PO
     *
     * Memverifikasi bahwa:
     * - Sistem menolak pembuatan label dengan nomor PO yang sudah ada
     * - Response error sesuai format yang diharapkan
     *
     * Steps:
     * 1. Buat user test
     * 2. Buat data order produksi yang sudah ada
     * 3. Coba buat label dengan nomor PO yang sama
     * 4. Verifikasi response error
     *
     * @return void
     */
    public function test_handles_duplicate_po_number(): void
    {
        // Create a test user
        $user = Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password'  => Hash::make('Test123'),
        ]);

        // Create an existing production order
        GeneratedProducts::create([
            'no_po' => "4000000001",
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => 1
        ]);

        // Prepare request data for label creation
        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'jml_label' => 5,
            'end_rim' => 5,
            'periksa1' => 'I444',
            'team' => 1
        ];

        // Attempt to create a label with a duplicate purchase order number
        $response = $this->actingAs($user)
            ->postJson('/api/order-kecil/cetak-label', $requestData);

        // Assert error response
        $response->assertStatus(422);
    }
}
