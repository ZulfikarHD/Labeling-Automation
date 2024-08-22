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
     * Display the main Entry PO page.
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
     * Store or create labels based on PO and the number of rims.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $sumRim = max(floor($request->jml_lembar / 1000), 1);
        $cnt_gen_po = GeneratedLabels::where('no_po_generated_products', $request->po)->count();

        DB::transaction(function () use ($request, $sumRim, $cnt_gen_po) {
            if ($cnt_gen_po === 0) {
                $this->createOrUpdateGeneratedProducts($request, $sumRim);
                $this->createOrUpdateGeneratedLabels($request, $sumRim);
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
     * Create or update generated products based on PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function createOrUpdateGeneratedProducts($request, $sumRim)
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
     * Create or update generated labels based on PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function createOrUpdateGeneratedLabels($request, $sumRim)
    {
        for ($i = 0; $i < $sumRim; $i++) {
            foreach (['Kiri', 'Kanan'] as $potongan) {
                GeneratedLabels::updateOrCreate(
                    [
                        'no_po_generated_products' => $request->po,
                        'no_rim' => $request->start_rim + $i,
                        'potongan' => $potongan
                    ]
                );
            }
        }
    }

    /**
     * Update generated products and labels based on PO.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sumRim
     * @return void
     */
    private function updateGeneratedProductsAndLabels($request, $sumRim)
    {
        $current_start_rim = GeneratedProducts::where('no_po', $request->po)->value('start_rim');

        if ($current_start_rim !== $request->start_rim) {
            $labels = GeneratedLabels::where('no_po_generated_products', $request->po)->get();
            foreach ($labels as $index => $label) {
                $label->update(['no_rim' => $request->start_rim + floor($index / 2)]);
            }

            $this->createOrUpdateGeneratedProducts($request, $sumRim);
        }
    }

    /**
     * Create inschiet labels based on PO.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function createInschietLabels($request)
    {
        foreach (['Kiri', 'Kanan'] as $potongan) {
            GeneratedLabels::updateOrCreate(
                [
                    'no_po_generated_products' => $request->po,
                    'no_rim' => 999,
                    'potongan' => $potongan
                ]
            );
        }

        DataInschiet::updateOrCreate(
            ['no_po' => $request->po],
            ['inschiet' => $request->inschiet]
        );
    }

    /**
     * Display the specified resource based on ID.
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
 * Function Descriptions:
 * --------------------------
 *
 * 1. **index()**:
 *    Displays the main Entry PO page with workstation data sorted by workstation name.
 *
 * 2. **store(Request $request)**:
 *    Stores or creates labels based on PO and the number of rims. Uses a database transaction to ensure data integrity.
 *
 * 3. **createOrUpdateGeneratedProducts($request, $sumRim)**:
 *    Creates or updates generated products based on PO.
 *
 * 4. **createOrUpdateGeneratedLabels($request, $sumRim)**:
 *    Creates or updates generated labels based on PO.
 *
 * 5. **updateGeneratedProductsAndLabels($request, $sumRim)**:
 *    Updates generated products and labels based on PO if there is a change in start_rim.
 *
 * 6. **createInschietLabels($request)**:
 *    Creates inschiet labels based on PO.
 *
 * 7. **show(string $id)**:
 *    Displays the specified resource based on PO ID.
 *
 **/
