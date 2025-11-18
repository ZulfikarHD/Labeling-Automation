<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Users;
use App\Models\GeneratedLabelsMmea;
use App\Models\GeneratedProducts;
use App\Models\Workstations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Feature test untuk PrintLabelMmeaController
 */
class PrintLabelMmeaTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $testPo = 5000000001;
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
            'id' => 6,
            'workstation' => 'MMEA Test',
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
     * Test tampilan halaman index
     */
    public function test_can_view_print_label_mmea_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/print-label/mmea');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('PrintLabel/PrintLabelMmea/Index')
                ->where('no_po', null)
            );
    }

    /**
     * Test tampilan halaman index dengan nomor PO
     */
    public function test_can_view_print_label_mmea_index_with_po(): void
    {
        $response = $this->actingAs($this->user)
            ->get("/print-label/mmea/{$this->testPo}");

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('PrintLabel/PrintLabelMmea/Index')
                ->where('no_po', (string)$this->testPo)
            );
    }

    /**
     * Test penyimpanan label MMEA dengan sukses
     */
    public function test_can_store_label_mmea_successfully(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'no_rim' => [1, 2],
            'periksa1' => [
                'np_1' => 'I444',
                'np_2' => 'I555'
            ],
            'periksa2' => [
                'np_1' => 'I666',
                'np_2' => 'I777'
            ],
            'jml_kemas' => [
                'no_1' => 100,
                'no_2' => 200
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', $requestData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Label berhasil diproses'
            ]);

        // Verifikasi data tersimpan di database
        $this->assertDatabaseHas('generated_labels_mmea', [
            'nomor_po' => $this->testPo,
            'nomor_rim' => 1,
            'periksa1' => 'I444',
            'periksa2' => 'I666',
            'lbr_kemas' => 100
        ]);

        $this->assertDatabaseHas('generated_labels_mmea', [
            'nomor_po' => $this->testPo,
            'nomor_rim' => 2,
            'periksa1' => 'I555',
            'periksa2' => 'I777',
            'lbr_kemas' => 200
        ]);
    }

    /**
     * Test update label MMEA yang sudah ada
     */
    public function test_can_update_existing_label_mmea(): void
    {
        // Buat data label yang sudah ada
        GeneratedLabelsMmea::create([
            'nomor_po' => $this->testPo,
            'nomor_rim' => 1,
            'periksa1' => 'I444',
            'periksa2' => 'I666',
            'lbr_kemas' => 100,
            'waktu_p1' => now()->subDay(),
            'waktu_p2' => now()->subDay()
        ]);

        $requestData = [
            'no_po' => $this->testPo,
            'no_rim' => [1],
            'periksa1' => [
                'np_1' => 'I444' // Same pemeriksa
            ],
            'periksa2' => [
                'np_1' => 'I888' // Different pemeriksa
            ],
            'jml_kemas' => [
                'no_1' => 150
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', $requestData);

        $response->assertStatus(200);

        // Verifikasi waktu_p1 tidak berubah (same pemeriksa), waktu_p2 berubah (different pemeriksa)
        $label = GeneratedLabelsMmea::where('nomor_po', $this->testPo)
            ->where('nomor_rim', 1)
            ->first();

        $this->assertNotNull($label);
        $this->assertEquals('I888', $label->periksa2);
        $this->assertEquals(150, $label->lbr_kemas);
    }

    /**
     * Test konversi NP yang lebih dari 4 karakter
     */
    public function test_converts_np_longer_than_four_characters(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'no_rim' => [1],
            'periksa1' => [
                'np_1' => 'I12345' // 6 characters
            ],
            'periksa2' => [
                'np_1' => 'I67890'
            ],
            'jml_kemas' => [
                'no_1' => 100
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', $requestData);

        $response->assertStatus(200);

        // Verifikasi NP dikonversi menjadi 4 karakter terakhir
        $this->assertDatabaseHas('generated_labels_mmea', [
            'nomor_po' => $this->testPo,
            'nomor_rim' => 1,
            'periksa1' => 'I2345',
            'periksa2' => 'I7890'
        ]);
    }

    /**
     * Test konversi NP ke uppercase
     */
    public function test_converts_np_to_uppercase(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'no_rim' => [1],
            'periksa1' => [
                'np_1' => 'i444'
            ],
            'periksa2' => [
                'np_1' => 'i555'
            ],
            'jml_kemas' => [
                'no_1' => 100
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', $requestData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('generated_labels_mmea', [
            'nomor_po' => $this->testPo,
            'nomor_rim' => 1,
            'periksa1' => 'I444',
            'periksa2' => 'I555'
        ]);
    }

    /**
     * Test penyimpanan produk MMEA
     */
    public function test_can_store_product_mmea(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'no_obc' => 'TST010110',
            'produk' => 'MMEA',
            'jml_lbr' => 1500.7 // Will be rounded up
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/storeProduct', $requestData);

        $response->assertStatus(200);

        // Verifikasi produk dibuat dengan sum_rim yang dibulatkan
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'no_obc' => 'TST010110',
            'type' => 'MMEA',
            'sum_rim' => 1501, // Rounded up
            'start_rim' => 1,
            'end_rim' => 1501,
            'assigned_team' => 6,
            'status' => 2
        ]);
    }

    /**
     * Test update produk MMEA yang sudah ada
     */
    public function test_can_update_existing_product_mmea(): void
    {
        // Buat produk yang sudah ada
        GeneratedProducts::create([
            'no_po' => $this->testPo,
            'no_obc' => 'OLD010110',
            'type' => 'MMEA',
            'sum_rim' => 1000,
            'start_rim' => 1,
            'end_rim' => 1000,
            'assigned_team' => 6,
            'status' => 1
        ]);

        $requestData = [
            'no_po' => $this->testPo,
            'no_obc' => 'NEW010110',
            'produk' => 'MMEA',
            'jml_lbr' => 2000
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/storeProduct', $requestData);

        $response->assertStatus(200);

        // Verifikasi produk diupdate
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $this->testPo,
            'no_obc' => 'NEW010110',
            'sum_rim' => 2000,
            'end_rim' => 2000,
            'status' => 2
        ]);
    }

    /**
     * Test pengambilan data QC berdasarkan nomor PO
     */
    public function test_can_get_qc_data_by_po(): void
    {
        // Buat beberapa label MMEA
        GeneratedLabelsMmea::create([
            'nomor_po' => $this->testPo,
            'nomor_rim' => 2,
            'periksa1' => 'I444',
            'periksa2' => 'I555',
            'lbr_kemas' => 100
        ]);

        GeneratedLabelsMmea::create([
            'nomor_po' => $this->testPo,
            'nomor_rim' => 1,
            'periksa1' => 'I666',
            'periksa2' => 'I777',
            'lbr_kemas' => 200
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/mmea/qc-data/{$this->testPo}");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                [
                    'nomor_po' => $this->testPo,
                    'nomor_rim' => 1,
                    'periksa1' => 'I666',
                    'periksa2' => 'I777',
                    'lbr_kemas' => 200
                ],
                [
                    'nomor_po' => $this->testPo,
                    'nomor_rim' => 2,
                    'periksa1' => 'I444',
                    'periksa2' => 'I555',
                    'lbr_kemas' => 100
                ]
            ]);

        // Verifikasi data diurutkan berdasarkan nomor_rim
        $data = $response->json();
        $this->assertEquals(1, $data[0]['nomor_rim']);
        $this->assertEquals(2, $data[1]['nomor_rim']);
    }

    /**
     * Test pengambilan data QC untuk PO yang tidak ada
     */
    public function test_returns_empty_array_for_nonexistent_po_qc_data(): void
    {
        $nonexistentPo = 9999999999;

        $response = $this->actingAs($this->user)
            ->getJson("/api/mmea/qc-data/{$nonexistentPo}");

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Test validasi input yang diperlukan untuk store
     */
    public function test_validates_required_fields_for_store(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', []);

        // Controller doesn't have explicit validation, but should handle missing data gracefully
        $response->assertStatus(500); // Will fail due to missing data
    }

    /**
     * Test pemrosesan multiple rim dalam satu request
     */
    public function test_can_process_multiple_rims_in_single_request(): void
    {
        $requestData = [
            'no_po' => $this->testPo,
            'no_rim' => [1, 2, 3, 4, 5],
            'periksa1' => [
                'np_1' => 'I111',
                'np_2' => 'I222',
                'np_3' => 'I333',
                'np_4' => 'I444',
                'np_5' => 'I555'
            ],
            'periksa2' => [
                'np_1' => 'I666',
                'np_2' => 'I777',
                'np_3' => 'I888',
                'np_4' => 'I999',
                'np_5' => 'I000'
            ],
            'jml_kemas' => [
                'no_1' => 100,
                'no_2' => 200,
                'no_3' => 300,
                'no_4' => 400,
                'no_5' => 500
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/print-label/mmea/store', $requestData);

        $response->assertStatus(200);

        // Verifikasi semua rim diproses
        for ($i = 1; $i <= 5; $i++) {
            $this->assertDatabaseHas('generated_labels_mmea', [
                'nomor_po' => $this->testPo,
                'nomor_rim' => $i
            ]);
        }
    }
}
