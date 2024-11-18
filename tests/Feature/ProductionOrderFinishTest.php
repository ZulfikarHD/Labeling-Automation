<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductionOrderFinishTest extends TestCase
{
    use DatabaseTransactions;

    private function createTestData($noPo)
    {
        // Create a test production order
        GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 2,
            'start_rim' => 1,
            'end_rim' => 2,
            'status' => 0,
            'assigned_team' => 1
        ]);

        // Create test labels for the production order
        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => $noPo,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => 'I444',
            'workstation' => 1
        ]);
    }

    public function test_production_order_can_be_marked_as_finished(): void
    {
        $noPo = 4000000001;
        $this->createTestData($noPo);

        $response = $this->put("/api/production-order-finish/{$noPo}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('generated_products', [
            'no_po' => $noPo,
            'status' => 2 // Verify status is updated to finished (1)
        ]);
    }

    public function test_returns_404_if_production_order_to_be_marked_have_nonexistent_po(): void
    {
        $nonexistentPo = 9999999999;

        $response = $this->put("/api/production-order-finish/{$nonexistentPo}");

        $response->assertStatus(404);
    }
}
