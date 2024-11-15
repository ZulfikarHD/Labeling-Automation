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
 * Class ini bertanggung jawab untuk mengelola pencetakan label untuk order produksi kecil,
 * termasuk registrasi order baru, menampilkan data, dan memproses pencetakan label.
 */
class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    /** @var ProductionOrderService */
    protected $productionOrderService;

    /** @var PrintLabelService */
    protected $printLabelService;

    /**
     * Constructor untuk CetakLabelController
     *
     * @param ProductionOrderService $productionOrderService Service untuk mengelola order produksi
     * @param PrintLabelService $printLabelService Service untuk mengelola pencetakan label
     */
    public function __construct(
        ProductionOrderService $productionOrderService,
        PrintLabelService $printLabelService
    ) {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Menampilkan halaman utama pencetakan label
     *
     * @param Workstations $workstations Model workstation
     * @return \Inertia\Response
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
     * Menampilkan detail spesifikasi berdasarkan nomor PO
     *
     * @param int $no_po Nomor PO yang akan ditampilkan
     * @param SpecificationService $specificationService Service untuk mengelola spesifikasi
     * @return mixed
     */
    public function show(int $no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }

    /**
     * Memproses pencetakan label berdasarkan request yang divalidasi
     *
     * @param StoreGeneratedProductsRequest $request Request tervalidasi untuk pencetakan label
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
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

            return redirect()->back();
        } catch (\Exception $exception) {
            \Log::error('Transaction failed: ' . $exception->getMessage());

            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
            ], 422);
        }
    }

    /**
     * Menghitung jumlah label yang belum memiliki NP user
     *
     * @param string $noPo Nomor PO yang akan dihitung
     * @return int Jumlah label tanpa NP user
     */
    private function countNullNp(string $noPo): int
    {
        return GeneratedLabels::query()
            ->where('no_po_generated_products', $noPo)
            ->whereNull('np_users')
            ->count();
    }
}
