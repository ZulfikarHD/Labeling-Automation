<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Specification;
use App\Models\GeneratedProducts;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegisterNomorPoTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Create a test specification with default values.
     *
     * @param int $noPo The production order number to be used.
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
     * Test that a new production order number can be registered successfully.
     *
     * @return void
     */
    public function test_can_register_new_production_order_number(): void
    {
        $requestData = [
            'po'    => "4000000001",
            'obc'   => 'TST010110',
            'produk'    => 'PCHT',
            'jml_rim'   => 5,
            'start_rim' => 1,
            'end_rim'   => 5,
            'jml_lembar'=> 2500,
            'team' => 1
        ];

        // Send a POST request to register a new production order
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Assert that the production order was successfully created in the database
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $requestData['po'],
            'no_obc' => $requestData['obc'],
            'type' => 'PCHT',
            'assigned_team' => $requestData['team']
        ]);
    }

    /**
     * Test that attempting to register a duplicate production order fails.
     *
     * @return void
     */
    public function test_cannot_register_duplicate_production_order(): void
    {
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

        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'team' => 1,
            'produk' => 'PCHT',
            'jml_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
        ];

        // Attempt to register the duplicate production order
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Assert that the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);
    }

    /**
     * Test that required fields are validated when registering a production order.
     *
     * @return void
     */
    public function test_validates_required_fields(): void
    {
        // Attempt to register a production order with missing fields
        $response = $this->postJson('/api/order-besar/register-no-po', []);

        // Assert that the response status is 422 and the correct validation errors are returned
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['po', 'obc', 'jml_lembar', 'team']);
    }

    /**
     * Test that the production order number format is validated correctly.
     *
     * @return void
     */
    public function test_validates_production_order_number_format(): void
    {
        $requestData = [
            'po' => 123, // Invalid format
            'obc' => 'TST010110',
            'jml_lembar' => 2500,
            'team' => 1
        ];

        // Attempt to register the production order with an invalid PO format
        $response = $this->postJson('/api/order-besar/register-no-po', $requestData);

        // Assert that the response status is 422 and the correct validation error is returned
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['po']);
    }
}
