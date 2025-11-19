<?php

namespace App\Services;

use App\Models\DataInschiet;
use App\Models\GeneratedLabels;
use Illuminate\Support\Facades\DB;

class PrintLabelService
{
    private const POTONGAN_TYPES = ['Kiri', 'Kanan'];
    private const INSCHIET_RIM_NUMBER = 999;
    private const SHEETS_PER_RIM = 1000;

    public function populateLabelForRegisteredPo(array $dataPo): void
    {
        $sumRim = max(floor($dataPo['jml_lembar'] / self::SHEETS_PER_RIM), 1);

        if ($this->shouldGenerateLabels($dataPo['jml_lembar'])) {
            $this->generateLabels($dataPo, $sumRim);
        }

        $this->insertInschiet(
            $dataPo['po'],
            $dataPo['jml_lembar'],
            $dataPo['periksa1'] ?? null,
            $dataPo['periksa2'] ?? null,
            $dataPo['team']
        );
    }

    public function createLabel(int $noPo, int $rimNumber, string $potongan, ?string $periksa1, ?string $periksa2, int $team): array
    {
        if ($rimNumber === self::INSCHIET_RIM_NUMBER) {
            $this->updateInschietData($noPo, null, $periksa1, $potongan);
            $nextLabel = ['rim' => $rimNumber, 'potongan' => $potongan];
        } else {
            $nextLabel = $this->findNextAvailableLabel($noPo, $rimNumber, $potongan);
        }

        if (!$nextLabel) {
            return [
                'status' => 'error',
                'message' => 'Order sudah selesai'
            ];
        }

        GeneratedLabels::updateOrCreate(
            [
                'no_po_generated_products' => $noPo,
                'no_rim' => $nextLabel['rim'],
                'potongan' => $nextLabel['potongan'],
            ],
            [
                'np_users' => $this->formatPeriksaName($periksa1),
                'np_user_p2' => $this->formatPeriksaName($periksa2),
                'start' => $periksa1 ? now() : null,
                'finish' => $periksa2 ? now() : null,
                'workstation' => $team,
            ]
        );

        DB::table('generated_products')
                ->where('no_po', $noPo)
                ->update(['assigned_team' => $team]);

        return [
            'status' => 'success',
            'message' => 'Label berhasil dibuat',
            'data' => [
                'no_rim' => $nextLabel['rim'],
                'potongan' => $nextLabel['potongan']
            ]
        ];
    }

