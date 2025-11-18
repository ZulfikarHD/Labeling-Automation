<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SpecificationService;
use App\Models\Specification;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Unit test untuk SpecificationService
 *
 * Menguji logika terkait:
 * - Pengambilan spesifikasi berdasarkan nomor PO
 * - Penanganan PO yang tidak ada
 */
class SpecificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SpecificationService $service;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SpecificationService();
    }

    /**
     * Test mendapatkan spesifikasi berdasarkan nomor PO
     */
    public function test_get_spec_by_nomor_po_returns_specification(): void
    {
        // Arrange
        $specification = Specification::create([
            'no_po' => 7000000001,
            'no_obc' => 'TST010110',
            'seri' => 1,
            'type' => 'PCHT',
            'rencet' => 38123,
            'mesin' => '1'
        ]);

        // Act
        $result = $this->service->getSpecByNomorPo(7000000001);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(7000000001, $result->no_po);
        $this->assertEquals('TST010110', $result->no_obc);
        $this->assertEquals(1, $result->seri);
        $this->assertEquals('PCHT', $result->type);
        $this->assertEquals(38123, $result->rencet);
    }

    /**
     * Test mendapatkan spesifikasi hanya mengembalikan field yang diperlukan
     */
    public function test_get_spec_by_nomor_po_returns_only_selected_fields(): void
    {
        // Arrange
        Specification::create([
            'no_po' => 7000000002,
            'no_obc' => 'TST010111',
            'seri' => 2,
            'type' => 'PCHT',
            'rencet' => 40000,
            'mesin' => '2'
        ]);

        // Act
        $result = $this->service->getSpecByNomorPo(7000000002);

        // Assert - Should only have selected fields
        $this->assertArrayHasKey('no_po', $result->toArray());
        $this->assertArrayHasKey('no_obc', $result->toArray());
        $this->assertArrayHasKey('seri', $result->toArray());
        $this->assertArrayHasKey('type', $result->toArray());
        $this->assertArrayHasKey('rencet', $result->toArray());
        // mesin should not be in the result
        $this->assertArrayNotHasKey('mesin', $result->toArray());
    }

    /**
     * Test error ketika PO tidak ditemukan
     */
    public function test_get_spec_by_nomor_po_throws_exception_when_not_found(): void
    {
        // Arrange
        $nonexistentPo = 9999999999;

        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->getSpecByNomorPo($nonexistentPo);
    }

    /**
     * Test mendapatkan spesifikasi dengan berbagai tipe produk
     */
    public function test_get_spec_by_nomor_po_works_with_different_product_types(): void
    {
        // Arrange
        $types = ['PCHT', 'MMEA', 'OTHER'];

        foreach ($types as $index => $type) {
            Specification::create([
                'no_po' => 7000000003 + $index,
                'no_obc' => 'TST01011' . (2 + $index),
                'seri' => 1,
                'type' => $type,
                'rencet' => 30000 + $index,
                'mesin' => '1'
            ]);

            // Act
            $result = $this->service->getSpecByNomorPo(7000000003 + $index);

            // Assert
            $this->assertEquals($type, $result->type);
        }
    }

    /**
     * Test mendapatkan spesifikasi dengan berbagai nomor seri
     */
    public function test_get_spec_by_nomor_po_works_with_different_series(): void
    {
        // Arrange
        for ($seri = 1; $seri <= 5; $seri++) {
            Specification::create([
                'no_po' => 7000000010 + $seri,
                'no_obc' => 'TST01011' . (10 + $seri),
                'seri' => $seri,
                'type' => 'PCHT',
                'rencet' => 30000 + $seri,
                'mesin' => '1'
            ]);

            // Act
            $result = $this->service->getSpecByNomorPo(7000000010 + $seri);

            // Assert
            $this->assertEquals($seri, $result->seri);
        }
    }
}
