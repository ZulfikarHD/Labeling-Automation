<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Workstations;
use App\Models\GeneratedProducts;
use App\Models\GeneratedLabels;
use App\Traits\UpdateStatusProgress;
use App\Models\DataInschiet;

class PrintLabelController extends Controller
{
    use UpdateStatusProgress;

    public function index(String $workstation, String $id)
    {
        $product = GeneratedProducts::find($id);

        return Inertia::render('NonPerekat/NonPersonal/Verifikator/GenerateLabel', [
            'product'   => $product,
            'listTeam'  => Workstations::select('id', 'workstation')->get(),
            'crntTeam'  => $workstation,
            'noRim'     => $this->fetchNoRim($product->no_po)['noRim'],
            'potongan'  => $this->fetchNoRim($product->no_po)['potongan'],
            'date'      => now(),
        ]);
    }

    private function countNullNp(String $po)
    {
        return GeneratedLabels::where('no_po_generated_products', $po)
                              ->whereNull('np_users')
                              ->count();
    }

    public function store(Request $request)
    {
        $rfid = strtoupper($request->rfid);
        $cnt_prog = GeneratedLabels::where('np_users', $rfid)
                                   ->whereNull('finish')
                                   ->count();

        if ($cnt_prog > 0) {
            GeneratedLabels::where('np_users', $rfid)
                           ->whereNull('finish')
                           ->update([
                               'np_users' => $rfid,
                               'finish'   => now()
                           ]);
        }

        GeneratedLabels::where('no_po_generated_products', $request->po)
                       ->where('potongan', $request->lbr_ptg)
                       ->where('no_rim', $request->no_rim)
                       ->update([
                           'np_users'    => $rfid,
                           'start'       => now(),
                           'finish'      => null,
                           'workstation' => $request->team
                       ]);

        GeneratedProducts::where('no_po', $request->po)
                         ->update(['assigned_team' => $request->team]);

        $this->updateProgress($request->po, $this->countNullNp($request->po) > 0 ? 1 : 2);

        if ($request->no_rim === 999) {
            $field = $request->lbr_ptg === "Kiri" ? 'np_kiri' : 'np_kanan';
            DataInschiet::where('no_po', $request->po)
                        ->update([$field => $rfid]);
        }

        return redirect()->back();
    }

    public function edit(Request $request)
    {
        return GeneratedLabels::where('no_po_generated_products', $request->po)
                              ->where('potongan', $request->dataRim)
                              ->select('no_rim', 'np_users', 'potongan', 'start', 'finish')
                              ->get();
    }

    public function update(Request $request)
    {
        $npPetugas = strtoupper($request->npPetugas);

        GeneratedLabels::where('no_po_generated_products', $request->po)
                       ->where('potongan', $request->dataRim)
                       ->where('no_rim', $request->noRim)
                       ->update([
                           'np_users'    => $npPetugas,
                           'workstation' => $request->team,
                           'start'       => now()
                       ]);

        if ($request->noRim === 999) {
            $field = $request->dataRim === "Kiri" ? 'np_kiri' : 'np_kanan';
            DataInschiet::where('no_po', $request->po)
                        ->update([$field => $npPetugas]);
        }

        return redirect()->back();
    }

    public function delete(String $id)
    {
        // Implement label deletion
    }

    private function fetchNoRim(String $po)
    {
        $nullKiri = GeneratedLabels::where('no_po_generated_products', $po)
                                   ->where('potongan', 'Kiri')
                                   ->whereNull('np_users')
                                   ->get();

        $nullKanan = GeneratedLabels::where('no_po_generated_products', $po)
                                    ->where('potongan', 'Kanan')
                                    ->whereNull('np_users')
                                    ->get();

        $lastKiri = GeneratedLabels::where('no_po_generated_products', $po)
                                   ->where('potongan', 'Kiri')
                                   ->whereNull('np_users')
                                   ->first();

        $lastKanan = GeneratedLabels::where('no_po_generated_products', $po)
                                    ->where('potongan', 'Kanan')
                                    ->whereNull('np_users')
                                    ->first();

        $lastRimKiri = $lastKiri ? $lastKiri->no_rim : GeneratedLabels::where('no_po_generated_products', $po)
                                                                      ->where('potongan', 'Kiri')
                                                                      ->latest('no_rim')
                                                                      ->first()
                                                                      ->no_rim;

        $lastRimKanan = $lastKanan ? $lastKanan->no_rim : GeneratedLabels::where('no_po_generated_products', $po)
                                                                         ->where('potongan', 'Kanan')
                                                                         ->latest('no_rim')
                                                                         ->first()
                                                                         ->no_rim;

        $rim999Kiri = GeneratedLabels::where('no_po_generated_products', $po)
                                     ->where('potongan', 'Kiri')
                                     ->where('no_rim', 999)
                                     ->whereNull('np_users')
                                     ->first();

        $rim999Kanan = GeneratedLabels::where('no_po_generated_products', $po)
                                      ->where('potongan', 'Kanan')
                                      ->where('no_rim', 999)
                                      ->whereNull('np_users')
                                      ->first();

        if ($rim999Kiri) {
            return [
                'noRim'    => 999,
                'potongan' => 'Kiri'
            ];
        }

        if ($rim999Kanan) {
            return [
                'noRim'    => 999,
                'potongan' => 'Kanan'
            ];
        }

        if ($nullKiri->isEmpty() && $nullKanan->isEmpty()) {
            return [
                'noRim'    => 0,
                'potongan' => 'Finished'
            ];
        }

        if ($nullKiri->isNotEmpty()) {
            if ($lastRimKiri == $lastRimKanan) {
                return [
                    'noRim'    => $lastKiri->no_rim,
                    'potongan' => 'Kiri'
                ];
            }

            return [
                'noRim'    => $lastRimKiri > $lastRimKanan ? $lastKanan->no_rim : $lastKiri->no_rim,
                'potongan' => $lastRimKiri > $lastRimKanan ? 'Kanan' : 'Kiri'
            ];
        }

        return [
            'noRim'    => $lastRimKanan == $lastRimKiri ? $lastKanan->no_rim : ($lastRimKanan > $lastRimKiri ? $lastKiri->no_rim : $lastKanan->no_rim),
            'potongan' => $lastRimKanan == $lastRimKiri ? 'Kanan' : ($lastRimKanan > $lastRimKiri ? 'Kiri' : 'Kanan')
        ];
    }
}
