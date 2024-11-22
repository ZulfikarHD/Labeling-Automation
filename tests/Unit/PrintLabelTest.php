<?php

namespace Tests\Unit;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use App\Services\PrintLabelService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test case untuk fitur pencetakan label produksi
 *
 * Class ini menguji fungsionalitas terkait:
 * - Pembuatan dan populasi data label
 * - Penanganan data inschiet (sisa produksi)
 * - Manajemen sesi pengguna
 * - Validasi dan penyimpanan data label
 *
 * Related files:
 * - Services:
 *   - App\Services\PrintLabelService
 * - Models:
 *   - App\Models\GeneratedLabels
 *   - App\Models\DataInschiet
 */
class PrintLabelTest extends TestCase
{
    use DatabaseTransactions;

    private PrintLabelService $printLabelService;

    /**
     * Setup test environment
     *
     * Mempersiapkan:
     * - Inisialisasi service untuk pengujian
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->printLabelService = new PrintLabelService();
    }

    /**
     * Test populasi label untuk PO yang terdaftar
     *
     * Memverifikasi bahwa:
     * - Label dibuat dengan benar di database
     * - Data inschiet tersimpan sesuai perhitungan
     *
     * Steps:
     * 1. Siapkan data PO test
     * 2. Jalankan proses populasi label
     * 3. Verifikasi data tersimpan
     *
     * @return void
     */
    public function test_populate_label_for_registered_po(): void
    {
        // Arrange
        $dataPo = [
            'po' => 3999999999,
            'jml_lembar' => 2500,
            'periksa1' => 'I444',
            'periksa2' => 'I406',
            'team' => 1,
            'start_rim' => 1,
            'end_rim' => 3
        ];

        // Act
        $this->printLabelService->populateLabelForRegisteredPo($dataPo);

        // Assert
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => 3999999999,
            'no_rim' => 1,
            'np_users' => 'I444',
            'np_user_p2' => 'I406',
            'workstation' => 1
        ]);

        $this->assertDatabaseHas('data_inschiet', [
            'no_po' => 3999999999,
            'inschiet' => 500, // 2500 % 1000
        ]);
    }

    /**
     * Test pembuatan label produksi
     *
     * Memverifikasi bahwa:
     * - Label dapat dibuat dengan atribut yang benar
     * - Data tersimpan sesuai parameter yang diberikan
     *
     * Steps:
     * 1. Siapkan data label
     * 2. Buat label baru
     * 3. Verifikasi penyimpanan
     *
     * @return void
     */
    public function test_create_label(): void
    {
        // Arrange
        $noPo = 3999999999;
        $rimNumber = 1;
        $potongan = 'Kiri';
        $periksa1 = 'I444';
        $periksa2 = 'I406';
        $team = 1;

        // Act
        $this->printLabelService->createLabel(
            $noPo,
            $rimNumber,
            $potongan,
            $periksa1,
            $periksa2,
            $team
        );

        // Assert
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'no_rim' => $rimNumber,
            'potongan' => $potongan,
            'np_users' => 'I444',
            'np_user_p2' => 'I406',
            'workstation' => $team
        ]);
    }

    /**
     * Test penyimpanan data inschiet dengan sisa
     *
     * Memverifikasi bahwa:
     * - Data inschiet tersimpan saat ada sisa lembar
     * - Label inschiet dibuat untuk kedua potongan
     *
     * Steps:
     * 1. Siapkan data dengan sisa lembar
     * 2. Proses penyimpanan inschiet
     * 3. Verifikasi data tersimpan
     *
     * @return void
     */
    public function test_insert_inschiet_with_remainder(): void
    {
        // Arrange
        $noPo = 3999999999;
        $lembar = 1500; // Will result in 500 inschiet
        $periksa1 = 'I444';
        $periksa2 = 'I406';
        $workstation = 1;

        // Act
        $this->printLabelService->insertInschiet(
            $noPo,
            $lembar,
            $periksa1,
            $periksa2,
            $workstation
        );

        // Assert
        $this->assertDatabaseHas('data_inschiet', [
            'no_po' => $noPo,
            'inschiet' => 500,
            'np_kiri' => 'I444',
            'np_kanan' => 'I444'
        ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'no_rim' => 999,
            'potongan' => 'Kiri'
        ]);

        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => $noPo,
            'no_rim' => 999,
            'potongan' => 'Kanan'
        ]);
    }

    /**
     * Test penyimpanan data inschiet tanpa sisa
     *
     * Memverifikasi bahwa:
     * - Tidak ada data inschiet saat lembar habis dibagi
     *
     * Steps:
     * 1. Siapkan data tanpa sisa lembar
     * 2. Proses penyimpanan inschiet
     * 3. Verifikasi tidak ada data tersimpan
     *
     * @return void
     */
    public function test_insert_inschiet_without_remainder(): void
    {
        // Arrange
        $noPo = 3999999999;
        $lembar = 2000; // No remainder
        $periksa1 = 'I444';
        $periksa2 = 'I406';
        $workstation = 1;

        // Act
        $this->printLabelService->insertInschiet(
            $noPo,
            $lembar,
            $periksa1,
            $periksa2,
            $workstation
        );

        // Assert
        $this->assertDatabaseMissing('data_inschiet', [
            'no_po' => $noPo
        ]);
    }

    /**
     * Test penyelesaian sesi pengguna sebelumnya
     *
     * Memverifikasi bahwa:
     * - Timestamp finish diupdate untuk sesi sebelumnya
     * - Data label teridentifikasi dengan benar
     *
     * Steps:
     * 1. Buat data sesi pengguna
     * 2. Proses penyelesaian sesi
     * 3. Verifikasi update timestamp
     *
     * @return void
     */
    public function test_finish_previous_user_session(): void
    {
        // Arrange
        GeneratedLabels::create([
            'no_po_generated_products' => 3999999999,
            'no_rim' => 1,
            'potongan' => 'Kiri',
            'np_users' => 'I444',
            'start' => now(),
            'finish' => null
        ]);

        // Act
        $this->printLabelService->finishPreviousUserSession('I444');

        // Assert
        $this->assertDatabaseHas('generated_labels', [
            'no_po_generated_products' => 3999999999,
            'np_users' => 'I444',
        ]);

        $label = GeneratedLabels::where('np_users', 'I444')->first();
        $this->assertNotNull($label->finish);
    }
}
