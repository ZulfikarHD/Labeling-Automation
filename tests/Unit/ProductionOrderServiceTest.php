<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ProductionOrderService;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Unit test untuk ProductionOrderService
 *
 * Menguji logika bisnis terkait:
 * - Pendaftaran production order
 * - Perhitungan total rim
 * - Pengecekan status PO
 * - Validasi PO terdaftar
 */
class ProductionOrderServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ProductionOrderService $service;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductionOrderService();
    }

    /**
     * Test pendaftaran production order baru
     */
    public function test_register_production_order_creates_new_order(): void
    {
        // Arrange
        $productionOrder = [
            'po' => 6000000001,
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'start_rim' => 1,
            'end_rim' => 5,
            'team' => 1
        ];

        // Act
        $this->service->registerProductionOrder($productionOrder);

        // Assert
        $this->assertDatabaseHas('generated_products', [
            'no_po' => 6000000001,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5, // 2500 / 500 = 5
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 1,
            'status' => 0
        ]);
    }

    /**
     * Test pendaftaran production order dengan jumlah lembar kurang dari 500
     */
    public function test_register_production_order_with_less_than_500_sheets(): void
    {
        // Arrange
        $productionOrder = [
            'po' => 6000000002,
            'obc' => 'TST010111',
            'jml_lembar' => 250, // Less than 500
            'start_rim' => 1,
            'end_rim' => 1,
            'team' => 1
        ];

        // Act
        $this->service->registerProductionOrder($productionOrder);

        // Assert - Should have minimum 1 rim
        $this->assertDatabaseHas('generated_products', [
            'no_po' => 6000000002,
            'sum_rim' => 1 // Minimum rim
        ]);
    }

    /**
     * Test pendaftaran production order dengan jumlah lembar tepat 500
     */
    public function test_register_production_order_with_exactly_500_sheets(): void
    {
        // Arrange
        $productionOrder = [
            'po' => 6000000003,
            'obc' => 'TST010112',
            'jml_lembar' => 500,
            'start_rim' => 1,
            'end_rim' => 1,
            'team' => 1
        ];

        // Act
        $this->service->registerProductionOrder($productionOrder);

        // Assert
        $this->assertDatabaseHas('generated_products', [
            'no_po' => 6000000003,
            'sum_rim' => 1
        ]);
    }

    /**
     * Test pendaftaran production order dengan jumlah lembar lebih dari 500
     */
    public function test_register_production_order_with_more_than_500_sheets(): void
    {
        // Arrange
        $productionOrder = [
            'po' => 6000000004,
            'obc' => 'TST010113',
            'jml_lembar' => 1500,
            'start_rim' => 1,
            'end_rim' => 3,
            'team' => 1
        ];

        // Act
        $this->service->registerProductionOrder($productionOrder);

        // Assert
        $this->assertDatabaseHas('generated_products', [
            'no_po' => 6000000004,
            'sum_rim' => 3 // 1500 / 500 = 3
        ]);
    }

    /**
     * Test error ketika PO sudah terdaftar
     */
    public function test_throws_exception_when_po_already_registered(): void
    {
        // Arrange
        GeneratedProducts::create([
            'no_po' => 6000000005,
            'no_obc' => 'TST010114',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 1,
            'status' => 0
        ]);

        $productionOrder = [
            'po' => 6000000005,
            'obc' => 'TST010115',
            'jml_lembar' => 2500,
            'start_rim' => 1,
            'end_rim' => 5,
            'team' => 1
        ];

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nomor PO sudah terdaftar dalam sistem');

        // Act
        $this->service->registerProductionOrder($productionOrder);
    }

    /**
     * Test cek production order terdaftar
     */
    public function test_registered_production_order_returns_query_builder(): void
    {
        // Arrange
        GeneratedProducts::create([
            'no_po' => 6000000006,
            'no_obc' => 'TST010116',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 1,
            'status' => 0
        ]);

        // Act
        $result = $this->service->registeredProductionOrder(6000000006);

        // Assert
        $this->assertTrue($result->exists());
        $this->assertEquals(6000000006, $result->first()->no_po);
    }

    /**
     * Test cek label PCHT terdaftar
     */
    public function test_cek_label_pcht_terdaftar_returns_query_builder(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 6000000007,
            'no_rim' => 1,
            'potongan' => 'Kiri'
        ]);

        // Act
        $result = $this->service->cekLabelPchtTerdaftar(6000000007);

        // Assert
        $this->assertTrue($result->exists());
        $this->assertEquals(6000000007, $result->first()->no_po_generated_products);
    }

    /**
     * Test mendapatkan list nomor rim PCHT
     */
    public function test_get_list_nomor_rim_pcht_returns_ordered_list(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 6000000008,
            'no_rim' => 3,
            'potongan' => 'Kiri'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 6000000008,
            'no_rim' => 1,
            'potongan' => 'Kiri'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 6000000008,
            'no_rim' => 2,
            'potongan' => 'Kanan'
        ]);

        // Act
        $result = $this->service->getListNomorRimPcht(6000000008)->get();

        // Assert
        $this->assertCount(3, $result);
        $this->assertEquals(1, $result[0]->no_rim);
        $this->assertEquals(2, $result[1]->no_rim);
        $this->assertEquals(3, $result[2]->no_rim);
    }

    /**
     * Test cek PO selesai ketika semua label memiliki np_users
     */
    public function test_is_po_finished_returns_true_when_all_labels_have_np_users(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 6000000009,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 6000000009,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => 'I555'
        ]);

        // Act
        $result = $this->service->isPoFinished(6000000009);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test cek PO belum selesai ketika ada label tanpa np_users
     */
    public function test_is_po_finished_returns_false_when_some_labels_missing_np_users(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 6000000010,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 6000000010,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => null // Missing np_users
        ]);

        // Act
        $result = $this->service->isPoFinished(6000000010);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test cek PO belum selesai ketika ada label dengan np_users kosong
     */
    public function test_is_po_finished_returns_false_when_some_labels_have_empty_np_users(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 6000000011,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 6000000011,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => '' // Empty string
        ]);

        // Act
        $result = $this->service->isPoFinished(6000000011);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test perhitungan rim dengan jumlah lembar yang tidak habis dibagi
     */
    public function test_calculate_rims_with_non_divisible_sheets(): void
    {
        // Arrange
        $productionOrder = [
            'po' => 6000000012,
            'obc' => 'TST010117',
            'jml_lembar' => 1250, // 1250 / 500 = 2.5, should floor to 2
            'start_rim' => 1,
            'end_rim' => 2,
            'team' => 1
        ];

        // Act
        $this->service->registerProductionOrder($productionOrder);

        // Assert
        $this->assertDatabaseHas('generated_products', [
            'no_po' => 6000000012,
            'sum_rim' => 2 // Floored from 2.5
        ]);
    }

    /**
     * Test transaction rollback pada error
     */
    public function test_transaction_rollback_on_error(): void
    {
        // Arrange - Create PO first
        GeneratedProducts::create([
            'no_po' => 6000000013,
            'no_obc' => 'TST010118',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 1,
            'status' => 0
        ]);

        $productionOrder = [
            'po' => 6000000013, // Duplicate PO
            'obc' => 'TST010119',
            'jml_lembar' => 2500,
            'start_rim' => 1,
            'end_rim' => 5,
            'team' => 1
        ];

        // Act & Assert
        try {
            $this->service->registerProductionOrder($productionOrder);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verify no duplicate was created
            $count = GeneratedProducts::where('no_po', 6000000013)->count();
            $this->assertEquals(1, $count); // Only the original one
        }
    }
}