    public function insertInschiet(int $noPo, int $lembar, ?string $periksa1 = null, ?string $periksa2 = null, ?int $workstation = null): void
    {
        $calcInschiet = $lembar % self::SHEETS_PER_RIM;

        if ($calcInschiet <= 0) {
            return;
        }

        try {
            DB::transaction(function () use ($noPo, $calcInschiet, $periksa1, $periksa2, $workstation) {
                $formattedPeriksa1 = $this->formatPeriksaName($periksa1);
                $formattedPeriksa2 = $this->formatPeriksaName($periksa2);

                $this->updateInschietData($noPo, $calcInschiet, $formattedPeriksa1, null);
                $this->createInschietLabels($noPo, $formattedPeriksa1, $formattedPeriksa2, $workstation);
            });
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function finishPreviousUserSession(string $npPegawai): void
    {
        GeneratedLabels::where('np_users', $npPegawai)
            ->whereNull('finish')
            ->update(['finish' => now()]);
    }

    private function shouldGenerateLabels(int $totalSheets): bool
    {
        return self::SHEETS_PER_RIM > 0 ? ($totalSheets / self::SHEETS_PER_RIM) > 1 : false;
    }

    private function generateLabels(array $dataPo, int $sumRim): void
    {
        try {
            DB::transaction(function () use ($dataPo, $sumRim) {
                $periksa1 = $this->formatPeriksaName($dataPo['periksa1'] ?? null);
                $periksa2 = $this->formatPeriksaName($dataPo['periksa2'] ?? null);

                $labels = [];
                for ($i = $dataPo['start_rim'] ?? 1; $i <= $dataPo['end_rim'] ?? $sumRim; $i++) {
                    foreach (self::POTONGAN_TYPES as $potongan) {
                        $labels[] = [
                            'no_po_generated_products' => $dataPo['po'],
                            'no_rim' => $i,
                            'potongan' => $potongan,
                            'np_users' => $periksa1,
                            'np_user_p2' => $periksa2,
                            'start' => $periksa1 ? now() : null,
                            'finish' => $periksa2 ? now() : null,
                            'workstation' => $dataPo['team']
                        ];
                    }
                }

                GeneratedLabels::upsert(
                    $labels,
                    ['no_po_generated_products', 'no_rim', 'potongan'],
                    ['np_users', 'np_user_p2', 'start', 'finish', 'workstation']
                );
            });
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function findNextAvailableLabel(int $noPo, int $rimNumber, string $potongan): ?array
    {
        $currentRim = $rimNumber;

        while (true) {
            $label = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->where('potongan', $potongan)
                ->first();

            if (!$label || is_null($label->np_users)) {
                return ['rim' => $currentRim, 'potongan' => $potongan];
            }

            if ($potongan === 'Kiri') {
                $labelKanan = GeneratedLabels::where('no_po_generated_products', $noPo)
                    ->where('no_rim', $currentRim)
                    ->where('potongan', 'Kanan')
                    ->first();

                if (!$labelKanan || is_null($labelKanan->np_users)) {
                    return ['rim' => $currentRim, 'potongan' => 'Kanan'];
                }
            }

            $currentRim++;

            $nextRimExists = GeneratedLabels::where('no_po_generated_products', $noPo)
                ->where('no_rim', $currentRim)
                ->exists();

            if (!$nextRimExists) {
                return null;
            }

            $potongan = 'Kiri';
        }
    }

    private function updateInschietData(int $noPo, ?int $calcInschiet = null, ?string $periksa1, ?string $potongan = null): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);

        if ($potongan === "Kiri") {
            DataInschiet::where('no_po', $noPo)->update(['np_kiri' => $periksa1]);
        } elseif ($potongan === "Kanan") {
            DataInschiet::where('no_po', $noPo)->update(['np_kanan' => $periksa1]);
        } else {
            DataInschiet::updateOrCreate(
                ['no_po' => $noPo],
                [
                    'inschiet' => $calcInschiet,
                    'np_kiri' => $periksa1,
                    'np_kanan' => $periksa1,
                ]
            );
        }
    }

    private function createInschietLabels(int $noPo, ?string $periksa1, ?string $periksa2, ?int $workstation): void
    {
        $periksa1 = $this->formatPeriksaName($periksa1);
        $periksa2 = $this->formatPeriksaName($periksa2);

        $labels = [];
        foreach (self::POTONGAN_TYPES as $potongan) {
            $labels[] = [
                'no_po_generated_products' => $noPo,
                'no_rim' => self::INSCHIET_RIM_NUMBER,
                'potongan' => $potongan,
                'np_users' => $periksa1,
                'np_user_p2' => $periksa2,
                'start' => $periksa1 ? now() : null,
                'workstation' => $workstation
            ];
        }

        GeneratedLabels::upsert(
            $labels,
            ['no_po_generated_products', 'no_rim', 'potongan'],
            ['np_users', 'np_user_p2', 'start', 'workstation']
        );
    }

    public function getRemainingLabelCount(int $noPo): int
    {
        return GeneratedLabels::where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->count();
    }

    public function getNextAvailableLabel(int $noPo, string $orderBy = 'asc')
    {
        return GeneratedLabels::where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->orderBy('no_rim', $orderBy)
            ->first();
    }

    public function updateLabelWithInspection(
        $label,
        string $np1,
        ?string $np2,
        int $team
    ): bool {
        try {
            $label->update([
                'np_users' => strtoupper($np1),
                'np_user_p2' => $np2 ? strtoupper($np2) : null,
                'workstation' => $team,
                'start' => now(),
                'finish' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update label', [
                'label_id' => $label->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function fetchNextRim(string $po): array
    {
        $baseQuery = GeneratedLabels::where('no_po_generated_products', $po)
            ->where(fn($query) => $query->whereNull('np_users')->orWhere('np_users', ''))
            ->orderBy('no_rim');

        // Check for inschiet rims first
        $inschietResult = $this->checkInschietRims($baseQuery);
        if ($inschietResult) {
            return $inschietResult;
        }

        // Get next available rim for both Kiri and Kanan
        $nextKiri = (clone $baseQuery)->where('potongan', 'Kiri')->first();
        $nextKanan = (clone $baseQuery)->where('potongan', 'Kanan')->first();

        return $this->determineNextRim($nextKiri, $nextKanan);
    }

    public function formatPeriksaName(?string $name): ?string
    {
        return $name ? strtoupper($name) : null;
    }

    public function convertNpFormat(string $np): string
    {
        if (strlen($np) > 4) {
            $firstLetter = substr($np, 0, 1);
            $lastFour = substr($np, (strlen($np) - 4), strlen($np));
            return $firstLetter . $lastFour;
        }

        return $np;
    }

    private function checkInschietRims($baseQuery): ?array
    {
        foreach (['Kiri', 'Kanan'] as $potongan) {
            $inschiet = (clone $baseQuery)
                ->where('potongan', $potongan)
                ->where('no_rim', self::INSCHIET_RIM_NUMBER)
                ->whereNull('start')
                ->first();

            if ($inschiet) {
                return ['noRim' => self::INSCHIET_RIM_NUMBER, 'potongan' => $potongan];
            }
        }
        return null;
    }

    private function determineNextRim($nextKiri, $nextKanan): array
    {
        if (!$nextKiri && !$nextKanan) {
            return ['noRim' => 0, 'potongan' => 'Finished'];
        }

        if (!$nextKiri) {
            return ['noRim' => $nextKanan->no_rim, 'potongan' => 'Kanan'];
        }

        if (!$nextKanan) {
            return ['noRim' => $nextKiri->no_rim, 'potongan' => 'Kiri'];
        }

        return $nextKiri->no_rim <= $nextKanan->no_rim
            ? ['noRim' => $nextKiri->no_rim, 'potongan' => 'Kiri']
            : ['noRim' => $nextKanan->no_rim, 'potongan' => 'Kanan'];
    }
}
