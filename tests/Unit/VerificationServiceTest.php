<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\VerificationService;
use App\Models\GeneratedLabels;
use App\Models\GeneratedLabelsMmea;
use App\Models\DataInschiet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

/**
 * Unit test untuk VerificationService
 *
 * Menguji logika terkait:
 * - Pengambilan data verifikasi harian
 * - Perhitungan verifikasi base dan inschiet
 * - Perhitungan jumlah PO
 * - Data verifikasi MMEA
 */
class VerificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private VerificationService $service;
    private Carbon $testDate;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VerificationService();
        $this->testDate = Carbon::today();
    }

    /**
     * Test mendapatkan data verifikasi harian untuk tim tertentu
     */
    public function test_get_daily_verification_returns_data_for_team(): void
    {
        // Arrange
        $teamId = 1;
        $pegawai1 = 'I444';
        $pegawai2 = 'I555';

        // Create labels for pegawai1
        GeneratedLabels::create([
            'no_po_generated_products' => 9000000001,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai1,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 9000000001,
            'no_rim' => 1,
            'potongan' => 'Kanan',
            'np_users' => $pegawai1,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        // Create labels for pegawai2
        GeneratedLabels::create([
            'no_po_generated_products' => 9000000002,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai2,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDailyVerification($this->testDate, (string)$teamId);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($pegawai1, $result[0]['pegawai']);
        $this->assertEquals(1000, $result[0]['verifikasi']); // 2 labels * 500
        $this->assertEquals(1, $result[0]['jumlah_po']);
    }

    /**
     * Test mendapatkan data verifikasi harian untuk semua tim
     */
    public function test_get_daily_verification_returns_data_for_all_teams(): void
    {
        // Arrange
        $pegawai = 'I444';

        GeneratedLabels::create([
            'no_po_generated_products' => 9000000003,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai,
            'workstation' => 1,
            'start' => $this->testDate
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 9000000004,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai,
            'workstation' => 2,
            'start' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDailyVerification($this->testDate, '0');

        // Assert
        $this->assertGreaterThanOrEqual(1, $result->count());
    }

    /**
     * Test mengembalikan empty collection ketika tidak ada data
     */
    public function test_get_daily_verification_returns_empty_when_no_data(): void
    {
        // Act
        $result = $this->service->getDailyVerification($this->testDate, '1');

        // Assert
        $this->assertTrue($result->isEmpty());
    }

    /**
     * Test menghitung verifikasi dengan inschiet
     */
    public function test_get_daily_verification_includes_inschiet_calculation(): void
    {
        // Arrange
        $teamId = 2;
        $pegawai = 'I444';

        // Create base labels
        GeneratedLabels::create([
            'no_po_generated_products' => 9000000005,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        // Create inschiet label (no_rim = 999)
        GeneratedLabels::create([
            'no_po_generated_products' => 9000000005,
            'no_rim' => 999,
            'potongan' => 'Kiri',
            'np_users' => $pegawai,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        // Create inschiet data
        DataInschiet::create([
            'no_po' => 9000000005,
            'inschiet' => 200,
            'np_kiri' => $pegawai,
            'np_kanan' => $pegawai,
            'updated_at' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDailyVerification($this->testDate, (string)$teamId);

        // Assert
        $this->assertCount(1, $result);
        // Base: 1 label * 500 = 500
        // Inschiet: 200 / 2 = 100
        // Total: 500 + 100 = 600
        $this->assertEquals(600, $result[0]['verifikasi']);
    }

    /**
     * Test menghitung jumlah PO yang berbeda
     */
    public function test_get_daily_verification_counts_distinct_pos(): void
    {
        // Arrange
        $teamId = 3;
        $pegawai = 'I444';

        // Create labels for multiple POs
        for ($po = 9000000010; $po <= 9000000012; $po++) {
            GeneratedLabels::create([
                'no_po_generated_products' => $po,
                'no_rim' => 1,
                'potongan' => 'Kiri',
                'np_users' => $pegawai,
                'workstation' => $teamId,
                'start' => $this->testDate
            ]);
        }

        // Act
        $result = $this->service->getDailyVerification($this->testDate, (string)$teamId);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals(3, $result[0]['jumlah_po']);
    }

    /**
     * Test mengabaikan label dengan np_users mengandung 'mesin'
     */
    public function test_get_daily_verification_excludes_machine_labels(): void
    {
        // Arrange
        $teamId = 4;
        $pegawai = 'I444';

        GeneratedLabels::create([
            'no_po_generated_products' => 9000000013,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => $pegawai,
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        GeneratedLabels::create([
            'no_po_generated_products' => 9000000013,
            'no_rim' => 2,
            'potongan' => 'Kiri',
            'np_users' => 'mesin1',
            'workstation' => $teamId,
            'start' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDailyVerification($this->testDate, (string)$teamId);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals(500, $result[0]['verifikasi']); // Only 1 label counted
    }

    /**
     * Test mendapatkan data verifikasi MMEA
     */
    public function test_get_data_verif_mmea_returns_correct_data(): void
    {
        // Arrange
        $pegawai1 = 'I444';
        $pegawai2 = 'I555';
        $noPo = 9000000020;

        // Create MMEA labels for P1
        GeneratedLabelsMmea::create([
            'nomor_po' => $noPo,
            'nomor_rim' => 1,
            'periksa1' => $pegawai1,
            'periksa2' => $pegawai2,
            'lbr_kemas' => 300,
            'waktu_p1' => $this->testDate,
            'waktu_p2' => $this->testDate
        ]);

        GeneratedLabelsMmea::create([
            'nomor_po' => $noPo,
            'nomor_rim' => 2,
            'periksa1' => $pegawai1,
            'periksa2' => $pegawai2,
            'lbr_kemas' => 600,
            'waktu_p1' => $this->testDate,
            'waktu_p2' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDataVerifMmea($this->testDate->format('Y-m-d'));

        // Assert
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(2, count($result));

        // Find pegawai1 in result
        $pegawai1Data = collect($result)->firstWhere('np', $pegawai1);
        $this->assertNotNull($pegawai1Data);
        $this->assertEquals(900, $pegawai1Data['lbr']); // 300 + 600
        $this->assertEquals(3, $pegawai1Data['rim']); // (300 + 600) / 300 = 3
        $this->assertEquals(1, $pegawai1Data['po']); // 1 unique PO
    }

    /**
     * Test perhitungan rim MMEA dengan pembulatan
     */
    public function test_get_data_verif_mmea_rounds_rim_correctly(): void
    {
        // Arrange
        $pegawai = 'I666';
        $noPo = 9000000021;

        // Create MMEA label with 450 lembar (1.5 rim, should round up to 2)
        GeneratedLabelsMmea::create([
            'nomor_po' => $noPo,
            'nomor_rim' => 1,
            'periksa1' => $pegawai,
            'periksa2' => $pegawai,
            'lbr_kemas' => 450,
            'waktu_p1' => $this->testDate,
            'waktu_p2' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDataVerifMmea($this->testDate->format('Y-m-d'));

        // Assert
        $pegawaiData = collect($result)->firstWhere('np', $pegawai);
        $this->assertNotNull($pegawaiData);
        // 450 / 300 = 1.5, rounded up = 2
        $this->assertEquals(2, $pegawaiData['rim']);
    }

    /**
     * Test mengurutkan data MMEA berdasarkan lembar tertinggi
     */
    public function test_get_data_verif_mmea_sorts_by_highest_lembar(): void
    {
        // Arrange
        $pegawai1 = 'I777';
        $pegawai2 = 'I888';
        $pegawai3 = 'I999';

        GeneratedLabelsMmea::create([
            'nomor_po' => 9000000022,
            'nomor_rim' => 1,
            'periksa1' => $pegawai1,
            'periksa2' => $pegawai1,
            'lbr_kemas' => 100,
            'waktu_p1' => $this->testDate
        ]);

        GeneratedLabelsMmea::create([
            'nomor_po' => 9000000023,
            'nomor_rim' => 1,
            'periksa1' => $pegawai2,
            'periksa2' => $pegawai2,
            'lbr_kemas' => 300,
            'waktu_p1' => $this->testDate
        ]);

        GeneratedLabelsMmea::create([
            'nomor_po' => 9000000024,
            'nomor_rim' => 1,
            'periksa1' => $pegawai3,
            'periksa2' => $pegawai3,
            'lbr_kemas' => 200,
            'waktu_p1' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDataVerifMmea($this->testDate->format('Y-m-d'));

        // Assert - Should be sorted by lbr descending
        $pegawai2Index = collect($result)->search(fn($item) => $item['np'] === $pegawai2);
        $pegawai3Index = collect($result)->search(fn($item) => $item['np'] === $pegawai3);
        $pegawai1Index = collect($result)->search(fn($item) => $item['np'] === $pegawai1);

        if ($pegawai2Index !== false && $pegawai3Index !== false && $pegawai1Index !== false) {
            $this->assertLessThan($pegawai3Index, $pegawai2Index);
            $this->assertLessThan($pegawai1Index, $pegawai3Index);
        }
    }

    /**
     * Test menghitung jumlah PO unik untuk MMEA
     */
    public function test_get_data_verif_mmea_counts_unique_pos(): void
    {
        // Arrange
        $pegawai = 'I000';

        // Create labels for multiple POs
        for ($po = 9000000030; $po <= 9000000032; $po++) {
            GeneratedLabelsMmea::create([
                'nomor_po' => $po,
                'nomor_rim' => 1,
                'periksa1' => $pegawai,
                'periksa2' => $pegawai,
                'lbr_kemas' => 300,
                'waktu_p1' => $this->testDate,
                'waktu_p2' => $this->testDate
            ]);
        }

        // Act
        $result = $this->service->getDataVerifMmea($this->testDate->format('Y-m-d'));

        // Assert
        $pegawaiData = collect($result)->firstWhere('np', $pegawai);
        $this->assertNotNull($pegawaiData);
        $this->assertEquals(3, $pegawaiData['po']);
    }

    /**
     * Test mengabaikan NP dengan panjang kurang dari 4 karakter
     */
    public function test_get_data_verif_mmea_excludes_short_np(): void
    {
        // Arrange
        $shortNp = 'I12'; // 3 characters
        $validNp = 'I444';

        GeneratedLabelsMmea::create([
            'nomor_po' => 9000000040,
            'nomor_rim' => 1,
            'periksa1' => $shortNp,
            'periksa2' => $validNp,
            'lbr_kemas' => 300,
            'waktu_p1' => $this->testDate
        ]);

        // Act
        $result = $this->service->getDataVerifMmea($this->testDate->format('Y-m-d'));

        // Assert
        $shortNpData = collect($result)->firstWhere('np', $shortNp);
        $this->assertNull($shortNpData); // Should be excluded

        $validNpData = collect($result)->firstWhere('np', $validNp);
        $this->assertNotNull($validNpData); // Should be included
    }
}
