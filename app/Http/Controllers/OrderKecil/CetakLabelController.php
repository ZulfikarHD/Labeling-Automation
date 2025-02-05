<?php

namespace App\Http\Controllers\OrderKecil;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGeneratedProductsRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedLabels;
use App\Models\Workstations;
use App\Traits\UpdateStatusProgress;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use App\Services\SpecificationService;
use Inertia\Inertia;

/**
 * Controller untuk menangani pencetakan label order kecil
 *
 * Flow utama:
 * 1. User memilih workstation
 * 2. Input nomor PO dan data label
 * 3. System generate label untuk setiap rim
 * 4. Update status progress PO
 *
 * Dependencies:
 * - PrintLabelService: Service untuk generate dan manage label
 * - ProductionOrderService: Service untuk manage PO dan status
 * - SpecificationService: Service untuk get spec produk
 * - UpdateStatusProgress: Trait untuk update status PO
 *
 * @see PrintLabelService
 * @see ProductionOrderService
 * @see SpecificationService
 */
class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    /** @var ProductionOrderService */
    protected $productionOrderService;

    /** @var PrintLabelService */
    protected $printLabelService;

    /**
     * Constructor untuk inject dependencies
     *
     * @param ProductionOrderService $productionOrderService Service untuk manage PO
     * @param PrintLabelService $printLabelService Service untuk manage label
     */
    public function __construct(
        ProductionOrderService $productionOrderService,
        PrintLabelService $printLabelService
    ) {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Menampilkan halaman utama cetak label
     *
     * Flow:
     * 1. Get list workstation
     * 2. Get current user workstation
     * 3. Render view dengan data
     *
     * @param Workstations $workstations Model workstation
     * @return \Inertia\Response Response Inertia dengan data workstation
     */
    public function index(Workstations $workstations)
    {
        $listTeam = $workstations->listWorkstation();

        return Inertia::render('OrderKecil/CetakLabel', [
            'listTeam'    => $listTeam,
            'currentTeam' => Auth::user()->workstation_id,
        ]);
    }

    /**
     * Get detail spesifikasi produk
     *
     * Flow:
     * 1. Get spec dari service berdasar nomor PO
     * 2. Return data spec ke frontend
     *
     * @param int $no_po Nomor PO yang dicari
     * @param SpecificationService $specificationService Service untuk get spec
     * @return mixed Data spesifikasi produk
     */
    public function show(int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    /**
     * Handle request cetak label baru
     *
     * Flow:
     * 1. Validate request
     * 2. Start transaction
     * 3. Register PO baru
     * 4. Generate label untuk PO
     * 5. Update status user sebelumnya
     * 6. Update progress PO
     * 7. Commit atau rollback transaction
     *
     * @param StoreGeneratedProductsRequest $request Request tervalidasi
     * @return \Illuminate\Http\JsonResponse Response status operasi
     */
    public function cetakLabel(StoreGeneratedProductsRequest $request)
    {
        $validatedRequest = $request->validated();

        try {
            \DB::transaction(function() use ($validatedRequest) {
                $this->productionOrderService->registerProductionOrder($validatedRequest);
                $this->printLabelService->populateLabelForRegisteredPo($validatedRequest);
                $this->printLabelService->finishPreviousUserSession($validatedRequest['periksa1']);
                $this->updateProgress($validatedRequest['po'], 2);
            });

            return response()->json(['message' => 'Label berhasil dibuat'], 200);
        } catch (\Exception $exception) {
            \Log::error('Transaction failed: ' . $exception->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
            ], 422);
        }
    }

    /**
     * Helper untuk hitung label yang belum diproses
     *
     * Menghitung jumlah label yang:
     * - Belum memiliki NP user (np_users = null)
     * - Untuk PO tertentu
     *
     * @param string $noPo Nomor PO yang dihitung
     * @return int Jumlah label yang belum diproses
     */
    private function countNullNp(string $noPo): int
    {
        return GeneratedLabels::query()
            ->where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->count();
    }
}
