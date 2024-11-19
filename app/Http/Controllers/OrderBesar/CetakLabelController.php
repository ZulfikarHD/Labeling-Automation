<?php

namespace App\Http\Controllers\OrderBesar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{
    Workstations,
    GeneratedProducts,
    GeneratedLabels,
    DataInschiet
};
use App\Traits\UpdateStatusProgress;
use App\Services\{
    PrintLabelService,
    ProductionOrderService
};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Class CetakLabelController
 * Handles the printing of labels for large orders.
 */
class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    private const INSCHIET_RIM = 999; // Constant for inschiet rim identifier
    private const POTONGAN_KIRI = 'Kiri'; // Constant for left cut
    private const POTONGAN_KANAN = 'Kanan'; // Constant for right cut
    private const ERROR_PROCESS = 'Terjadi kesalahan saat memproses label'; // Error message for processing
    private const ERROR_UPDATE = 'Terjadi kesalahan saat memperbarui label'; // Error message for updating
    private const ERROR_DELETE = 'Terjadi kesalahan saat menghapus label'; // Error message for deleting
    private const SUCCESS_DELETE = 'Label berhasil dihapus'; // Success message for deletion

    /**
     * CetakLabelController constructor.
     *
     * @param ProductionOrderService $productionOrderService
     * @param PrintLabelService $printLabelService
     */
    public function __construct(
        protected ProductionOrderService $productionOrderService,
        protected PrintLabelService $printLabelService
    ) {}

    /**
     * Display the label printing page.
     *
     * @param string $team
     * @param string $id
     * @return \Inertia\Response
     */
    public function index(string $team, string $id)
    {
        $product = GeneratedProducts::findOrFail($id); // Fetch product by ID
        $noRimData = $this->fetchNoRim($product->no_po); // Fetch rim data

        return Inertia::render('OrderBesar/CetakLabel', [
            'product' => $product,
            'listTeam' => Workstations::select(['id', 'workstation'])->get(), // Get list of workstations
            'crntTeam' => $team,
            'noRim' => $noRimData['noRim'],
            'potongan' => $noRimData['potongan'],
            'date' => now(),
        ]);
    }

    /**
     * Store a newly created label.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction(); // Start database transaction

            $this->printLabelService->finishPreviousUserSession($request->periksa1); // Finish previous session

            $this->printLabelService->createLabel(
                $request->po,
                $request->no_rim,
                $request->lbr_ptg,
                $request->periksa1,
                null,
                $request->team
            );

            $poStatus = $this->productionOrderService->isPoFinished($request->po) ? 2 : 1; // Determine PO status
            $this->updateProgress($request->po, $poStatus); // Update progress

            DB::commit(); // Commit transaction
            return response()->json(['status' => 'success']); // Return success response

        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaction on error
            return redirect()->back()->withErrors(['error' => self::ERROR_PROCESS]); // Return error response
        }
    }

    /**
     * Edit the specified label.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function edit(Request $request)
    {
        return GeneratedLabels::query()
            ->where('no_po_generated_products', $request->po)
            ->where('potongan', $request->dataRim)
            ->select(['no_rim', 'np_users', 'potongan', 'start', 'finish'])
            ->get(); // Fetch labels based on PO and rim data
    }

    /**
     * Update the specified label.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction(); // Start database transaction

            $npPetugas = strtoupper($request->npPetugas); // Convert user name to uppercase
            $this->updateGeneratedLabel($request, $npPetugas); // Update label data

            if ($request->noRim === self::INSCHIET_RIM) {
                $this->updateInschietData($request, $npPetugas); // Update inschiet data if applicable
            }

            DB::commit(); // Commit transaction
            return response()->json(['status' => 'success']); // Return success response

        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaction on error
            return redirect()->back()->withErrors(['error' => self::ERROR_UPDATE]); // Return error response
        }
    }

    /**
     * Remove the specified label.
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function delete(string $id): RedirectResponse
    {
        try {
            GeneratedLabels::findOrFail($id)->delete(); // Find and delete the label
            return redirect()->back()->with('success', self::SUCCESS_DELETE); // Return success response
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => self::ERROR_DELETE]); // Return error response
        }
    }

    /**
     * Fetch the next available rim based on the PO.
     *
     * @param string $po
     * @return array
     */
    private function fetchNoRim(string $po): array
    {
        $baseQuery = $this->getBaseQuery($po); // Get base query for rims

        // Check inschiet rims first
        $inschietResult = $this->checkInschietRims($baseQuery);
        if ($inschietResult) return $inschietResult; // Return inschiet result if found

        // Get next available regular rims
        $nextKiri = $this->getNextRim($baseQuery, self::POTONGAN_KIRI);
        $nextKanan = $this->getNextRim($baseQuery, self::POTONGAN_KANAN);

        return $this->determineNextRim($nextKiri, $nextKanan); // Determine and return the next rim
    }

    /**
     * Get the base query for fetching rims.
     *
     * @param string $po
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBaseQuery(string $po)
    {
        return GeneratedLabels::where('no_po_generated_products', $po)
            ->where(fn($query) => $query->whereNull('np_users')->orWhere('np_users', ''))
            ->orderBy('no_rim'); // Build base query for rims
    }

    /**
     * Check for available inschiet rims.
     *
     * @param $baseQuery
     * @return array|null
     */
    private function checkInschietRims($baseQuery): ?array
    {
        foreach ([self::POTONGAN_KIRI, self::POTONGAN_KANAN] as $potongan) {
            $inschiet = (clone $baseQuery)
                ->where('potongan', $potongan)
                ->where('no_rim', self::INSCHIET_RIM)
                ->whereNull('start')
                ->first(); // Check for inschiet rims

            if ($inschiet) {
                return ['noRim' => self::INSCHIET_RIM, 'potongan' => $potongan]; // Return inschiet rim data if found
            }
        }
        return null; // Return null if no inschiet rims found
    }

    /**
     * Get the next available rim based on the potongan.
     *
     * @param $baseQuery
     * @param string $potongan
     * @return mixed
     */
    private function getNextRim($baseQuery, string $potongan)
    {
        return (clone $baseQuery)
            ->where('potongan', $potongan)
            ->first(); // Fetch the next rim based on potongan
    }

    /**
     * Determine the next available rim based on the left and right rims.
     *
     * @param $nextKiri
     * @param $nextKanan
     * @return array
     */
    private function determineNextRim($nextKiri, $nextKanan): array
    {
        if (!$nextKiri && !$nextKanan) {
            return ['noRim' => 0, 'potongan' => 'Finished']; // Return finished if no rims available
        }

        if (!$nextKiri) {
            return ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN]; // Return right rim if left is not available
        }

        if (!$nextKanan) {
            return ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI]; // Return left rim if right is not available
        }

        return $nextKiri->no_rim <= $nextKanan->no_rim
            ? ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI] // Return the next available rim
            : ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN];
    }

    /**
     * Update the generated label with new data.
     *
     * @param Request $request
     * @param string $npPetugas
     */
    private function updateGeneratedLabel(Request $request, string $npPetugas): void
    {
        GeneratedLabels::where('no_po_generated_products', $request->po)
            ->where('potongan', $request->dataRim)
            ->where('no_rim', $request->noRim)
            ->update([
                'np_users' => $npPetugas,
                'workstation' => $request->team,
                'start' => now()
            ]); // Update label data
    }

    /**
     * Update the inschiet data based on the rim type.
     *
     * @param Request $request
     * @param string $npPetugas
     */
    private function updateInschietData(Request $request, string $npPetugas): void
    {
        $field = $request->dataRim === self::POTONGAN_KIRI ? 'np_kiri' : 'np_kanan'; // Determine field based on rim type
        DataInschiet::where('no_po', $request->po)
            ->update([$field => $npPetugas]); // Update inschiet data
    }
}
