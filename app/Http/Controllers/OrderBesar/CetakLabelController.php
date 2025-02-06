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
 * Controller untuk menangani pencetakan label order besar
 *
 * Flow utama:
 * 1. User memilih PO dan workstation
 * 2. System generate label untuk setiap rim dan potongan
 * 3. User dapat mencetak, mengedit dan menghapus label
 * 4. System update status progress PO
 *
 * Dependencies:
 * - PrintLabelService: Service untuk generate dan manage label
 * - ProductionOrderService: Service untuk manage PO dan status
 * - UpdateStatusProgress: Trait untuk update status PO
 *
 * @see PrintLabelService
 * @see ProductionOrderService
 */
class CetakLabelController extends Controller
{
    use UpdateStatusProgress;

    // Constants untuk identifikasi dan pesan
    private const INSCHIET_RIM = 999; // Rim khusus untuk inschiet
    private const POTONGAN_KIRI = 'Kiri'; // Identifier potongan kiri
    private const POTONGAN_KANAN = 'Kanan'; // Identifier potongan kanan
    private const ERROR_PROCESS = 'Terjadi kesalahan saat memproses label';
    private const ERROR_UPDATE = 'Terjadi kesalahan saat memperbarui label';
    private const ERROR_DELETE = 'Terjadi kesalahan saat menghapus label';
    private const SUCCESS_DELETE = 'Label berhasil dihapus';

    /**
     * Constructor untuk inject dependencies
     *
     * @param ProductionOrderService $productionOrderService Service untuk manage PO
     * @param PrintLabelService $printLabelService Service untuk manage label
     */
    public function __construct(
        protected ProductionOrderService $productionOrderService,
        protected PrintLabelService $printLabelService
    ) {}

    /**
     * Menampilkan halaman cetak label
     *
     * Flow:
     * 1. Fetch product berdasarkan ID
     * 2. Fetch data rim dan potongan
     * 3. Render view dengan data yang diperlukan
     *
     * @param string $team ID workstation
     * @param string $id ID product
     * @return \Inertia\Response
     */
    public function index(string $team, string $id)
    {
        $product = GeneratedProducts::findOrFail($id);
        $noRimData = $this->fetchNoRim($product->no_po);

        return Inertia::render('OrderBesar/CetakLabel/Index', [
            'product' => $product,
            'listTeam' => Workstations::select(['id', 'workstation'])->get(),
            'crntTeam' => $team,
            'noRim' => $noRimData['noRim'],
            'potongan' => $noRimData['potongan'],
            'date' => now(),
        ]);
    }

