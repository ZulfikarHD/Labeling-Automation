<?php

namespace App\Http\Controllers;

use App\Models\DataInschiet;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Models\Specification;
use App\Models\Workstations;
use App\Services\PrintLabelService;
use App\Services\ProductionOrderService;
use App\Traits\UpdateStatusProgress;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk mengelola Production Order (PO)
 *
 * Bertanggung jawab untuk:
 * - Menampilkan daftar PO
 * - Membuat PO baru
 * - Mengedit PO
 * - Menghapus PO
 * - Mengupdate status PO
 *
 * @package App\Http\Controllers
 */
class ProductionOrderController extends Controller
{
    use UpdateStatusProgress;

    protected $productionOrderService;
    protected $printLabelService;

    public function __construct(ProductionOrderService $productionOrderService, PrintLabelService $printLabelService)
    {
        $this->productionOrderService = $productionOrderService;
        $this->printLabelService = $printLabelService;
    }

    /**
     * Menampilkan daftar Production Order berdasarkan tim
     *
     * @param string $team ID tim
     * @return \Inertia\Response
     */
    public function index(String $team)
    {
        return Inertia::render('ProductionOrderList', [
            'products' => $this->data_products($team, request()->merge(['search' => ''])),
            'listTeam' => Workstations::select('id', 'workstation')->get(),
            'crntTeam' => $team,
        ]);
    }

    /**
     * Mengambil data PO dengan filter dan pagination
     *
     * @param string $team ID tim
     * @param Request $request Request dengan parameter search, sort_field, sort_direction
     * @return mixed Data PO yang sudah difilter dan dipaginasi
     */
    public function data_products(String $team, Request $request)
    {
        $search = $request->search;
        $sort_field = $request->sort_field ?? 'status';
        $sort_direction = $request->sort_direction ?? 'asc';

        // Validasi field sorting yang diizinkan
        $allowed_sort_fields = ['no_po', 'no_obc', 'created_at', 'updated_at', 'status'];
        if (!in_array($sort_field, $allowed_sort_fields)) {
            $sort_field = 'created_at';
        }

        $team_filter = $team == 0 ? "!=" : "=";
        $data_product = GeneratedProducts::query()
            ->with('workstation')
            ->where('assigned_team', $team_filter, $team)
            ->where(function ($query) use ($search) {
                $query->where('no_po', 'LIKE', "%{$search}%")
                    ->orWhere('no_obc', 'LIKE', "%{$search}%");
            })
            ->orderBy($sort_field, $sort_direction)
            ->paginate(10)
            ->through(function ($q) {
                return [
                    'id'    => $q->id,
                    'no_po' => $q->no_po,
                    'no_obc' => $q->no_obc,
                    'workstation' => $q->workstation->workstation,
                    'created_at'  => $q->created_at,
                    'updated_at'  => $q->updated_at,
                    'status'    => $q->status,
                    'assigned_team' => $q->assigned_team,
                ];
            });

        return $data_product == null ? '' : $data_product;
    }

    /**
     * Menampilkan form edit PO
     *
     * @param int $po Nomor PO
     * @return \Inertia\Response
     */
    public function edit(Int $po)
    {
        $dataPo = GeneratedProducts::where('no_po', $po)->firstOrFail();
        $specPo = Specification::where('no_po',$po)
            ->select('seri','type','rencet','mesin')
            ->firstOrFail();

        $dataLabel  = GeneratedLabels::where('no_po_generated_products', $po)
            ->select('id', 'no_po_generated_products', 'no_rim', 'np_users', 'np_user_p2', 'potongan', 'start', 'finish')
            ->get();

        $inschiet   = DataInschiet::where('no_po', $po)->first()->inschiet ?? 0;

        return Inertia::render('EditProductionOrder/Index', [
            'dataPo'    => $dataPo,
            'specPo'    => $specPo,
            'dataLabel' => $dataLabel,
            'teamList'  => Workstations::listWorkstation(),
            'inschiet'  => $inschiet,
        ]);
    }

    /**
     * Menampilkan detail status verifikasi PO
     *
     * @param string $team ID tim
     * @param string $po Nomor PO
     * @return \Inertia\Response
     */
    public function show(String $team, string $po)
    {
        $assigned_team = GeneratedProducts::where('no_po', $po)->firstOrFail()->assigned_team;
        return Inertia::render('MonitoringProduksi/StatusVerifikasiTeam', [
            'dataRim'   => GeneratedLabels::where('no_po_generated_products', $po)->get(),
            'spec'  => GeneratedProducts::where('no_po', $po)->firstOrFail(),
            'team'  => Workstations::where('id', $assigned_team)->firstOrFail(),
        ]);
    }

    /**
     * Menyimpan PO baru
     *
     * @param Request $request Data PO baru
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->productionOrderService->registerProductionOrder($request);
            $this->printLabelService->populateLabelForRegisteredPo($request);
        } catch (\Exception $exeption) {
            return response()->json(['error' =>  $exeption->getMessage()], 422);
        }

        return redirect()->back();
    }

    /**
     * Mengupdate data PO yang sudah ada
     *
     * @param Request $request Data PO yang diupdate
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            // Ambil semua label untuk PO ini
            $labels = GeneratedLabels::where('no_po_generated_products', $request->no_po)
                        ->orderBy('no_rim')
                        ->get();

            // Update nomor rim untuk setiap label
            $newRimNumber = $request->start_rim;
            $previousRim = null;

            foreach ($labels as $label) {
                // Increment rim number jika berbeda dengan sebelumnya
                if ($previousRim !== null && $label->no_rim != $previousRim) {
                    $newRimNumber++;
                }

                // Skip update untuk rim inschiet (999)
                GeneratedLabels::where('id', $label->id)
                    ->update([
                        'no_rim' => $label->no_rim == 999 ? 999 : $newRimNumber
                    ]);

                $previousRim = $label->no_rim;
            }

            // Update data PO
            GeneratedProducts::where('no_po', $request->no_po)->update([
                'type'  => $request->type,
                'sum_rim'   => $request->sum_rim,
                'start_rim' => $request->start_rim,
                'end_rim'   => $request->end_rim,
                'assigned_team' => $request->assigned_team,
                'status'    => $request->status,
            ]);

            DB::commit();

            // Ambil data label yang sudah diupdate
            $updatedLabels = GeneratedLabels::where('no_po_generated_products', $request->no_po)
                ->select('id', 'no_po_generated_products', 'no_rim', 'np_users', 'np_user_p2', 'potongan', 'start', 'finish')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui',
                'labels' => $updatedLabels
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus PO beserta data terkait
     *
     * @param string $po Nomor PO yang akan dihapus
     */
    public function destroy(string $po)
    {
        GeneratedLabels::where('no_po_generated_products', $po)->delete();
        GeneratedProducts::where('no_po', $po)->delete();
        DataInschiet::where('no_po', $po)->delete();
    }

    /**
     * Mengupdate status PO menjadi selesai
     *
     * @param string $po Nomor PO
     */
    public function updateStatusFinish(String $po)
    {
        $this->updateProgress($po, 2);
    }
}
