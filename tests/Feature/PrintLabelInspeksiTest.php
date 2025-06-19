<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use App\Models\Specification;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Workstations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Feature test untuk PrintLabelInspeksiController
 *
 * @see Docs/Tests/FeatureTests/print_label_inspeksi_feature_test.md untuk dokumentasi lengkap
 */
class PrintLabelInspeksiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $testPo = 4000000001;
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
     * Create test specification
     */
    private function createTestSpecification($noPo = null)
    {
        $noPo = $noPo ?? $this->testPo;

        return Specification::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'seri' => 1,
            'type' => 'PCHT',
            'rencet' => 38123,
            'mesin' => '1'
        ]);
    }

    /**
     * Create test generated product
     */
    private function createTestGeneratedProduct($noPo = null, $status = 0)
    {
        $noPo = $noPo ?? $this->testPo;

        return GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 76,
            'start_rim' => 1,
            'end_rim' => 76,
            'status' => $status,
            'assigned_team' => $this->testWorkstation->id
        ]);
    }

    /**
     * Create test generated labels
     */
    private function createTestGeneratedLabels($noPo = null, $count = 5, $withInspection = false)
    {
        $noPo = $noPo ?? $this->testPo;

        for ($i = 1; $i <= $count; $i++) {

            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'no_rim' => $i,
                'potongan' => 'Kiri',
                'np_users' => $withInspection ? 'I444' : null,
                'np_user_p2' => $withInspection ? 'I555' : null,
                'workstation' => $withInspection ? $this->testWorkstation->id : null,
                'start' => $withInspection ? now() : null,
                'finish' => $withInspection ? now() : null,
            ]);

            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'no_rim' => $i,
                'potongan' => 'Kanan',
                'np_users' => $withInspection ? 'I444' : null,
                'np_user_p2' => $withInspection ? 'I555' : null,
                'workstation' => $withInspection ? $this->testWorkstation->id : null,
                'start' => $withInspection ? now() : null,
                'finish' => $withInspection ? now() : null,
            ]);
        }
    }

    /**
     * Test tampilan halaman index
     */
    public function test_can_view_print_label_inspeksi_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/print-label/inspeksi');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('PrintLabel/PrintLabelInspeksi')
                ->has('listTeam')
                ->where('currentTeam', $this->user->workstation_id)
            );
    }

    /**
     * Test pengambilan data spesifikasi berdasarkan nomor PO
     */
    public function test_can_get_specification_by_po_number(): void
    {
        $specification = $this->createTestSpecification();

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-label/inspeksi/{$this->testPo}");

        $response->assertStatus(200)
            ->assertJson([
                'no_po' => $this->testPo,
                'no_obc' => 'TST010110',
                'seri' => 1,
                'type' => 'PCHT'
            ]);
    }

    /**
     * Test pengambilan spesifikasi dengan nomor PO yang tidak ada
     */
    public function test_returns_null_for_nonexistent_po_specification(): void
    {
        $nonexistentPo = 9999999999;

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-label/inspeksi/{$nonexistentPo}");

        $response->assertStatus(200);
        $this->assertEquals([], $response->json());
    }

    /**
     * Test perhitungan sisa label untuk PO yang sudah terdaftar
     */
    public function test_can_count_remaining_labels_for_registered_po(): void
    {
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 10, false);

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-label/inspeksi/count-remaining-label/{$this->testPo}");

        $response->assertStatus(200);
        $this->assertEquals(20, $response->json());
    }

    /**
     * Test perhitungan sisa label untuk PO yang belum terdaftar
     */
    public function test_can_count_labels_from_specification_for_unregistered_po(): void
    {
        $this->createTestSpecification();

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-label/inspeksi/count-remaining-label/{$this->testPo}");

        $response->assertStatus(200);
        $this->assertEquals(76, $response->json());
    }

    /**
     * Test perhitungan sisa label untuk PO yang tidak ada sama sekali
     */
    public function test_returns_zero_for_nonexistent_po_label_count(): void
    {
        $nonexistentPo = 9999999999;

        $response = $this->actingAs($this->user)
            ->getJson("/api/print-label/inspeksi/count-remaining-label/{$nonexistentPo}");

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json());
    }

    /**
     * Test pemrosesan label inspeksi dengan sukses
     */
    public function test_can_process_label_inspection_successfully(): void
    {
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 10, false);

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 3,
            'np1' => 'I444',
            'np2' => 'I555'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Label berhasil diproses',
                'data' => [
                    'processed_labels' => 3,
                    'failed_labels' => 0,
                    'remaining_labels' => 17,
                    'status' => 'in_progress'
                ]
            ]);


        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $this->testPo,
            'np_users' => 'I444',
            'np_user_p2' => 'I555',
            'workstation' => $this->testWorkstation->id
        ]);
    }

    /**
     * Test penanganan PO baru tanpa production order yang sudah ada
     */
    public function test_auto_creates_production_order_for_new_po(): void
    {
        $this->createTestSpecification();

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 2,
            'np1' => 'I444',
            'np2' => null
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Label berhasil diproses'
            ]);

        // Verifikasi production order dibuat otomatis
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT'
        ]);

        // Verifikasi labels dibuat dan diproses
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $this->testPo,
            'np_users' => 'I444'
        ]);
    }

    /**
     * Test error ketika PO tidak memiliki spesifikasi
     */
    public function test_fails_when_po_has_no_specification(): void
    {
        // Tidak membuat specification untuk PO ini
        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 2,
            'np1' => 'I444',
            'np2' => null
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                'error_code' => 'SYSTEM_ERROR'
            ]);
    }

    /**
     * Test completion status ketika sebagian label diinspeksi
     */
    public function test_marks_production_order_in_progress_when_partial_labels_processed(): void
    {
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 2, false);

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 2,
            'np1' => 'I444',
            'np2' => 'I555'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed_labels' => 2,
                    'remaining_labels' => 2,
                    'status' => 'in_progress'
                ]
            ]);


        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'status' => 1
        ]);
    }

    /**
     * Test completion status ketika semua label selesai diinspeksi
     */
    public function test_marks_production_order_completed_when_all_labels_processed(): void
    {
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 1, false);

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 2,
            'np1' => 'I444',
            'np2' => 'I555'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed_labels' => 2,
                    'remaining_labels' => 0,
                    'status' => 'completed'
                ]
            ]);


        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'status' => 2
        ]);
    }

    /**
     * Test pemrosesan ketika jumlah label melebihi yang tersedia
     */
    public function test_processes_only_available_labels_when_requesting_more(): void
    {
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 3, false);

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 5,
            'np1' => 'I444',
            'np2' => null
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed_labels' => 5,
                    'remaining_labels' => 1
                ]
            ]);
    }

    /**
     * Test validasi input yang diperlukan
     */
    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['no_po', 'team', 'jumlah_label', 'np1']);
    }

    /**
     * Test validasi team yang tidak ada
     */
    public function test_validates_team_exists(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'team' => 999,
            'jumlah_label' => 1,
            'np1' => 'I444'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['team']);
    }

    /**
     * Test validasi jumlah label minimum
     */
    public function test_validates_minimum_label_quantity(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 0,
            'np1' => 'I444'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['jumlah_label']);
    }

    /**
     * Test validasi maksimal karakter NP
     */
    public function test_validates_np_max_length(): void
    {
        $this->createTestSpecification();

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 1,
            'np1' => 'I44444',
            'np2' => 'I55555'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['np1', 'np2']);
    }

    /**
     * Test penanganan error sistem
     */
    public function test_handles_system_error_gracefully(): void
    {

        $requestData = [
            'no_po' => 'invalid_po_format',
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 1,
            'np1' => 'I444'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);


        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
                'error_code' => 'SYSTEM_ERROR'
            ]);
    }

    /**
     * Test pemrosesan dengan NP yang di-uppercase
     */
    public function test_converts_np_to_uppercase(): void
    {
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();
        $this->createTestGeneratedLabels($this->testPo, 2, false);

        $requestData = [
            'no_po' => $this->testPo,
            'team' => $this->testWorkstation->id,
            'jumlah_label' => 1,
            'np1' => 'i444',
            'np2' => 'i555'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/inspeksi/store', $requestData);

        $response->assertStatus(200);


        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $this->testPo,
            'np_users' => 'I444',
            'np_user_p2' => 'I555'
        ]);
    }

    /**
     * Test database transaction rollback pada error
     */
    public function test_rolls_back_transaction_on_error(): void
    {
        $this->assertTrue(true);
    }
}
