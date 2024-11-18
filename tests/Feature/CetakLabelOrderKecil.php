<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Specification;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class CetakLabelOrderKecil
 *
 * This class contains feature tests for the small order label printing functionality.
 * It includes tests for creating labels and handling duplicate production order numbers.
 */
class CetakLabelOrderKecil extends TestCase
{
    use DatabaseTransactions;

    /**
     * Create a test specification for a given purchase order number.
     *
     * @param int $noPo The purchase order number to create a specification for.
     * @return Specification The created specification instance.
     */
    private function createTestSpecification($noPo = 4000000001)
    {
        return Specification::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'seri' => 1,
            'type' => 'PCHT',
            'rencet' => 38123,
            'mesin' => 1
        ]);
    }

    /**
     * Test the creation of a label for a small order.
     *
     * This test verifies that a label can be successfully created for a small order
     * and that the relevant data is stored in the database.
     *
     * @return void
     */
    public function test_can_create_label_for_small_order(): void
    {
        // Create a test user
        $user = Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password'  => Hash::make('Test123'),
        ]);

        // Create a test specification
        $this->createTestSpecification();

        // Prepare request data for label creation
        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'jml_label' => 5,
            'end_rim' => 5,
            'periksa1' => 'I444',
            'team' => 1,
            'produk'    => "PCHT",
            'jml_rim'   => 5,
            'start_rim' => 1,
            'end_rim'   => 5,
        ];

        // Send request with authentication
        $response = $this->actingAs($user)
            ->postJson('/api/order-kecil/cetak-label', $requestData);

        // Assert successful response
        $response->assertStatus(200);

        // Assert that the generated product data was stored correctly
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $requestData['po'],
            'no_obc' => $requestData['obc'],
            'assigned_team' => $requestData['team']
        ]);

        // Assert that the generated label data was stored correctly
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $requestData['po'],
            'np_users' => 'I444',
            'workstation' => $requestData['team']
        ]);
    }

    /**
     * Test handling of duplicate purchase order numbers.
     *
     * This test verifies that an error response is returned when attempting to create
     * a label with a duplicate purchase order number.
     *
     * @return void
     */
    public function test_handles_duplicate_po_number(): void
    {
        // Create a test user
        $user = Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password'  => Hash::make('Test123'),
        ]);

        // Create an existing production order
        GeneratedProducts::create([
            'no_po' => "4000000001",
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => 1
        ]);

        // Prepare request data for label creation
        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'jml_label' => 5,
            'end_rim' => 5,
            'periksa1' => 'I444',
            'team' => 1
        ];

        // Attempt to create a label with a duplicate purchase order number
        $response = $this->actingAs($user)
            ->postJson('/api/order-kecil/cetak-label', $requestData);

        // Assert error response
        $response->assertStatus(422);
    }
}
