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
 * Class RegisterProductionOrderTest
 *
 * This class contains unit tests for the Production Order registration functionality.
 * It tests various scenarios including successful registration, handling of duplicate
 * PO numbers, and validation of rim calculations.
 */
class RegisterProductionOrderTest extends TestCase
{
    use DatabaseTransactions;

    private ProductionOrderService $service;

    /**
     * Set up the test environment.
     * Initializes the ProductionOrderService before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductionOrderService();
    }

    /**
     * Create dummy data for testing purposes.
     * This method sets up initial data in the database to facilitate testing.
     */
    private function create_dummy_data_for_testing()
    {
        // Create a specification entry for testing
        $create_specification = Specification::create([
            'no_po'  => 3999999999,
            'no_obc' => "TST010110",
            'seri'   => 1,
            'type'   => "NP",
            'rencet' => 38123,
        ]);

        // Create a generated product entry for testing
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
     * Test successful registration of a production order.
     * This test verifies that a production order can be registered correctly.
     */
    public function test_register_production_order_successfully(): void
    {
        // Uncomment the line below to create dummy data for testing
        // $this->create_dummy_data_for_testing();

        $productionOrder = [
            'po' => 4000000000,
            'obc' => 'TST010110',
            'jml_lembar' => 2500, // 5 rims (2500/500)
            'team' => 1
        ];

        // Register the production order
        $this->service->registerProductionOrder($productionOrder);

        // Retrieve the generated product to verify registration
        $result = GeneratedProducts::where('no_po', $productionOrder['po'])->first();

        // Assertions to verify the registration was successful
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
     * Test that registering a duplicate PO throws an exception.
     * This test ensures that the system prevents duplicate PO registrations.
     */
    public function test_register_duplicate_po_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nomor PO sudah terdaftar dalam sistem');

        // Create dummy data for testing
        $this->create_dummy_data_for_testing();

        $productionOrder = [
            'po' => 3999999999, // Using same PO from dummy data
            'obc' => 'TST010110',
            'jml_lembar' => 38123,
            'team' => 1
        ];

        // Attempt to register the duplicate production order
        $this->service->registerProductionOrder($productionOrder);
    }

    /**
     * Test the calculation of minimum rims for small orders.
     * This test verifies that small orders default to a minimum of 1 rim.
     */
    public function test_calculate_minimum_rim_for_small_orders(): void
    {
        $productionOrder = [
            'po' => 4000000002,
            'obc' => 'TEST124',
            'jml_lembar' => 100, // Less than 500 sheets
            'team' => 1
        ];

        // Register the production order
        $this->service->registerProductionOrder($productionOrder);

        // Retrieve the generated product to verify rim calculation
        $result = GeneratedProducts::where('no_po', $productionOrder['po'])->first();

        // Assert that the sum_rim is set to the minimum of 1
        $this->assertEquals(1, $result->sum_rim); // Should default to minimum 1 rim
    }

    /**
     * Test the registration of PCHT labels.
     * This test checks if the labels are correctly registered for a given PO number.
     */
    public function test_check_pcht_label_registration(): void
    {
        $po_number = 4000000003;

        // Create test data for label registration
        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri'
        ]);

        // Check the count of registered labels
        $result = $this->service->cekLabelPchtTerdaftar($po_number)->count();

        // Assert that the label count matches the expected value
        $this->assertEquals(1, $result);
    }

    /**
     * Test that rim numbers are ordered correctly.
     * This test verifies that the rim numbers are returned in the correct order.
     */
    public function test_get_rim_numbers_ordered_correctly(): void
    {
        $po_number = 4000000004;

        // Create test data in random order
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

        // Retrieve the list of rim numbers
        $results = $this->service->getListNomorRimPcht($po_number)->get();

        // Assert that the rim numbers are ordered correctly
        $this->assertEquals(1, $results[0]->no_rim);
        $this->assertEquals(2, $results[1]->no_rim);
    }

    /**
     * Test that a PO is marked as finished when all labels are processed.
     * This test checks the completion status of a PO based on label processing.
     */
    public function test_po_is_finished_when_all_labels_processed(): void
    {
        $po_number = 4000000005;

        // Create a label entry for the PO
        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'TestUser1'
        ]);

        // Assert that the PO is marked as finished
        $this->assertTrue($this->service->isPoFinished($po_number));
    }

    /**
     * Test that a PO is not finished with unprocessed labels.
     * This test verifies that the completion status is correctly determined.
     */
    public function test_po_is_not_finished_with_unprocessed_labels(): void
    {
        $po_number = 4000000006;

        // Create a label entry with unprocessed status
        GeneratedLabels::create([
            'no_po_generated_products' => $po_number,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => null
        ]);

        // Assert that the PO is not marked as finished
        $this->assertFalse($this->service->isPoFinished($po_number));
    }
}
