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

class CetakLabelOrderBesar extends TestCase
{
    use DatabaseTransactions;

    private function createTestUser()
    {
        return Users::create([
            'np' => "TEST",
            'role' => 0,
            'workstation_id' => 1,
            'password'  => Hash::make('Test123'),
        ]);
    }

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

    private function createTestGeneratedProduct($noPo = 4000000001)
    {
        return GeneratedProducts::create([
            'no_po' => $noPo,
            'no_obc' => 'TST010110',
            'type' => 'PCHT',
            'sum_rim' => 5,
            'start_rim' => 1,
            'end_rim' => 5,
            'status' => 0,
            'assigned_team' => 1
        ]);
    }

    public function test_can_cetak_label_for_order_besar(): void
    {
        $user = $this->createTestUser();
        $this->createTestSpecification();
        $this->createTestGeneratedProduct();

        $requestData = [
            'po' => "4000000001",
            'obc' => 'TST010110',
            'team' => 1,
            'seri' => 1,
            'jml_rim' => 1,
            'lbr_ptg' => 'Kiri',
            'no_rim' => 1,
            'periksa1' => 'I444',
            'date' => '2024-03-20'
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/order-besar/cetak-label', $requestData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $requestData['po'],
            'no_rim' => $requestData['no_rim'],
            'potongan' => $requestData['lbr_ptg'],
            'np_users' => $requestData['periksa1']
        ]);
    }

    public function test_can_edit_label(): void
    {
        $user = $this->createTestUser();

        GeneratedLabels::create([
            'no_po_generated_products' => '4000000001',
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        $requestData = [
            'dataRim' => 'Kiri',
            'noRim' => 1,
            'po' => '4000000001',
            'team' => 1,
            'np_users' => 'I555',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/order-besar/cetak-label/edit', $requestData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'no_rim',
                    'np_users',
                    'potongan',
                    'start',
                    'finish'
                ]
            ]);
    }

    public function test_can_update_label(): void
    {
        $user = $this->createTestUser();

        GeneratedLabels::create([
            'no_po_generated_products' => '4000000001',
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'workstation' => 1
        ]);

        $requestData = [
            'dataRim' => 'Kiri',
            'noRim' => 1,
            'npPetugas' => 'I555',
            'po' => '4000000001',
            'team' => 1
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/order-besar/cetak-label/update', $requestData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $requestData['po'],
            'no_rim' => $requestData['noRim'],
            'potongan' => $requestData['dataRim'],
            'np_users' => $requestData['npPetugas']
        ]);
    }
}
