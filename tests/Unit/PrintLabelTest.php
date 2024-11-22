<?php

namespace Tests\Unit;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use App\Services\PrintLabelService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class PrintLabelTest
 *
 * This class contains unit tests for the PrintLabelService.
 * It tests various functionalities related to label printing and data management.
 */
class PrintLabelTest extends TestCase
{
    use DatabaseTransactions;

    private PrintLabelService $printLabelService;

    /**
     * Set up the test environment.
     * Initializes the PrintLabelService before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->printLabelService = new PrintLabelService();
    }

    /**
     * Test populating labels for a registered production order.
     * This test verifies that labels are correctly populated in the database
     * based on the provided production order data.
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
     * Test creating a label for a production order.
     * This test verifies that a label can be created and stored in the database
     * with the correct attributes.
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
     * Test inserting inschiet data when there is a remainder.
     * This test verifies that the correct inschiet data is inserted into the database
     * when the number of sheets results in a remainder.
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
     * Test inserting inschiet data when there is no remainder.
     * This test verifies that no inschiet data is inserted into the database
     * when the number of sheets does not result in a remainder.
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
     * Test finishing a previous user session.
     * This test verifies that the finish timestamp is updated for the previous user session
     * in the generated labels table.
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
