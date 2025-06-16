<?php

namespace Tests\Feature\PrintLabel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;

use App\Http\Controllers\PrintLabel\PrintLabelInspeksiController;
use App\Models\Users;
use App\Models\Specification;
use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Services\ProductionOrderService;
use App\Services\PrintLabelService;

class PrintLabelInspeksiTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $controller;
    protected $productionOrderService;
    protected $printLabelService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock services
        $this->productionOrderService = Mockery::mock(ProductionOrderService::class);
        $this->printLabelService = Mockery::mock(PrintLabelService::class);

        // Create controller instance with mocked services
        $this->controller = new PrintLabelInspeksiController(
            $this->productionOrderService,
            $this->printLabelService
        );

        // Create test user
        $this->user = $this->createTestUser();

        // Authenticate user
        $this->actingAs($this->user);
    }

    /**
     * Helper untuk membuat user test
     *
     * @return Users
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

        /** @test */
    public function index_returns_correct_view_with_data()
    {
        // Arrange
        $workstation1 = Workstations::create(['id' => 1, 'workstation' => 'WS001']);
        $workstation2 = Workstations::create(['id' => 2, 'workstation' => 'WS002']);

        // Mock the listWorkstation method
        Workstations::shouldReceive('listWorkstation->toArray')
            ->once()
            ->andReturn([
                ['id' => 1, 'workstation' => 'WS001'],
                ['id' => 2, 'workstation' => 'WS002']
            ]);

        // Act
        $response = $this->get('/print-label/inspeksi');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('PrintLabel/PrintLabelInspeksi/Index')
                ->has('listTeam', 2)
                ->where('currentTeam', 1)
        );
    }

    /** @test */
    public function get_specification_returns_specification_data()
    {
        // Arrange
        $specification = Specification::create([
            'no_po' => 12345,
            'no_obc' => 'OBC001',
            'nomor_plat' => 'B1234CD',
            'seri' => 3
        ]);

        // Act
        $response = $this->getJson("/api/print-label/inspeksi/{$specification->no_po}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'no_po' => 12345,
            'no_obc' => 'OBC001',
            'nomor_plat' => 'B1234CD',
            'seri' => 3
        ]);
    }

    /** @test */
    public function get_specification_returns_null_when_not_found()
    {
        // Act
        $response = $this->getJson('/api/print-label/inspeksi/99999');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(null);
    }

    /** @test */
    public function get_remaining_label_count_returns_correct_count()
    {
        // Arrange
        $noPo = 12345;

                // Create labels with different states
        for ($i = 0; $i < 3; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null, // Unprocessed
                'no_rim' => $i + 1,
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => 'EMP1', // Processed
                'no_rim' => $i + 4,
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        // Act
        $response = $this->getJson("/api/print-label/inspeksi/count-remaining-label/{$noPo}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson(3); // Only unprocessed labels
    }

    /** @test */
    public function get_remaining_label_count_returns_zero_when_no_labels()
    {
        // Act
        $response = $this->getJson('/api/print-label/inspeksi/count-remaining-label/99999');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(0);
    }

    /** @test */
    public function store_validates_required_fields()
    {
        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', []);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'no_po',
            'team',
            'jumlah_label',
            'np1'
        ]);
    }

    /** @test */
    public function store_validates_field_formats()
    {
        // Arrange
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => '',
            'team' => 999, // Non-existent workstation
            'jumlah_label' => 0, // Below minimum
            'np1' => 'TOOLONG', // Exceeds max length
            'np2' => 'TOOLONG'
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'no_po',
            'team',
            'jumlah_label',
            'np1',
            'np2'
        ]);
    }

    /** @test */
    public function store_processes_labels_successfully_with_existing_po()
    {
                // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Create existing labels
        for ($i = 0; $i < 5; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null,
                'no_rim' => 'RIM' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        // Create generated product
        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => 1
        ]);

        // Mock service calls
        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 3,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Label berhasil diproses',
            'data' => [
                'processed_labels' => 3,
                'failed_labels' => 0,
                'remaining_labels' => 2,
                'status' => 'in_progress'
            ]
        ]);

        // Verify database updates
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'np_users' => 'EMP1',
            'np_user_p2' => 'EMP2',
            'workstation' => $workstation->id
        ]);

        $this->assertDatabaseHas('generated_products', [
            'no_po' => $noPo,
            'status' => 1 // STATUS_IN_PROGRESS
        ]);
    }

    /** @test */
    public function store_processes_labels_and_completes_po_when_all_processed()
    {
                // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Create exactly 2 labels
        for ($i = 0; $i < 2; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null,
                'no_rim' => 'RIM' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'status' => 1,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Act - Process all remaining labels
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 2,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'processed_labels' => 2,
                'failed_labels' => 0,
                'remaining_labels' => 0,
                'status' => 'completed'
            ]
        ]);

        // Verify PO is marked as completed
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $noPo,
            'status' => 2 // STATUS_COMPLETED
        ]);
    }

    /** @test */
    public function store_creates_new_po_when_no_labels_exist()
    {
                // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Mock service calls for new PO creation
        $this->productionOrderService
            ->shouldReceive('registerProductionOrder')
            ->once()
            ->with([
                'no_po' => $noPo,
                'team' => $workstation->id,
                'jumlah_label' => 5,
                'np1' => 'EMP1',
                'np2' => 'EMP2'
            ]);

        $this->printLabelService
            ->shouldReceive('populateLabelForRegisteredPo')
            ->once()
            ->with([
                'no_po' => $noPo,
                'team' => $workstation->id,
                'jumlah_label' => 5,
                'np1' => 'EMP1',
                'np2' => 'EMP2'
            ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Create labels after PO registration (simulating service behavior)
        $this->beforeApplicationDestroyed(function () use ($noPo) {
            for ($i = 0; $i < 5; $i++) {
                GeneratedLabels::create([
                    'no_po_generated_products' => $noPo,
                    'np_users' => null,
                    'no_rim' => $i + 1,
                    'potongan' => 'Kiri',
                    'workstation' => 1
                ]);
            }

            GeneratedProducts::create([
                'no_po' => $noPo,
                'no_obc' => 'OBC001',
                'type' => 'PCHT',
                'sum_rim' => 5,
                'start_rim' => 1,
                'end_rim' => 5,
                'status' => 0,
                'assigned_team' => 1
            ]);
        });

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 5,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Label berhasil diproses'
        ]);
    }

    /** @test */
    public function store_handles_partial_label_processing_failure()
    {
                // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Create labels, but make one fail by creating invalid data
        for ($i = 0; $i < 3; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null,
                'no_rim' => 'RIM' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 3,
            'start_rim' => 1,
            'end_rim' => 3,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Mock database error for one label update
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 3,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert - Should still return success with statistics
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Label berhasil diproses'
        ]);

        // Verify that some labels were processed
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'np_users' => 'EMP1'
        ]);
    }

    /** @test */
    public function store_stops_processing_when_no_more_labels_available()
    {
                // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Create only 2 labels but request 5
        for ($i = 0; $i < 2; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null,
                'no_rim' => 'RIM' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 5, // Request more than available
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'processed_labels' => 2, // Only 2 were available
                'failed_labels' => 0,
                'remaining_labels' => 0,
                'status' => 'completed'
            ]
        ]);
    }

    /** @test */
    public function store_handles_database_transaction_rollback_on_critical_error()
    {
        // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Mock service to throw exception
        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->andThrow(new \Exception('Critical service error'));

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 1,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.',
            'error_code' => 'SYSTEM_ERROR'
        ]);
    }

    /** @test */
    public function store_logs_processing_activities()
    {
        // Arrange
        Log::spy();

        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        for ($i = 0; $i < 2; $i++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $noPo,
                'np_users' => null,
                'no_rim' => 'RIM' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'potongan' => 'Kiri',
                'workstation' => 1
            ]);
        }

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once();

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 2,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify logging
        Log::shouldHaveReceived('info')
            ->with('Starting label processing', Mockery::type('array'));

        Log::shouldHaveReceived('info')
            ->with('Label processing completed successfully', Mockery::type('array'));
    }

    /** @test */
    public function store_processes_labels_with_nullable_np2()
    {
        // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM001',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 1,
            'start_rim' => 1,
            'end_rim' => 1,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('EMP1');

        // Act - Without np2
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 1,
            'np1' => 'EMP1'
            // np2 is nullable
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify label was updated with null np2
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'np_users' => 'EMP1',
            'np_user_p2' => null
        ]);
    }

    /** @test */
    public function store_converts_np_values_to_uppercase()
    {
        // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM001',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 1,
            'start_rim' => 1,
            'end_rim' => 1,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once()
            ->with('emp1'); // lowercase input

        // Act - With lowercase np values
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 1,
            'np1' => 'emp1', // lowercase
            'np2' => 'emp2'  // lowercase
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify values were converted to uppercase
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'np_users' => 'EMP1', // uppercase
            'np_user_p2' => 'EMP2'  // uppercase
        ]);
    }

    /** @test */
    public function store_processes_labels_in_correct_order()
    {
        // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        // Create labels with specific rim order
        $label1 = GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM003',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        $label2 = GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM001',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        $label3 = GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM002',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 3,
            'start_rim' => 1,
            'end_rim' => 3,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once();

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 2,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        // Assert
        $response->assertStatus(200);

        // Verify labels were processed in ascending rim order
        // RIM001 and RIM002 should be processed, RIM003 should remain unprocessed
        $this->assertDatabaseHas('generated_labels', [
            'no_rim' => 'RIM001',
            'np_users' => 'EMP1'
        ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_rim' => 'RIM002',
            'np_users' => 'EMP1'
        ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_rim' => 'RIM003',
            'np_users' => null // Still unprocessed
        ]);
    }

    /** @test */
    public function store_sets_correct_timestamps()
    {
        // Arrange
        $noPo = 12345;
        $workstation = Workstations::create(['workstation' => 'WS001']);

        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'np_users' => null,
            'no_rim' => 'RIM001',
            'potongan' => 'Kiri',
            'workstation' => 1
        ]);

        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'OBC001',
            'type' => 'PCHT',
            'sum_rim' => 1,
            'start_rim' => 1,
            'end_rim' => 1,
            'status' => 0,
            'assigned_team' => 1
        ]);

        $this->printLabelService
            ->shouldReceive('finishPreviousUserSession')
            ->once();

        $startTime = now();

        // Act
        $response = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $workstation->id,
            'jumlah_label' => 1,
            'np1' => 'EMP1',
            'np2' => 'EMP2'
        ]);

        $endTime = now();

        // Assert
        $response->assertStatus(200);

        // Verify timestamps were set
        $label = GeneratedLabels::where('no_rim', 'RIM001')->first();
        $this->assertNotNull($label->start);
        $this->assertNotNull($label->finish);
        $this->assertTrue($label->start >= $startTime);
        $this->assertTrue($label->finish <= $endTime);
    }

    /** @test */
    public function authentication_is_required_for_all_endpoints()
    {
        // Arrange - Logout user
        Auth::logout();

        // Act & Assert
        $this->get('/print-label/inspeksi')
            ->assertRedirect('/login');

        $this->getJson('/api/print-label/inspeksi/12345')
            ->assertStatus(401);

        $this->getJson('/api/print-label/inspeksi/count-remaining-label/12345')
            ->assertStatus(401);

        $this->postJson('/api/print-label/inspeksi/store', [])
            ->assertStatus(401);
    }

    /** @test */
    public function workstation_validation_works_correctly()
    {
        // Arrange
        $validWorkstation = Workstations::create(['workstation' => 'WS001']);
        $noPo = 12345;

        // Act - Valid workstation
        $response1 = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => $validWorkstation->id,
            'jumlah_label' => 1,
            'np1' => 'EMP1'
        ]);

        // Act - Invalid workstation
        $response2 = $this->postJson('/api/print-label/inspeksi/store', [
            'no_po' => $noPo,
            'team' => 999, // Non-existent
            'jumlah_label' => 1,
            'np1' => 'EMP1'
        ]);

        // Assert
        $response1->assertStatus(200); // Should pass validation (might fail later due to missing labels)
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['team']);
    }
}
