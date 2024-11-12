<?php

namespace App\Http\Controllers\OrderBesar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Traits\UpdateStatusProgress;
use App\Models\DataInschiet;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use Illuminate\Support\Facades\DB;

class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    private const INSCHIET_RIM = 999;
    private const POTONGAN_KIRI = 'Kiri';
    private const POTONGAN_KANAN = 'Kanan';

    protected ProductionOrderService $productionOrderService;
    protected PrintLabelService $printLabelService;

    public function __construct(ProductionOrderService $productionOrderService, PrintLabelService $printLabelService)
    {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    public function index(string $team, string $id)
    {
        $product = GeneratedProducts::findOrFail($id);
        $noRimData = $this->fetchNoRim($product->no_po);

        return Inertia::render('OrderBesar/CetakLabel', [
            'product'   => $product,
            'listTeam'  => Workstations::select(['id', 'workstation'])->get(),
            'crntTeam'  => $team,
            'noRim'     => $noRimData['noRim'],
            'potongan'  => $noRimData['potongan'],
            'date'      => now(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->printLabelService->finishPreviousUserSession($request->periksa1);

            $this->printLabelService->createLabel(
                $request->po,
                $request->no_rim,
                $request->lbr_ptg,
                $request->periksa1,
                null,
                $request->team
            );

            $poStatus = $this->productionOrderService->isPoFinished($request->po) ? 2 : 1;
            $this->updateProgress($request->po, $poStatus);

            DB::commit();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memproses label']);
        }
    }

    public function edit(Request $request)
    {
        return GeneratedLabels::where('no_po_generated_products', $request->po)
            ->where('potongan', $request->dataRim)
            ->select(['no_rim', 'np_users', 'potongan', 'start', 'finish'])
            ->get();
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $npPetugas = strtoupper($request->npPetugas);

            GeneratedLabels::where('no_po_generated_products', $request->po)
                ->where('potongan', $request->dataRim)
                ->where('no_rim', $request->noRim)
                ->update([
                    'np_users'    => $npPetugas,
                    'workstation' => $request->team,
                    'start'       => now()
                ]);

            if ($request->noRim === self::INSCHIET_RIM) {
                $field = $request->dataRim === self::POTONGAN_KIRI ? 'np_kiri' : 'np_kanan';
                DataInschiet::where('no_po', $request->po)
                    ->update([$field => $npPetugas]);
            }

            DB::commit();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui label']);
        }
    }

    public function delete(string $id)
    {
        try {
            $label = GeneratedLabels::findOrFail($id);
            $label->delete();
            return redirect()->back()->with('success', 'Label berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus label']);
        }
    }

    private function fetchNoRim(string $po): array
    {
        $baseQuery = GeneratedLabels::where('no_po_generated_products', $po)
            ->where(function($query) {
                $query->whereNull('np_users')
                      ->orWhere('np_users', '');
            })
            ->orderBy('no_rim');

        // Check for inschiet (no_rim = 999) with null start first
        $inschietKiri = (clone $baseQuery)
            ->where('potongan', self::POTONGAN_KIRI)
            ->where('no_rim', self::INSCHIET_RIM)
            ->whereNull('start')
            ->first();

        if ($inschietKiri) {
            return ['noRim' => self::INSCHIET_RIM, 'potongan' => self::POTONGAN_KIRI];
        }

        $inschietKanan = (clone $baseQuery)
            ->where('potongan', self::POTONGAN_KANAN)
            ->where('no_rim', self::INSCHIET_RIM)
            ->whereNull('start')
            ->first();

        if ($inschietKanan) {
            return ['noRim' => self::INSCHIET_RIM, 'potongan' => self::POTONGAN_KANAN];
        }

        // Get next available rims
        $nextKiri = (clone $baseQuery)
            ->where('potongan', self::POTONGAN_KIRI)
            ->first();

        $nextKanan = (clone $baseQuery)
            ->where('potongan', self::POTONGAN_KANAN)
            ->first();

        // Handle no available rims
        if (!$nextKiri && !$nextKanan) {
            return ['noRim' => 0, 'potongan' => 'Finished'];
        }

        // Handle single side completion
        if (!$nextKiri) {
            return ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN];
        }

        if (!$nextKanan) {
            return ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI];
        }

        // Return side with lower rim number
        return $nextKiri->no_rim <= $nextKanan->no_rim
            ? ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI]
            : ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN];
    }
}
