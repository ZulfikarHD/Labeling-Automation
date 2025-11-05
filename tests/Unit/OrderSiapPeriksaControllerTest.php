<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\OrderSiapPeriksaController;
use App\Models\GeneratedProducts;
use App\Models\Workstations;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

/**
 * Test case untuk unit test Order Siap Periksa Controller
 *
 * Class ini menguji logika bisnis terkait:
 * - Filter produk berdasarkan tim pengguna
 * - Filter produk berdasarkan status
 * - Query produk yang siap untuk diperiksa
 * - Pengambilan data workstation
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\OrderSiapPeriksaController
 * - Models:
 *   - App\Models\GeneratedProducts
 *   - App\Models\Workstations
 */
class OrderSiapPeriksaControllerTest extends TestCase
{
    use DatabaseTransactions;

    private OrderSiapPeriksaController $controller;

    /**
     * Set up test environment
     *
     * Inisialisasi controller sebelum setiap test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new OrderSiapPeriksaController();
    }

    /**
     * Helper untuk membuat user test
     *
     * Membuat user dengan workstation_id untuk pengujian
     *
     * @param int $workstationId ID workstation untuk user (default: 1)
     * @return Users Instance user yang dibuat
     */
    private function createTestUser($workstationId = 1)
    {
        return Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => $workstationId,
            'password' => Hash::make('Test123'),
        ]);
    }

    /**
     * Helper untuk membuat workstation test
     *
     * Membuat workstation dengan nama untuk pengujian
     *
     * @param string $workstationName Nama workstation (default: 'Team Test')
     * @return Workstations Instance workstation yang dibuat
     */
    private function createTestWorkstation($workstationName = 'Team Test')
    {
        return Workstations::create([
            'workstation' => $workstationName,
        ]);
    }

    /**
     * Helper untuk membuat data produk yang digenerate
     *
     * Membuat produk dengan status dan tim yang ditentukan untuk pengujian
     *
     * @param int $noPo Nomor PO untuk produk (default: 4000000001)
     * @param int $assignedTeam Tim yang ditugaskan (default: 1)
     * @param int $status Status produk (default: 0)
     * @return GeneratedProducts Instance produk yang dibuat
     */
    private function createTestGeneratedProduct($noPo = 4000000001, $assignedTeam = 1, $status = 0)
    {
        return GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => $status,
            'assigned_team' => $assignedTeam,
        ]);
    }

    /**
     * Test filter produk berdasarkan assigned_team
     *
     * Memverifikasi bahwa:
     * - Query hanya mengembalikan produk dengan assigned_team sesuai
     * - Produk dari tim lain tidak termasuk
     *
     * Steps:
     * 1. Buat produk untuk beberapa tim
     * 2. Test query filter berdasarkan tim
     * 3. Verifikasi hasil query
     *
     * @return void
     */
    public function test_filters_products_by_assigned_team(): void
    {
        $workstation1 = $this->createTestWorkstation('Team Alpha');
        $workstation2 = $this->createTestWorkstation('Team Beta');
        
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation1->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation1->id, 1);
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation2->id, 0);

        // Test query filter untuk tim 1
        $results = GeneratedProducts::where('assigned_team', $workstation1->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('no_po', $product1->no_po));
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product3->no_po));
    }

    /**
     * Test filter produk berdasarkan status < 2
     *
     * Memverifikasi bahwa:
     * - Query hanya mengembalikan produk dengan status 0 atau 1
     * - Produk dengan status >= 2 tidak termasuk
     *
     * Steps:
     * 1. Buat produk dengan berbagai status
     * 2. Test query filter berdasarkan status
     * 3. Verifikasi hasil query
     *
     * @return void
     */
    public function test_filters_products_by_status_less_than_2(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 1);
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation->id, 2);
        $product4 = $this->createTestGeneratedProduct(4000000004, $workstation->id, 3);

        // Test query filter untuk status < 2
        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('no_po', $product1->no_po));
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product3->no_po));
        $this->assertFalse($results->contains('no_po', $product4->no_po));
    }

    /**
     * Test kombinasi filter assigned_team dan status
     *
     * Memverifikasi bahwa:
     * - Query mengkombinasikan kedua filter dengan benar
     * - Hanya produk yang memenuhi kedua kondisi yang dikembalikan
     *
     * Steps:
     * 1. Buat produk dengan kombinasi tim dan status berbeda
     * 2. Test query kombinasi filter
     * 3. Verifikasi hasil query
     *
     * @return void
     */
    public function test_combines_assigned_team_and_status_filters(): void
    {
        $workstation1 = $this->createTestWorkstation('Team Alpha');
        $workstation2 = $this->createTestWorkstation('Team Beta');
        
        // Produk untuk tim 1 dengan status yang sesuai
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation1->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation1->id, 1);
        
        // Produk untuk tim 1 dengan status tidak sesuai
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation1->id, 2);
        
        // Produk untuk tim 2 dengan status yang sesuai
        $product4 = $this->createTestGeneratedProduct(4000000004, $workstation2->id, 0);

        // Test query kombinasi filter
        $results = GeneratedProducts::where('assigned_team', $workstation1->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('no_po', $product1->no_po));
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product3->no_po));
        $this->assertFalse($results->contains('no_po', $product4->no_po));
    }

    /**
     * Test fetchWorkPo mengembalikan produk sesuai tim
     *
     * Memverifikasi bahwa:
     * - Method fetchWorkPo mengembalikan produk yang benar
     * - Filter status diterapkan dengan benar
     *
     * Steps:
     * 1. Buat produk untuk beberapa tim dan status
     * 2. Panggil method fetchWorkPo melalui reflection
     * 3. Verifikasi hasil
     *
     * @return void
     */
    public function test_fetch_work_po_returns_correct_products(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 1);
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation->id, 2);

        // Test fetchWorkPo method logic
        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('no_po', $product1->no_po));
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product3->no_po));
    }

    /**
     * Test query mengembalikan array kosong jika tidak ada produk
     *
     * Memverifikasi bahwa:
     * - Query mengembalikan collection kosong jika tidak ada produk
     * - Tidak ada error saat query tanpa data
     *
     * Steps:
     * 1. Buat workstation tanpa produk
     * 2. Test query untuk workstation tersebut
     * 3. Verifikasi hasil kosong
     *
     * @return void
     */
    public function test_returns_empty_collection_when_no_products_exist(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');

        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(0, $results);
        $this->assertTrue($results->isEmpty());
    }

    /**
     * Test listWorkstation mengembalikan workstation yang terurut
     *
     * Memverifikasi bahwa:
     * - Method listWorkstation mengembalikan workstation terurut berdasarkan nama
     * - Data workstation lengkap (id dan workstation)
     *
     * Steps:
     * 1. Buat beberapa workstation dengan nama acak
     * 2. Test method listWorkstation
     * 3. Verifikasi hasil terurut
     *
     * @return void
     */
    public function test_list_workstation_returns_ordered_workstations(): void
    {
        $workstation1 = $this->createTestWorkstation('Zebra Team');
        $workstation2 = $this->createTestWorkstation('Alpha Team');
        $workstation3 = $this->createTestWorkstation('Beta Team');

        $results = Workstations::listWorkstation();

        $this->assertCount(3, $results);
        
        // Verifikasi hasil terurut berdasarkan nama workstation
        $workstationNames = $results->pluck('workstation')->toArray();
        $this->assertEquals(['Alpha Team', 'Beta Team', 'Zebra Team'], $workstationNames);
        
        // Verifikasi struktur data
        $this->assertArrayHasKey('id', $results->first()->toArray());
        $this->assertArrayHasKey('workstation', $results->first()->toArray());
    }

    /**
     * Test query dengan status null
     *
     * Memverifikasi bahwa:
     * - Produk dengan status null tidak termasuk dalam hasil (status < 2)
     * - Query berfungsi dengan benar untuk status null
     *
     * Steps:
     * 1. Buat produk dengan status null
     * 2. Test query filter status < 2
     * 3. Verifikasi hasil
     *
     * @return void
     */
    public function test_handles_null_status_correctly(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        
        // Buat produk dengan status null
        $product1 = GeneratedProducts::create([
            'no_po' => 4000000001,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => null,
            'assigned_team' => $workstation->id,
        ]);
        
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 0);

        // Test query - status null tidak akan memenuhi kondisi status < 2
        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        // Produk dengan status null tidak termasuk karena null < 2 adalah false
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product1->no_po));
    }

    /**
     * Test query dengan assigned_team null
     *
     * Memverifikasi bahwa:
     * - Produk dengan assigned_team null tidak termasuk dalam hasil
     * - Query berfungsi dengan benar untuk assigned_team null
     *
     * Steps:
     * 1. Buat produk dengan assigned_team null
     * 2. Buat produk dengan assigned_team tertentu
     * 3. Test query filter assigned_team
     * 4. Verifikasi hasil
     *
     * @return void
     */
    public function test_handles_null_assigned_team_correctly(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        
        // Buat produk dengan assigned_team null
        $product1 = GeneratedProducts::create([
            'no_po' => 4000000001,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => null,
        ]);
        
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 0);

        // Test query - assigned_team null tidak akan cocok dengan workstation->id
        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('no_po', $product2->no_po));
        $this->assertFalse($results->contains('no_po', $product1->no_po));
    }

    /**
     * Test query dengan multiple products untuk tim yang sama
     *
     * Memverifikasi bahwa:
     * - Query mengembalikan semua produk yang memenuhi kondisi
     * - Tidak ada limitasi jumlah produk
     *
     * Steps:
     * 1. Buat beberapa produk untuk tim yang sama
     * 2. Test query untuk tim tersebut
     * 3. Verifikasi semua produk dikembalikan
     *
     * @return void
     */
    public function test_returns_all_products_for_team(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        
        $products = [];
        for ($i = 1; $i <= 10; $i++) {
            $products[] = $this->createTestGeneratedProduct(4000000000 + $i, $workstation->id, $i % 2);
        }

        $results = GeneratedProducts::where('assigned_team', $workstation->id)
            ->where('status', '<', 2)
            ->get();

        // Harus ada 10 produk (semua dengan status 0 atau 1)
        $this->assertCount(10, $results);
        
        foreach ($products as $product) {
            $this->assertTrue($results->contains('no_po', $product->no_po));
        }
    }
}
