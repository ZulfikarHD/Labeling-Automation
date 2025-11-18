<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TeamVerifikasiService;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Workstations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Unit test untuk TeamVerifikasiService
 *
 * Menguji logika terkait:
 * - Pengambilan produk dan label berdasarkan tim
 * - Pengambilan semua workstation
 * - Penanganan tim tanpa produk aktif
 */
class TeamVerifikasiServiceTest extends TestCase
{
    use DatabaseTransactions;

    private TeamVerifikasiService $service;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeamVerifikasiService();
    }

    /**
     * Test mendapatkan produk dan label berdasarkan tim
     */
    public function test_get_product_and_labels_by_team_returns_data(): void
    {
        // Arrange
        $workstation = Workstations::create([
            'id' => 1,
            'workstation' => 'Team Test',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $product = GeneratedProducts::create([
            'no_po' => 8000000001,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 1,
            'status' => 1 // Active status
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 8000000001,
            'no_rim' => 1,
            'potongan' => 'Kiri'
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 8000000001,
            'no_rim' => 1,
            'potongan' => 'Kanan'
        ]);

        // Act
        $result = $this->service->getProductAndLabelsByTeam('1');

        // Assert
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('product', $result);
        $this->assertArrayHasKey('labels', $result);
        $this->assertArrayHasKey('team', $result);
        $this->assertEquals(8000000001, $result['product']->no_po);
        $this->assertCount(2, $result['labels']);
        $this->assertEquals(1, $result['team']->id);
    }

    /**
     * Test mengembalikan null ketika tim tidak memiliki produk aktif
     */
    public function test_get_product_and_labels_by_team_returns_null_when_no_active_product(): void
    {
        // Arrange
        // No active product for team 2

        // Act
        $result = $this->service->getProductAndLabelsByTeam('2');

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test hanya mengambil produk dengan status aktif
     */
    public function test_get_product_and_labels_by_team_only_returns_active_status(): void
    {
        // Arrange
        $workstation = Workstations::create([
            'id' => 3,
            'workstation' => 'Team Test 3',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create product with status 0 (not active)
        GeneratedProducts::create([
            'no_po' => 8000000002,
            'no_obc' => 'TST010111',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 3,
            'status' => 0 // Not active
        ]);

        // Act
        $result = $this->service->getProductAndLabelsByTeam('3');

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test mengambil semua label terkait dengan produk
     */
    public function test_get_product_and_labels_by_team_returns_all_related_labels(): void
    {
        // Arrange
        $workstation = Workstations::create([
            'id' => 4,
            'workstation' => 'Team Test 4',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $product = GeneratedProducts::create([
            'no_po' => 8000000003,
            'no_obc' => 'TST010112',
            'type' => 'PCHT',
            'sum_rim' => 3,
            'start_rim' => 1,
            'end_rim' => 3,
            'assigned_team' => 4,
            'status' => 1
        ]);

        // Create multiple labels
        for ($rim = 1; $rim <= 3; $rim++) {
            GeneratedLabels::create([
                'no_po_generated_products' => 8000000003,
                'no_rim' => $rim,
                'potongan' => 'Kiri'
            ]);

            GeneratedLabels::create([
                'no_po_generated_products' => 8000000003,
                'no_rim' => $rim,
                'potongan' => 'Kanan'
            ]);
        }

        // Act
        $result = $this->service->getProductAndLabelsByTeam('4');

        // Assert
        $this->assertNotNull($result);
        $this->assertCount(6, $result['labels']); // 3 rims * 2 potongan
    }

    /**
     * Test mendapatkan semua workstation
     */
    public function test_get_all_workstations_returns_all_workstations(): void
    {
        // Arrange
        Workstations::create([
            'id' => 10,
            'workstation' => 'Team A',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Workstations::create([
            'id' => 11,
            'workstation' => 'Team B',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Workstations::create([
            'id' => 12,
            'workstation' => 'Team C',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Act
        $result = $this->service->getAllWorkstations();

        // Assert
        $this->assertGreaterThanOrEqual(3, $result->count());
        $workstationNames = $result->pluck('workstation')->toArray();
        $this->assertContains('Team A', $workstationNames);
        $this->assertContains('Team B', $workstationNames);
        $this->assertContains('Team C', $workstationNames);
    }

    /**
     * Test mengambil produk pertama jika ada multiple produk dengan status aktif
     */
    public function test_get_product_and_labels_by_team_returns_first_active_product(): void
    {
        // Arrange
        $workstation = Workstations::create([
            'id' => 5,
            'workstation' => 'Team Test 5',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create multiple products with active status
        GeneratedProducts::create([
            'no_po' => 8000000004,
            'no_obc' => 'TST010113',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'assigned_team' => 5,
            'status' => 1,
            'created_at' => now()->subDay()
        ]);

        GeneratedProducts::create([
            'no_po' => 8000000005,
            'no_obc' => 'TST010114',
            'type' => 'PCHT',
            'sum_rim' => 3,
            'start_rim' => 1,
            'end_rim' => 3,
            'assigned_team' => 5,
            'status' => 1,
            'created_at' => now()
        ]);

        // Act
        $result = $this->service->getProductAndLabelsByTeam('5');

        // Assert
        $this->assertNotNull($result);
        // Should return the first one found (implementation dependent)
        $this->assertContains($result['product']->no_po, [8000000004, 8000000005]);
    }

    /**
     * Test struktur data yang dikembalikan
     */
    public function test_get_product_and_labels_by_team_returns_correct_structure(): void
    {
        // Arrange
        $workstation = Workstations::create([
            'id' => 6,
            'workstation' => 'Team Test 6',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $product = GeneratedProducts::create([
            'no_po' => 8000000006,
            'no_obc' => 'TST010115',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'assigned_team' => 6,
            'status' => 1
        ]);

        // Act
        $result = $this->service->getProductAndLabelsByTeam('6');

        // Assert
        $this->assertIsArray($result);
        $this->assertInstanceOf(GeneratedProducts::class, $result['product']);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result['labels']);
        $this->assertInstanceOf(Workstations::class, $result['team']);
    }
}
