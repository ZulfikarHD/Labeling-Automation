<?php

namespace App\Http\Controllers\OrderBesar;

use App\Services\SpecificationService;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGeneratedProductsRequest;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use DB;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk mengelola registrasi nomor PO (Production Order)
 *
 * Controller ini bertanggung jawab untuk:
 * - Menampilkan form registrasi PO
 * - Menyimpan data PO baru dan generate label
 * - Menampilkan detail spesifikasi PO
 *
 * Tech Stack:
 * - Laravel 10 untuk backend
 * - Inertia.js untuk SSR
 * - Vue 3 untuk frontend
 *
 * Flow Aplikasi:
 * 1. User membuka halaman registrasi PO
 * 2. User mengisi form dengan data PO dan jumlah rim
 * 3. System menyimpan PO dan generate label
 * 4. User dapat melihat detail spesifikasi PO
 */
class RegisterNomorPoController extends Controller
{
    protected $productionOrderService;
    protected $printLabelService;

    /**
     * Constructor dengan dependency injection
     *
     * Dependencies:
     * - ProductionOrderService: Service untuk manage PO
     * - PrintLabelService: Service untuk generate label
     *
     * Best Practice:
     * - Menggunakan dependency injection via constructor
     * - Single Responsibility Principle
     */
    public function __construct(ProductionOrderService $productionOrderService, PrintLabelService $printLabelService)
    {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Menampilkan halaman utama registrasi PO
     *
     * Method ini:
     * 1. Mengambil daftar workstation
     * 2. Mengambil ID workstation user yang login
     * 3. Merender view dengan data yang diperlukan
     *
     * @param Workstations $workstations Model untuk akses data workstation
     * @return \Inertia\Response Response Inertia dengan data workstation
     */
    public function index(Workstations $workstations)
    {
        return Inertia::render('OrderBesar/RegisterNomorPo', [
            'workstation' => $workstations->listWorkstation(),
            'currentTeam' => Auth::user()->workstation_id,
        ]);
    }

    /**
     * Menyimpan PO baru dan generate label
     *
     * Flow Method:
     * 1. Validasi input menggunakan form request
     * 2. Start database transaction
     * 3. Register PO baru via service
     * 4. Generate label via service
     * 5. Commit transaction jika sukses
     * 6. Rollback jika terjadi error
     *
     * @param StoreGeneratedProductsRequest $request Form request untuk validasi
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function store(StoreGeneratedProductsRequest $request)
    {
        $validatedData = $request->validated();

        try {
            \DB::transaction(function() use ($validatedData) {
                $this->productionOrderService->registerProductionOrder($validatedData);
                $this->printLabelService->populateLabelForRegisteredPo($validatedData);
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
     * Menampilkan detail spesifikasi PO
     *
     * Method ini menggunakan SpecificationService untuk:
     * - Mengambil data spesifikasi berdasarkan nomor PO
     * - Memastikan data konsisten
     * - Mengembalikan response dalam format yang sesuai
     *
     * @param string $no_po Nomor PO yang akan ditampilkan
     * @param SpecificationService $specificationService Service untuk query spesifikasi
     * @return \Illuminate\Database\Eloquent\Model Data spesifikasi PO
     */
    public function show($no_po, SpecificationService $specificationService)
    {
        return $specificationService->getSpecByNomorPo($no_po);
    }
}
