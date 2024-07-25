<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;
use App\Models\Specification;
use App\Models\DataInschiet;
use DB;

class EntryPoController extends Controller
{
    /**
     * Menampilkan halaman utama Entry PO.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('NonPerekat/NonPersonal/Pic/EntryPo', [
            'workstation' => Workstations::orderBy('workstation')->select('id', 'workstation')->get(),
        ]);
    }

    /**
     * Menyimpan atau membuat label berdasarkan PO dan jumlah rimnya.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $sumRim = $request->jml_lembar > 0 ? floor($request->jml_lembar / 1000) : 0;
        $cnt_gen_po = GeneratedLabels::where('no_po_generated_products', $request->po)->count();

        DB::transaction(function () use ($request, $sumRim, $cnt_gen_po) {
            if ($cnt_gen_po === 0) {
                $this->createGeneratedProducts($request, $sumRim);
                $this->createGeneratedLabels($request, $sumRim);
            } else {
                $this->updateGeneratedProductsAndLabels($request, $sumRim);
            }

            if ($request->inschiet > 0) {
                $this->createInschietLabels($request);
            }
        });

        return redirect()->back();
    }

    /**
     * Membuat atau memperbarui data produk yang dihasilkan berdasarkan PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function createGeneratedProducts($request, $sumRim)
    {
        GeneratedProducts::updateOrCreate(
            ['no_po' => $request->po],
            [
                'no_obc' => $request->obc,
                'type' => $request->produk,
                'sum_rim' => $sumRim,
                'start_rim' => $request->start_rim,
                'end_rim' => $request->end_rim,
                'status' => 0,
                'assigned_team' => $request->team,
            ]
        );
    }

    /**
     * Membuat atau memperbarui label yang dihasilkan berdasarkan PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function createGeneratedLabels($request, $sumRim)
    {
        for ($i = 0; $i < $sumRim; $i++) {
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim' => $request->start_rim + $i,
                    'potongan' => "Kiri"
                ]
            );

            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim' => $request->start_rim + $i,
                    'potongan' => "Kanan"
                ]
            );
        }
    }

    /**
     * Memperbarui data produk dan label yang dihasilkan berdasarkan PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function updateGeneratedProductsAndLabels($request, $sumRim)
    {
        $current_start_rim = GeneratedProducts::where('no_po', $request->po)->value('start_rim');

        if ($current_start_rim !== $request->start_rim) {
            for ($i = 0; $i < ($sumRim * 2); $i++) {
                $current_id = GeneratedLabels::where('no_po_generated_products', $request->po)->value('id');
                GeneratedLabels::where('id', $current_id + $i)
                    ->update(['no_rim' => $i === 0 ? $request->start_rim : $request->start_rim + floor($i / 2)]);
            }

            $this->createGeneratedProducts($request, $sumRim);
        }
    }

    /**
     * Membuat label inschiet berdasarkan PO.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function createInschietLabels($request)
    {
        GeneratedLabels::updateOrCreate(
            [
                'no_po_generated_products' => $request->po,
                'no_rim' => 999,
                'potongan' => "Kiri"
            ]
        );

        GeneratedLabels::updateOrCreate(
            [
                'no_po_generated_products' => $request->po,
                'no_rim' => 999,
                'potongan' => "Kanan"
            ]
        );

        DataInschiet::updateOrCreate(
            ['no_po' => $request->po],
            ['inschiet' => $request->inschiet]
        );
    }

    /**
     * Menampilkan resource yang ditentukan berdasarkan ID.
     *
     * @param string $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function show(string $id)
    {
        return Specification::where('no_po', $id)
            ->select('no_po', 'no_obc', 'seri', 'type', 'rencet')
            ->firstOrFail();
    }
}

/*
 * Penjelasan Fungsi:
 * --------------------------
 *
 * 1. **index()**:
 *    Menampilkan halaman utama Entry PO dengan data workstation yang diurutkan berdasarkan nama workstation.
 *
 * 2. **store(Request $request)**:
 *    Menyimpan atau membuat label berdasarkan PO dan jumlah rimnya. Menggunakan transaksi database untuk memastikan integritas data.
 *
 * 3. **createGeneratedProducts($request, $sumRim)**:
 *       Membuat atau memperbarui data produk yang dihasilkan berdasarkan PO.
 *
 * 4. **createGeneratedLabels($request, $sumRim)**:
 *      Membuat atau memperbarui label yang dihasilkan berdasarkan PO.
 *
 * 5. **updateGeneratedProductsAndLabels($request, $sumRim)**:
 *      Memperbarui data produk dan label yang dihasilkan berdasarkan PO jika ada perubahan pada start_rim.
 *
 * 6. **createInschietLabels($request)**:
 *      Membuat label inschiet berdasarkan PO.
 *
 * 7. **show(string $id)**:
 *      Menampilkan resource yang ditentukan berdasarkan ID PO.
 *
 **/