    /**
     * Menyimpan label baru
     *
     * Flow:
     * 1. Start transaction
     * 2. Selesaikan sesi user sebelumnya
     * 3. Create label baru
     * 4. Update status PO jika semua label selesai
     * 5. Commit transaction
     *
     * @param Request $request Data label baru
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            $this->printLabelService->finishPreviousUserSession($request->periksa1);

            $result = $this->printLabelService->createLabel(
                $request->po,
                $request->no_rim,
                $request->lbr_ptg,
                $request->periksa1,
                null,
                $request->team
            );

            if ($result['status'] === 'error') {
                DB::rollback();
                return response()->json($result, 422);
            }

            $poStatus = $this->productionOrderService->isPoFinished($request->po) ? 2 : 1;
            $this->updateProgress($request->po, $poStatus);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'poStatus' => $poStatus,
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => self::ERROR_PROCESS]);
        }
    }

    /**
     * Mengambil data label untuk diedit
     *
     * @param Request $request Query params untuk filter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function show(Request $request)
    {
        return GeneratedLabels::query()
            ->where('no_po_generated_products', $request->po)
            ->where('potongan', $request->dataRim)
            ->select(['no_rim', 'np_users', 'potongan', 'start', 'finish'])
            ->get();
    }

    /**
     * Update label yang sudah ada
     *
     * Flow:
     * 1. Start transaction
     * 2. Update data label
     * 3. Update data inschiet jika rim adalah inschiet
     * 4. Commit transaction
     *
     * @param Request $request Data update label
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            $npPetugas = strtoupper($request->npPetugas);
            $this->updateGeneratedLabel($request, $npPetugas);

            if ($request->noRim === self::INSCHIET_RIM) {
                $this->updateInschietData($request, $npPetugas);
            }

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => self::ERROR_UPDATE]);
        }
    }

    /**
     * Menghapus label
     *
     * @param string $id ID label yang akan dihapus
     * @return RedirectResponse
     */
    public function delete(string $id): RedirectResponse
    {
        try {
            GeneratedLabels::findOrFail($id)->delete();
            return redirect()->back()->with('success', self::SUCCESS_DELETE);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => self::ERROR_DELETE]);
        }
    }

    /**
     * Mengambil rim berikutnya yang tersedia
     *
     * Flow:
     * 1. Cek rim inschiet terlebih dahulu
     * 2. Jika tidak ada, cek rim reguler
     * 3. Return rim yang tersedia berikutnya
     *
     * @param string $po Nomor PO
     * @return array Data rim dan potongan
     */
    private function fetchNoRim(string $po): array
    {
        $baseQuery = $this->getBaseQuery($po);

        $inschietResult = $this->checkInschietRims($baseQuery);
        if ($inschietResult) return $inschietResult;

        $nextKiri = $this->getNextRim($baseQuery, self::POTONGAN_KIRI);
        $nextKanan = $this->getNextRim($baseQuery, self::POTONGAN_KANAN);

        return $this->determineNextRim($nextKiri, $nextKanan);
    }

    /**
     * Membuat base query untuk fetch rim
     *
     * @param string $po Nomor PO
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBaseQuery(string $po)
    {
        return GeneratedLabels::where('no_po_generated_products', $po)
            ->where(fn($query) => $query->whereNull('np_users')->orWhere('np_users', ''))
            ->orderBy('no_rim');
    }

    /**
     * Cek ketersediaan rim inschiet
     *
     * @param $baseQuery Query builder
     * @return array|null Data rim inschiet atau null
     */
    private function checkInschietRims($baseQuery): ?array
    {
        foreach ([self::POTONGAN_KIRI, self::POTONGAN_KANAN] as $potongan) {
            $inschiet = (clone $baseQuery)
                ->where('potongan', $potongan)
                ->where('no_rim', self::INSCHIET_RIM)
                ->whereNull('start')
                ->first();

            if ($inschiet) {
                return ['noRim' => self::INSCHIET_RIM, 'potongan' => $potongan];
            }
        }
        return null;
    }

    /**
     * Mengambil rim berikutnya berdasarkan potongan
     *
     * @param $baseQuery Query builder
     * @param string $potongan Tipe potongan
     * @return mixed
     */
    private function getNextRim($baseQuery, string $potongan)
    {
        return (clone $baseQuery)
            ->where('potongan', $potongan)
            ->first();
    }

    /**
     * Menentukan rim berikutnya berdasarkan data kiri dan kanan
     *
     * @param $nextKiri Data rim kiri
     * @param $nextKanan Data rim kanan
     * @return array Data rim terpilih
     */
    private function determineNextRim($nextKiri, $nextKanan): array
    {
        if (!$nextKiri && !$nextKanan) {
            return ['noRim' => 0, 'potongan' => 'Finished'];
        }

        if (!$nextKiri) {
            return ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN];
        }

        if (!$nextKanan) {
            return ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI];
        }

        return $nextKiri->no_rim <= $nextKanan->no_rim
            ? ['noRim' => $nextKiri->no_rim, 'potongan' => self::POTONGAN_KIRI]
            : ['noRim' => $nextKanan->no_rim, 'potongan' => self::POTONGAN_KANAN];
    }

    /**
     * Update data label yang di-generate
     *
     * @param Request $request Data update
     * @param string $npPetugas NIP petugas
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
            ]);
    }

    /**
     * Update data inschiet
     *
     * @param Request $request Data update
     * @param string $npPetugas NIP petugas
     */
    private function updateInschietData(Request $request, string $npPetugas): void
    {
        $field = $request->dataRim === self::POTONGAN_KIRI ? 'np_kiri' : 'np_kanan';
        DataInschiet::where('no_po', $request->po)
            ->update([$field => $npPetugas]);
    }

    /**
     * Mengambil data untuk API endpoint
     *
     * @param string $team ID workstation
     * @param string $id ID product
     * @return JsonResponse
     */
    public function getData(string $team, string $id)
    {
        try {
            $product = GeneratedProducts::findOrFail($id);
            $noRimData = $this->fetchNoRim($product->no_po);

            return response()->json([
                'product' => $product,
                'noRim' => $noRimData['noRim'],
                'potongan' => $noRimData['potongan'],
                'printData' => GeneratedLabels::query()
                    ->where('no_po_generated_products', $product->no_po)
                    ->where('potongan', request('dataRim', 'Kiri'))
                    ->select(['no_rim', 'np_users', 'potongan', 'start', 'finish'])
                    ->get()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }
}
