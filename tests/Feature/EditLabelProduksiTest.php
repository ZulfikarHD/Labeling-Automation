<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\DataInschiet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test case untuk fitur edit label produksi
 *
 * Class ini menguji fungsionalitas terkait:
 * - Pengambilan data label produksi
 * - Update data rim produksi
 * - Update data label
 * - Penambahan rim baru
 * - Validasi duplikasi rim
 * - Validasi input
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\ProductionOrderController
 *   - App\Http\Controllers\OrderBesar\CetakLabelController
 * - Services:
 *   - App\Services\PrintLabelService
 *   - App\Services\ProductionOrderService
 * - API Routes:
 *   - GET /api/production-order/get-labels/{po}
 *   - POST /api/production-order/update-rim
 *   - POST /api/production-order/update-label
 *   - POST /api/production-order/add-rim
 */
class EditLabelProduksiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $testPo = 4000000001;

    /**
     * Set up test environment
     *
     * Membuat user test untuk autentikasi
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createTestUser();
    }

    /**
     * Helper untuk membuat user test
     *
     * @return Users
     * @see App\Models\Users
     */
    private function createTestUser()
    {
        return Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password' => Hash::make('Test123'),
        ]);
    }

    /**
     * Helper untuk membuat data order produksi test
     *
     * @return GeneratedProducts
     * @see App\Models\GeneratedProducts
     * @see App\Services\ProductionOrderService::registerProductionOrder()
     */
    private function createTestProductionOrder()
    {
        return GeneratedProducts::create([
            'no_po' => $this->testPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => 1
        ]);
    }

    /**
     * Helper untuk membuat data label test
     *
     * @see App\Models\GeneratedLabels
     * @see App\Services\PrintLabelService::createLabel()
     */
    private function createTestLabels()
    {
        GeneratedLabels::create([
            'no_po_generated_products' => $this->testPo,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => $this->testPo,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => 'I444',
            'workstation' => 1
        ]);
    }

    /**
     * Test pengambilan data label order produksi
     *
     * @see App\Http\Controllers\ProductionOrderController::getLabels()
     * @see GET /api/production-order/get-labels/{po}
     */
    public function test_can_get_production_order_labels(): void
    {
        $this->createTestProductionOrder();
        $this->createTestLabels();

        $response = $this->actingAs($this->user)
            ->getJson("/api/production-order/get-labels/{$this->testPo}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'no_po_generated_products',
                    'no_rim',
                    'np_users',
                    'potongan',
                    'start',
                    'finish'
                ]
            ]);
    }

    /**
     * Test update data rim order produksi
     *
     * @see App\Http\Controllers\ProductionOrderController::updateRim()
     * @see App\Services\ProductionOrderService::updateProductionOrder()
     * @see POST /api/production-order/update-rim
     */
    public function test_can_update_production_order_rim(): void
    {
        $this->createTestProductionOrder();
        $this->createTestLabels();

        $updateData = [
            'no_po' => $this->testPo,
            'type' => 'PCHT',
            'sum_rim' => 6,
            'start_rim' => 1,
            'end_rim' => 6,
            'assigned_team' => 1,
            'status' => 1
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/update-rim', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'sum_rim' => 6,
            'end_rim' => 6
        ]);
    }

    /**
     * Test update data label
     *
     * @see App\Http\Controllers\ProductionOrderController::updateLabel()
     * @see App\Services\PrintLabelService::updateLabel()
     * @see POST /api/production-order/update-label
     */
    public function test_can_update_label(): void
    {
        $this->createTestProductionOrder();
        $label = GeneratedLabels::create([
            'no_po_generated_products' => $this->testPo,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        $updateData = [
            'id' => $label->id,
            'np_users' => 'I555',
            'team' => 1
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/update-label', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Label berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('generated_labels', [
            'id' => $label->id,
            'np_users' => 'I555'
        ]);
    }

    /**
     * Test penambahan rim baru
     *
     * @see App\Http\Controllers\ProductionOrderController::addRim()
     * @see App\Services\PrintLabelService::createLabel()
     * @see POST /api/production-order/add-rim
     */
    public function test_can_add_new_rim(): void
    {
        $this->createTestProductionOrder();

        $newRimData = [
            'no_po' => $this->testPo,
            'no_rim' => 6,
            'potongan' => 'both',
            'team' => 1
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/add-rim', $newRimData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Label berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $this->testPo,
            'no_rim' => 6,
            'potongan' => 'Kiri'
        ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $this->testPo,
            'no_rim' => 6,
            'potongan' => 'Kanan'
        ]);
    }

    /**
     * Test validasi duplikasi rim
     *
     * @see App\Http\Controllers\ProductionOrderController::addRim()
     * @see POST /api/production-order/add-rim
     */
    public function test_cannot_add_duplicate_rim(): void
    {
        $this->createTestProductionOrder();
        $this->createTestLabels();

        $duplicateRimData = [
            'no_po' => $this->testPo,
            'no_rim' => 1,
            'potongan' => 'both',
            'team' => 1
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/add-rim', $duplicateRimData);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Rim sudah ada dalam database'
            ]);
    }

    /**
     * Test validasi input saat update label
     *
     * @see App\Http\Controllers\ProductionOrderController::updateLabel()
     * @see POST /api/production-order/update-label
     */
    public function test_validation_errors_on_update_label(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/update-label', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id', 'team']);
    }

    /**
     * Test validasi input saat menambah rim
     *
     * @see App\Http\Controllers\ProductionOrderController::addRim()
     * @see POST /api/production-order/add-rim
     */
    public function test_validation_errors_on_add_rim(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/production-order/add-rim', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_po', 'no_rim', 'potongan', 'team']);
    }
}
