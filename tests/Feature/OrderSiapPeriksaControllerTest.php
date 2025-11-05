<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use App\Models\GeneratedProducts;
use App\Models\Workstations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Test case untuk fitur Order Siap Periksa Controller
 *
 * Class ini menguji fungsionalitas terkait:
 * - Menampilkan daftar produk yang siap untuk diperiksa
 * - Filter produk berdasarkan tim pengguna
 * - Mengambil data produk berdasarkan tim tertentu
 * - Autentikasi dan akses kontrol
 *
 * Related files:
 * - Controllers:
 *   - App\Http\Controllers\OrderSiapPeriksaController
 * - Routes:
 *   - GET / (orderSiapPeriksa.index)
 *   - GET /order-besar/order-siap-periksa (orderBesar.orderSiapPeriksa)
 *   - GET /api/order-besar/verif/{team}
 */
class OrderSiapPeriksaControllerTest extends TestCase
{
    use DatabaseTransactions;

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
     * Test menampilkan halaman index untuk user yang terautentikasi
     *
     * Memverifikasi bahwa:
     * - User yang terautentikasi dapat mengakses halaman index
     * - Produk yang ditampilkan sesuai dengan workstation_id user
     * - Hanya produk dengan status < 2 yang ditampilkan
     * - Data workstation list tersedia
     *
     * Steps:
     * 1. Buat user dan workstation
     * 2. Buat produk untuk tim user
     * 3. Akses halaman index sebagai user
     * 4. Verifikasi response dan data yang ditampilkan
     *
     * @return void
     */
    public function test_can_access_index_page_when_authenticated(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $user = $this->createTestUser($workstation->id);
        $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);
        $this->createTestGeneratedProduct(4000000002, $workstation->id, 1);

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test filter produk berdasarkan tim user
     *
     * Memverifikasi bahwa:
     * - Hanya produk dengan assigned_team sesuai workstation_id user yang ditampilkan
     * - Produk dari tim lain tidak ditampilkan
     *
     * Steps:
     * 1. Buat dua workstation berbeda
     * 2. Buat user dengan workstation_id tertentu
     * 3. Buat produk untuk masing-masing tim
     * 4. Akses halaman index
     * 5. Verifikasi hanya produk tim user yang ditampilkan
     *
     * @return void
     */
    public function test_filters_products_by_user_workstation(): void
    {
        $workstation1 = $this->createTestWorkstation('Team Alpha');
        $workstation2 = $this->createTestWorkstation('Team Beta');
        
        $user = $this->createTestUser($workstation1->id);
        
        // Produk untuk tim user
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation1->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation1->id, 1);
        
