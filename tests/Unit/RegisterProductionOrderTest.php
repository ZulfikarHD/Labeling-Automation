<?php

namespace Tests\Unit;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use Tests\TestCase;
use App\Services\ProductionOrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Exception;

/**
 * Test case untuk fitur registrasi dan pengelolaan Production Order
 *
 * Class ini menguji fungsionalitas terkait:
 * - Registrasi Production Order baru
 * - Penanganan duplikasi nomor PO
 * - Kalkulasi jumlah rim
 * - Pengelolaan label PCHT
 * - Status penyelesaian PO
 *
 * Related files:
 * - Services:
 *   - App\Services\ProductionOrderService
 * - Models:
 *   - App\Models\GeneratedProducts
 *   - App\Models\GeneratedLabels
 *   - App\Models\Specification
 */
class RegisterProductionOrderTest extends TestCase
{
    use DatabaseTransactions;

    private ProductionOrderService $service;

    /**
     * Set up test environment
     *
     * Inisialisasi ProductionOrderService sebelum setiap test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductionOrderService();
    }

    /**
     * Helper untuk membuat data dummy testing
     *
     * Membuat data spesifikasi dan generated product untuk pengujian
     *
     * @return void
     */
    private function create_dummy_data_for_testing()
    {
        // Buat data spesifikasi
        $create_specification = Specification::create([
            'no_po'  => 3999999999,
            'no_obc' => "TST010110",
            'seri'   => 1,
            'type'   => "NP",
            'rencet' => 38123,
        ]);

        // Buat data generated product
        $create_generated_products  = GeneratedProducts::create([
            'no_po'     => 3999999999,
            'no_obc'    => "TST010110",
            'type'      => "PCHT",
            'sum_rim'   => 39,
            'start_rim' => 1,
            'end_rim'   => 39,
            'status'    => 0,
            'assigned_team' => 1,
        ]);
    }

    /**
     * Test registrasi Production Order berhasil
     *
     * Memverifikasi bahwa:
     * - PO dapat diregistrasi dengan benar
     * - Data tersimpan sesuai di database
     * - Kalkulasi rim berjalan dengan benar
     *
     * Steps:
     * 1. Siapkan data PO
     * 2. Registrasi PO
     * 3. Verifikasi data tersimpan
     *
     * @return void
     */
    public function test_register_production_order_successfully(): void
    {
        $productionOrder = [
            'po' => 4000000000,
            'obc' => 'TST010110',
            'jml_lembar' => 2500, // 5 rim (2500/500)
            'team' => 1,
            'start_rim' => 1,
            'end_rim' => 3
        ];

        $this->service->registerProductionOrder($productionOrder);

        $result = GeneratedProducts::where('no_po', $productionOrder['po'])->first();

        $this->assertNotNull($result);
        $this->assertEquals($productionOrder['po'], $result->no_po);
        $this->assertEquals($productionOrder['obc'], $result->no_obc);
        $this->assertEquals('PCHT', $result->type);
        $this->assertEquals(1, $result->status);
        $this->assertEquals(5, $result->sum_rim);
        $this->assertEquals(1, $result->start_rim);
        $this->assertEquals(3, $result->end_rim);
        $this->assertEquals($productionOrder['team'], $result->assigned_team);
    }

    /**
     * Test penanganan duplikasi nomor PO
     *
     * Memverifikasi bahwa:
     * - Sistem menolak PO yang sudah terdaftar
     * - Exception dilempar dengan pesan yang sesuai
     *
     * Steps:
     * 1. Buat data PO awal
     * 2. Coba registrasi PO yang sama
     * 3. Verifikasi exception
     *
     * @return void
     */
    public function test_register_duplicate_po_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nomor PO sudah terdaftar dalam sistem');

        $this->create_dummy_data_for_testing();

        $productionOrder = [
            'po' => 3999999999,
            'obc' => 'TST010110',
            'jml_lembar' => 38123,
            'team' => 1
        ];

        $this->service->registerProductionOrder($productionOrder);
    }

    /**
     * Test kalkulasi minimum rim untuk order kecil
     *
     * Memverifikasi bahwa:
     * - Order dengan jumlah lembar kecil tetap mendapat minimal 1 rim
     * - Data tersimpan dengan jumlah rim yang benar
     *
     * Steps:
     * 1. Buat order dengan jumlah lembar kecil
     * 2. Registrasi PO
     * 3. Verifikasi jumlah rim
     *
     * @return void
     */
    public function test_calculate_minimum_rim_for_small_orders(): void
    {
        $productionOrder = [
            'po' => 4000000002,
            'obc' => 'TEST124',
            'jml_lembar' => 100,
            'team' => 1,
            'start_rim' => 1,
            'end_rim' => 1
        ];

        $this->service->registerProductionOrder($productionOrder);

        $result = GeneratedProducts::where('no_po', $productionOrder['po'])->first();

        $this->assertEquals(1, $result->sum_rim);
    }

    /**
     * Test registrasi label PCHT
     *
     * Memverifikasi bahwa:
     * - Label PCHT dapat diregistrasi
     * - Jumlah label terdaftar sesuai
     *
     * Steps:
     * 1. Buat data label
     * 2. Verifikasi jumlah label
     *
     * @return void
     */
    public function test_check_pcht_label_registration(): void
    {
        $po_number = 4000000003;

        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri'
        ]);

        $result = $this->service->cekLabelPchtTerdaftar($po_number)->count();

        $this->assertEquals(1, $result);
    }

    /**
     * Test pengurutan nomor rim
     *
     * Memverifikasi bahwa:
     * - Nomor rim terurut dengan benar
     * - Data diambil sesuai urutan
     *
     * Steps:
     * 1. Buat data label dengan urutan acak
     * 2. Ambil data terurut
     * 3. Verifikasi urutan
     *
     * @return void
     */
    public function test_get_rim_numbers_ordered_correctly(): void
    {
        $po_number = 4000000004;

        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 2,
            'potongan' => 'Kiri'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kanan'
        ]);

        $results = $this->service->getListNomorRimPcht($po_number)->get();

        $this->assertEquals(1, $results[0]->no_rim);
        $this->assertEquals(2, $results[1]->no_rim);
    }

    /**
     * Test status penyelesaian PO
     *
     * Memverifikasi bahwa:
     * - PO ditandai selesai saat semua label diproses
     * - Status selesai terdeteksi dengan benar
     *
     * Steps:
     * 1. Buat label yang sudah diproses
     * 2. Verifikasi status selesai
     *
     * @return void
     */
    public function test_po_is_finished_when_all_labels_processed(): void
    {
        $po_number = 4000000005;

        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'TestUser1'
        ]);

        $this->assertTrue($this->service->isPoFinished($po_number));
    }

    /**
     * Test status PO belum selesai
     *
     * Memverifikasi bahwa:
     * - PO tidak ditandai selesai jika masih ada label belum diproses
     * - Status belum selesai terdeteksi dengan benar
     *
     * Steps:
     * 1. Buat label yang belum diproses
     * 2. Verifikasi status belum selesai
     *
     * @return void
     */
    public function test_po_is_not_finished_with_unprocessed_labels(): void
    {
        $po_number = 4000000006;

        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => null
        ]);

        $this->assertFalse($this->service->isPoFinished($po_number));
    }
}
