<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use App\Models\Workstations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Feature test untuk ProduksiPegawaiController
 */
class ProduksiPegawaiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $testWorkstation;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testWorkstation = $this->createTestWorkstation();
        $this->user = $this->createTestUser();
    }

    /**
     * Create test workstation
     */
    private function createTestWorkstation()
    {
        return Workstations::create([
            'id' => 1,
            'workstation' => 'Team Test',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Create test user
     */
    private function createTestUser()
    {
        return Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => $this->testWorkstation->id,
            'password' => Hash::make('Test123'),
        ]);
    }

    /**
     * Test tampilan halaman monitoring produksi pegawai
     */
    public function test_can_view_produksi_pegawai_index(): void
    {
        // Create additional workstations
        Workstations::create([
            'id' => 2,
            'workstation' => 'Team A',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Workstations::create([
            'id' => 3,
            'workstation' => 'Team B',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->user)
            ->get('/monitoring-produksi/produksi-pegawai');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('MonitoringProduksi/ProduksiPegawai')
                ->has('teams')
            );
    }

    /**
     * Test halaman menampilkan semua tim
     */
    public function test_index_displays_all_teams(): void
    {
        // Create multiple workstations
        $workstations = [];
        for ($i = 10; $i <= 15; $i++) {
            $workstations[] = Workstations::create([
                'id' => $i,
                'workstation' => "Team {$i}",
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/monitoring-produksi/produksi-pegawai');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('MonitoringProduksi/ProduksiPegawai')
                ->has('teams', count($workstations) + 1) // +1 for testWorkstation
            );
    }

    /**
     * Test akses tanpa autentikasi diredirect ke login
     */
    public function test_unauthenticated_access_redirects_to_login(): void
    {
        $response = $this->get('/monitoring-produksi/produksi-pegawai');

        $response->assertRedirect('/login');
    }

    /**
     * Test halaman dapat diakses oleh user yang terautentikasi
     */
    public function test_authenticated_user_can_access_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/monitoring-produksi/produksi-pegawai');

        $response->assertStatus(200);
    }
}