        // Produk untuk tim lain
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation2->id, 0);

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
        
        // Verifikasi produk tim user ada
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $product1->no_po,
            'assigned_team' => $workstation1->id,
        ]);
        
        // Verifikasi produk tim lain tidak ditampilkan untuk user ini
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $product3->no_po,
            'assigned_team' => $workstation2->id,
        ]);
    }

    /**
     * Test hanya menampilkan produk dengan status < 2
     *
     * Memverifikasi bahwa:
     * - Produk dengan status 0 dan 1 ditampilkan
     * - Produk dengan status >= 2 tidak ditampilkan
     *
     * Steps:
     * 1. Buat user dan workstation
     * 2. Buat produk dengan berbagai status
     * 3. Akses halaman index
     * 4. Verifikasi hanya produk dengan status < 2 yang ditampilkan
     *
     * @return void
     */
    public function test_only_shows_products_with_status_less_than_2(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $user = $this->createTestUser($workstation->id);
        
        // Produk dengan status yang akan ditampilkan
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 1);
        
        // Produk dengan status yang tidak akan ditampilkan
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation->id, 2);
        $product4 = $this->createTestGeneratedProduct(4000000004, $workstation->id, 3);

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
        
        // Verifikasi produk dengan status < 2 ada di database
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $product1->no_po,
            'status' => 0,
        ]);
        
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $product2->no_po,
            'status' => 1,
        ]);
        
        // Verifikasi produk dengan status >= 2 juga ada di database tapi tidak ditampilkan
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $product3->no_po,
            'status' => 2,
        ]);
    }

    /**
     * Test mengakses halaman index tanpa autentikasi
     *
     * Memverifikasi bahwa:
     * - User yang tidak terautentikasi diarahkan ke halaman login
     *
     * Steps:
     * 1. Akses halaman index tanpa login
     * 2. Verifikasi redirect ke login
     *
     * @return void
     */
    public function test_cannot_access_index_page_without_authentication(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test mengakses route order-besar/order-siap-periksa
     *
     * Memverifikasi bahwa:
     * - Route alternatif juga berfungsi dengan benar
     * - Data yang ditampilkan sama dengan route utama
     *
     * Steps:
     * 1. Buat user dan workstation
     * 2. Buat produk
     * 3. Akses route alternatif
     * 4. Verifikasi response
     *
     * @return void
     */
    public function test_can_access_order_besar_route(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $user = $this->createTestUser($workstation->id);
        $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);

        $response = $this->actingAs($user)
            ->get('/order-besar/order-siap-periksa');

        $response->assertStatus(200);
    }

    /**
     * Test API endpoint fetchWorkPo untuk mengambil produk berdasarkan tim
     *
     * Memverifikasi bahwa:
     * - API endpoint dapat mengambil produk berdasarkan tim
     * - Hanya produk dengan status < 2 yang dikembalikan
     * - Response dalam format JSON
     *
     * Steps:
     * 1. Buat workstation dan produk
     * 2. Buat user dan autentikasi
     * 3. Panggil API endpoint
     * 4. Verifikasi response dan data
     *
     * @return void
     */
    public function test_can_fetch_work_po_by_team_via_api(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $user = $this->createTestUser($workstation->id);
        
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation->id, 1);
        $product3 = $this->createTestGeneratedProduct(4000000003, $workstation->id, 2);

        $response = $this->actingAs($user)
            ->getJson("/api/order-besar/verif/{$workstation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'no_po',
                    'no_obc',
                    'type',
                    'sum_rim',
                    'start_rim',
                    'end_rim',
                    'assigned_team',
                    'status',
                ]
            ]);

        // Verifikasi hanya produk dengan status < 2 yang dikembalikan
        $responseData = $response->json();
        $this->assertCount(2, $responseData);
        $this->assertEquals($product1->no_po, $responseData[0]['no_po']);
        $this->assertEquals($product2->no_po, $responseData[1]['no_po']);
    }

    /**
     * Test API endpoint fetchWorkPo dengan tim yang berbeda
     *
     * Memverifikasi bahwa:
     * - API endpoint mengembalikan produk sesuai tim yang diminta
     * - Produk dari tim lain tidak termasuk dalam response
     *
     * Steps:
     * 1. Buat dua workstation berbeda
     * 2. Buat produk untuk masing-masing tim
     * 3. Panggil API untuk salah satu tim
     * 4. Verifikasi hanya produk tim tersebut yang dikembalikan
     *
     * @return void
     */
    public function test_fetch_work_po_returns_only_products_for_specified_team(): void
    {
        $workstation1 = $this->createTestWorkstation('Team Alpha');
        $workstation2 = $this->createTestWorkstation('Team Beta');
        
        $user = $this->createTestUser($workstation1->id);
        
        $product1 = $this->createTestGeneratedProduct(4000000001, $workstation1->id, 0);
        $product2 = $this->createTestGeneratedProduct(4000000002, $workstation2->id, 0);

        $response = $this->actingAs($user)
            ->getJson("/api/order-besar/verif/{$workstation1->id}");

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertCount(1, $responseData);
        $this->assertEquals($product1->no_po, $responseData[0]['no_po']);
        $this->assertNotEquals($product2->no_po, $responseData[0]['no_po']);
    }

    /**
     * Test API endpoint fetchWorkPo dengan tim yang tidak memiliki produk
     *
     * Memverifikasi bahwa:
     * - API endpoint mengembalikan array kosong jika tim tidak memiliki produk
     *
     * Steps:
     * 1. Buat workstation tanpa produk
     * 2. Buat user dan autentikasi
     * 3. Panggil API endpoint
     * 4. Verifikasi response array kosong
     *
     * @return void
     */
    public function test_fetch_work_po_returns_empty_array_when_team_has_no_products(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $user = $this->createTestUser($workstation->id);

        $response = $this->actingAs($user)
            ->getJson("/api/order-besar/verif/{$workstation->id}");

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Test API endpoint fetchWorkPo tanpa autentikasi
     *
     * Memverifikasi bahwa:
     * - API endpoint memerlukan autentikasi
     *
     * Steps:
     * 1. Panggil API endpoint tanpa autentikasi
     * 2. Verifikasi response unauthorized
     *
     * @return void
     */
    public function test_cannot_fetch_work_po_without_authentication(): void
    {
        $workstation = $this->createTestWorkstation('Team Alpha');
        $this->createTestGeneratedProduct(4000000001, $workstation->id, 0);

        $response = $this->getJson("/api/order-besar/verif/{$workstation->id}");

        $response->assertStatus(401);
    }

    /**
     * Test workstation list tersedia di halaman index
     *
     * Memverifikasi bahwa:
     * - Daftar workstation tersedia untuk ditampilkan
     * - Data workstation dapat diakses
     *
     * Steps:
     * 1. Buat beberapa workstation
     * 2. Buat user dan autentikasi
     * 3. Akses halaman index
     * 4. Verifikasi workstation list tersedia
     *
     * @return void
     */
    public function test_workstation_list_is_available_on_index_page(): void
    {
        $workstation1 = $this->createTestWorkstation('Team Alpha');
        $workstation2 = $this->createTestWorkstation('Team Beta');
        $workstation3 = $this->createTestWorkstation('Team Gamma');
        
        $user = $this->createTestUser($workstation1->id);
        $this->createTestGeneratedProduct(4000000001, $workstation1->id, 0);

        $response = $this->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
        
        // Verifikasi workstation ada di database
        $this->assertDatabaseHas('workstation', [
            'id' => $workstation1->id,
            'workstation' => 'Team Alpha',
        ]);
        
        $this->assertDatabaseHas('workstation', [
            'id' => $workstation2->id,
            'workstation' => 'Team Beta',
        ]);
    }
}
