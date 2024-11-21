<?php

namespace Tests\Unit;

use App\Http\Controllers\GeneratedLabelController;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class GeneratedLabelTest extends TestCase
{
    use DatabaseTransactions;

    private GeneratedLabelController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new GeneratedLabelController();
    }

    public function test_get_labels_empty_result(): void
    {
        $po = '12345';

        $response = $this->controller->getLabels($po);

        $this->assertEquals(200, $response->status());
        $this->assertEmpty($response->getData());
    }

    public function test_add_rim_with_invalid_po(): void
    {
        $request = new Request([
            'no_po' => 999999,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->controller->addRim($request);
    }

    public function test_add_rim_updates_production_order_single_side(): void
    {
        $po = GeneratedProducts::create([
            'no_po' => 3000999999,
            'no_obc' => 'TEST123456',
            'type' => 'TEST',
            'sum_rim' => 10,
            'start_rim' => 1,
            'end_rim' => 10/2,
            'assignet_team' => 1,
            'status' => 0,
        ]);


        $request = new Request([
            'no_po' => $po->no_po,
            'no_rim' => 2,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $response = $this->controller->addRim($request);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('generated_products', [
            'no_po' => $po->no_po,
            'sum_rim' => 11,
            'end_rim' => 6
        ]);
    }

    public function test_get_labels_ordered_by_rim_number(): void
    {
        $po = '12345';
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 3,
            'potongan' => 'Kiri',
            'team' => 1
        ]);
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'team' => 1
        ]);
        GeneratedLabels::create([
            'no_po_generated_products' => $po,
            'no_rim' => 2,
            'potongan' => 'Kiri',
            'team' => 1
        ]);

        $response = $this->controller->getLabels($po);
        $labels = $response->getData();

        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, $labels[0]->no_rim);
        $this->assertEquals(2, $labels[1]->no_rim);
        $this->assertEquals(3, $labels[2]->no_rim);
    }
}
