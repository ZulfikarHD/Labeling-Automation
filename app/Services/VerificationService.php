<?php

namespace App\Services;

use App\Models\GeneratedLabels;
use App\Models\GeneratedLabelsMmea;
use Carbon\Carbon;
use App\Http\Jobs\Divnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola verifikasi produksi
 *
 * Service ini bertanggung jawab untuk:
 * - Mengambil data verifikasi harian per tim/pegawai
 * - Menghitung total verifikasi (base + inschiet)
 * - Menghitung jumlah PO yang dikerjakan
 */
class VerificationService
{
    use Divnum;

    /**
     * Mengambil data verifikasi harian untuk sebuah tim
     */
    public function getDailyVerification(Carbon $date, string $team): Collection
    {
        // Query data verifikasi dasar
        $verificationData = DB::table('generated_labels as gl')
            ->select([
                'gl.np_users as pegawai',
                DB::raw('COUNT(DISTINCT gl.no_po_generated_products) as jumlah_po'),
                DB::raw('COUNT(CASE WHEN gl.no_rim != 999 THEN 1 END) * 500 as verifikasi_base')
            ])
            ->whereDate('gl.start', $date)
            ->where('gl.np_users', 'not like', '%mesin%')
            ->when($team !== '0', function($query) use ($team) {
                return $query->where('gl.workstation', $team);
            })
            ->groupBy('gl.np_users')
            ->get();

        if ($verificationData->isEmpty()) {
            return collect();
        }

        // Check if no_rim=999 exists in verification data
        $hasNoRim999 = DB::table('generated_labels as gl')
            ->whereDate('gl.start', $date)
            ->where('gl.no_rim', 999)
            ->when($team !== '0', function($query) use ($team) {
                return $query->where('gl.workstation', $team);
            })
            ->exists();

        $inschietData = collect();

        // Only execute inschiet query if no_rim=999 exists
        if ($hasNoRim999) {
            $inschietData = DB::table('data_inschiet')
                ->select([
                    DB::raw('np_kiri as pegawai'),
                    DB::raw('SUM(inschiet) as kiri_total')
                ])
                ->whereIn('np_kiri', $verificationData->pluck('pegawai'))
                ->whereBetween('updated_at', [
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay()
                ])
                ->groupBy('np_kiri')
                ->unionAll(
                    DB::table('data_inschiet')
                        ->select([
                            DB::raw('np_kanan as pegawai'),
                            DB::raw('SUM(inschiet) as kanan_total')
                        ])
                        ->whereIn('np_kanan', $verificationData->pluck('pegawai'))
                        ->whereBetween('updated_at', [
                            $date->copy()->startOfDay(),
                            $date->copy()->endOfDay()
                        ])
                        ->groupBy('np_kanan')
                )
                ->get()
                ->groupBy('pegawai');
        }

        // Menggabungkan dan menghitung hasil akhir
        return $verificationData->map(function($item) use ($inschietData) {
            $inschiet = $inschietData->get($item->pegawai, collect());
            $totalInschiet = $inschiet->sum('kiri_total') + $inschiet->sum('kanan_total');

            return [
                'pegawai' => $item->pegawai,
                'verifikasi' => $item->verifikasi_base + round($totalInschiet / 2),
                'jumlah_po' => $item->jumlah_po
            ];
        })->sortByDesc('verifikasi')->values();
    }

    public function getDataVerifMmea($date)
    {
        $data_prod_mmea = GeneratedLabelsMmea::query()
                            ->whereDate('created_at',$date)
                            ->get();

        $prod_p1 = $this->produksiMmeaP1($data_prod_mmea);
        $prod_p2 = $this->produksiMmeaP2($data_prod_mmea);
                    
        $np_prod = array_merge(array_keys($prod_p1),array_keys($prod_p2));

        $subtotal_prod = [];

        // Produksi Mmea Dalam Satuan Lembar
        foreach ($np_prod as $np) {
            $subtotal_prod[$np]['np'] = $np;

            $prod_verif_p1 = $data_prod_mmea->where('periksa1',$np)->sum('lbr_kemas');
            $prod_verif_p2 = $data_prod_mmea->where('periksa2',$np)->sum('lbr_kemas');
            $subtotal_prod[$np]['lbr'] = $prod_verif_p1 + $prod_verif_p2;

            $prod_verif_p1_rim = $this->divnum($prod_verif_p1,300);
            $prod_verif_p2_rim = $this->divnum($prod_verif_p2,300);
            $subtotal_prod[$np]['rim'] =  round($prod_verif_p1_rim + $prod_verif_p2_rim,0,PHP_ROUND_HALF_UP);

            $prod_po_p1 = $data_prod_mmea->where('periksa1',$np)->unique('nomor_po')->count('no_po');
            $prod_po_p2 = $data_prod_mmea->where('periksa2',$np)->unique('nomor_po')->count('no_po');
            $subtotal_prod[$np]['po'] = $prod_po_p1 + $prod_po_p2;
        }

        // if($subtotal_prod !== null) {
            usort($subtotal_prod, fn($a,$b) => $b['lbr'] <=> $a['lbr']);
        // }

        return $subtotal_prod;
    }

    private function produksiMmeaP1($data_produksi) : array
    {
        return $data_produksi->groupBy('periksa1')
                    ->map(function($q){
                        return $q->sum('lbr_kemas');
                    })->toArray();
    }

    private function produksiMmeaP2($data_produksi) : array
    {
        return $data_produksi->groupBy('periksa2')
                    ->map(function($q){
                        return $q->sum('lbr_kemas');
                    })->toArray();
    }
    
}
