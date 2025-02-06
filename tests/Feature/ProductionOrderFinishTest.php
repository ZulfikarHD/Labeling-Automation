<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test case untuk fitur penyelesaian order produksi
 *
 * Class ini menguji fungsionalitas terkait:
 * - Penandaan order produksi sebagai selesai
 * - Penanganan order produksi yang tidak ditemukan
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\ProductionOrderController
 * - Services:
 *   - App\Services\ProductionOrderService
 * - API Routes:
 *   - PUT /api/production-order/{noPo}/finish
 */
class ProductionOrderFinishTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Helper untuk membuat data test
     *
     * Membuat data order produksi dan label terkait untuk pengujian
     *
     * @param string $noPo Nomor PO untuk data test
     * @return void
     */
    private function createTestData($noPo)
    {
        // Create a test production order
        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'status' => 0,
            'assigned_team' => 1
        ]);

        // Create test labels for the production order
        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => 'I444',
            'workstation' => 1
        ]);
    }

    /**
     * Test penandaan order produksi sebagai selesai
     *
     * Memverifikasi bahwa:
     * - Order produksi dapat ditandai sebagai selesai
     * - Status order berubah menjadi selesai di database
     *
     * Steps:
     * 1. Buat data test order produksi
     * 2. Kirim request untuk menandai order selesai
     * 3. Verifikasi response dan perubahan status
     *
     * @return void
     */
    public function test_production_order_can_be_marked_as_finished(): void
    {
        $noPo = 4000000001;
        $this->createTestData($noPo);

        $response = $this->put("/api/production-order/{$noPo}/finish");

        $response->assertStatus(200);

        $this->assertDatabaseHas('generated_products', [
            'no_po' => $noPo,
            'status' => 2 // Verify status is updated to finished (2)
        ]);
    }

    /**
     * Test penanganan order produksi yang tidak ditemukan
     *
     * Memverifikasi bahwa:
     * - Sistem mengembalikan 404 untuk order yang tidak ada
     *
     * Steps:
     * 1. Kirim request dengan nomor PO yang tidak ada
     * 2. Verifikasi response 404
     *
     * @return void
     */
    public function test_returns_404_if_production_order_to_be_marked_have_nonexistent_po(): void
    {
        $nonexistentPo = 9999999999;

        $response = $this->put("/api/production-order/{$nonexistentPo}/finish");

        $response->assertStatus(404);
    }
}
