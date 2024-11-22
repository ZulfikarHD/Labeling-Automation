<?php

namespace Tests\Unit;

use App\Http\Controllers\GeneratedLabelController;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Test case untuk fitur manajemen label produksi
 *
 * Class ini menguji fungsionalitas terkait:
 * - Pengambilan data label berdasarkan nomor PO
 * - Penambahan rim baru ke order produksi
 * - Validasi dan penanganan error
 * - Pengurutan data label berdasarkan nomor rim
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\GeneratedLabelController
 * - Models:
 *   - App\Models\GeneratedLabels
 *   - App\Models\GeneratedProducts
 * - API Routes:
 *   - GET /api/labels/{po}
 *   - POST /api/labels/add-rim
 */
class GeneratedLabelTest extends TestCase
{
    use DatabaseTransactions;

    private GeneratedLabelController $controller;

    /**
     * Setup test environment
     *
     * Mempersiapkan:
     * - Inisialisasi controller untuk pengujian
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new GeneratedLabelController();
    }

    /**
     * Test pengambilan label dengan hasil kosong
     *
     * Memverifikasi bahwa:
     * - Sistem mengembalikan array kosong untuk PO yang tidak ada
     * - Response status 200 diterima
     *
     * Steps:
     * 1. Request label dengan nomor PO yang tidak ada
     * 2. Verifikasi response kosong
     *
     * @return void
     */
    public function test_get_labels_empty_result(): void
    {
        $po = '12345';

        $response = $this->controller->getLabels($po);

        $this->assertEquals(200, $response->status());
        $this->assertEmpty($response->getData());
    }

    /**
     * Test penambahan rim dengan nomor PO tidak valid
     *
     * Memverifikasi bahwa:
     * - Sistem menolak penambahan rim untuk PO yang tidak ada
     * - Exception dilempar dengan tipe yang sesuai
     *
     * Steps:
     * 1. Siapkan request dengan PO tidak valid
     * 2. Verifikasi exception
     *
     * @return void
     */
    public function test_add_rim_with_invalid_po(): void
    {
        $request = new Request([
            'no_po' => 999999,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->controller->addRim($request);
    }

    /**
     * Test penambahan rim untuk satu sisi
     *
     * Memverifikasi bahwa:
     * - Sistem dapat menambah rim baru
     * - Data order produksi diupdate dengan benar
     * - Total rim dan rim akhir dihitung dengan tepat
     *
     * Steps:
     * 1. Buat data order produksi test
     * 2. Tambah rim baru
     * 3. Verifikasi perubahan data
     *
     * @return void
     */
    public function test_add_rim_updates_production_order_single_side(): void
    {
        $po = GeneratedProducts::create([
            'no_po' => 3000999999,
            'no_obc' => 'TEST123456',
            'type' => 'TEST',
            'sum_rim' => 10,
            'start_rim' => 1,
            'end_rim' => 10/2,
            'assignet_team' => 1,
            'status' => 0,
        ]);

        $request = new Request([
            'no_po' => $po->no_po,
            'no_rim' => 2,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $response = $this->controller->addRim($request);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $po->no_po,
            'sum_rim' => 11,
            'end_rim' => 6
        ]);
    }

    /**
     * Test pengurutan label berdasarkan nomor rim
     *
     * Memverifikasi bahwa:
     * - Sistem mengurutkan label berdasarkan nomor rim
     * - Urutan ascending diterapkan dengan benar
     * - Semua label untuk PO yang sama terambil
     *
     * Steps:
     * 1. Buat beberapa data label test
     * 2. Request data label
     * 3. Verifikasi urutan data
     *
     * @return void
     */
    public function test_get_labels_ordered_by_rim_number(): void
    {
        $po = '12345';
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 3,
            'potongan' => 'Kiri',
            'team' => 1
        ]);
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'team' => 1
        ]);
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 2,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $response = $this->controller->getLabels($po);
        $labels = $response->getData();

        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, $labels[0]->no_rim);
        $this->assertEquals(2, $labels[1]->no_rim);
        $this->assertEquals(3, $labels[2]->no_rim);
    }
}
