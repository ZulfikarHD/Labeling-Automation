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

    /**
     * Menampilkan halaman untuk menghasilkan label.
     *
     * @param String $workstation ID workstation yang sedang digunakan
     * @param String $id ID produk yang akan ditampilkan
     * @return \Inertia\Response
     */
    public function index(String $workstation, String $id)
    {
        $product = GeneratedProducts::where('id',$id)->first();

        return Inertia::render('NonPerekat/NonPersonal/Verifikator/GenerateLabel',[
            'product'   => $product, // Produk yang diambil
            'listTeam'  => Workstations::select('id','workstation')->get(), // Daftar workstation
            'crntTeam'  => $workstation, // Workstation saat ini
            'noRim'     => $this->fetcNoRim($product->no_po)['noRim'], // Nomor rim yang diambil
            'potongan'  => $this->fetcNoRim($product->no_po)['potongan'], // Potongan yang diambil
            'date'      => now(), // Tanggal saat ini
        ]);
    }

    /**
     * Menghitung jumlah label yang np_users-nya null berdasarkan PO.
     *
     * @param String $po Nomor PO yang akan dihitung
     * @return int Jumlah label yang np_users-nya null
     */
    private function countNullNp(String $po)
    {
        // Menghitung jumlah label yang np_users-nya null
        return GeneratedLabels::where('no_po_generated_products',$po)->where('np_users',null)->count();
    }

    /**
     * Menyimpan data label dan memperbarui status pengguna.
     *
     * @param Request $request Data yang diterima dari permintaan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // dd($request);

        $rfid = strtoupper($request->rfid); // Mengubah RFID menjadi huruf kapital
        $cnt_prog = GeneratedLabels::where('np_users',$rfid)->where('finish',null)->count(); // Menghitung jumlah label yang belum selesai

        // Memperbarui label jika ada yang belum selesai
        if ($cnt_prog > 0) {
            GeneratedLabels::where('np_users',$rfid)
                    ->where('finish',null)
                    ->update([
                        'np_users'  => $rfid,
                        'finish'     => now()
                    ]);
        }

        // Memperbarui data label baru
        GeneratedLabels::where('no_po_generated_products',$request->po)
            ->where('potongan',$request->lbr_ptg)
            ->where('no_rim',$request->no_rim)
            ->update([
                'np_users'    => $rfid,
                'start'       => now(),
                'finish'      => null,
                'workstation' => $request->team
            ]);

        GeneratedProducts::where('no_po',$request->po)->update(['assigned_team' => $request->team]);

        // Memperbarui progres berdasarkan jumlah label yang np_users-nya null
        $this->updateProgress($request->po, $this->countNullNp($request->po) > 0 ? 1 : 2);

        // Memperbarui data di DataInschiet jika no_rim adalah 999
        if ($request->no_rim === 999) {
            $field = $request->lbr_ptg === "Kiri" ? 'np_kiri' : 'np_kanan'; // Menentukan field berdasarkan potongan
            DataInschiet::where('no_po',$request->po)->update([$field => $rfid]);
        }

        return redirect()->back(); // Kembali ke halaman sebelumnya
    }


    /**
     * Menampilkan data untuk mengedit label yang ditentukan.
     *
     * @param Request $request Data yang diterima dari permintaan
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function edit(Request $request)
    {
        // Mengambil data label berdasarkan PO dan potongan
        return GeneratedLabels::where('no_po_generated_products',$request->po)
                                     ->where('potongan',$request->dataRim)
                                     ->select('no_rim','np_users','potongan','start','finish')
                                     ->get();
    }

    /**
     * Memperbarui data label yang ditentukan.
     *
     * @param Request $request Data yang diterima dari permintaan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $npPetugas = strtoupper($request->npPetugas); // Mengubah nama petugas menjadi huruf kapital

        // Memperbarui data label
        GeneratedLabels::where('no_po_generated_products',$request->po)
                ->where('potongan',$request->dataRim)
                ->where('no_rim',$request->noRim)
                ->update([
                    'np_users'  => $npPetugas,
                    'workstation' => $request->team,
                    'start'     => now()
                ]);

        // Memperbarui data di DataInschiet jika noRim adalah 999
        if ($request->noRim === 999) {
            $field = $request->dataRim === "Kiri" ? 'np_kiri' : 'np_kanan'; // Menentukan field berdasarkan potongan
            DataInschiet::where('no_po',$request->po)->update([$field => $npPetugas]);
        }

        return redirect()->back(); // Kembali ke halaman sebelumnya
    }

    /**
     * Menghapus label berdasarkan ID.
     *
     * @param String $id ID label yang akan dihapus
     */
    public function delete(String $id)
    {
        // Implementasi penghapusan label
    }


    /**
     * Mengambil nomor rim dan potongan berdasarkan PO.
     *
     * @param String $po Nomor PO yang akan diambil
     * @return array Mengembalikan nomor rim dan potongan
     */
    private function fetcNoRim(String $po)
    {
        // Mengambil data label yang belum terisi untuk potongan Kiri dan Kanan
        $nullKiri   = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kiri')
                                ->where('np_users',null)
                                ->get();

        $nullKanan  = GeneratedLabels::where('no_po_generated_products',$po)
                                ->where('potongan','Kanan')
                                ->where('np_users',null)
                                ->get();

        // Mengambil label terakhir untuk potongan Kiri dan Kanan
        $lastKiri   = GeneratedLabels::where('no_po_generated_products',$po)
                            ->where('potongan','Kiri')
                            ->where('np_users',null)
                            ->first();

        $lastKanan   = GeneratedLabels::where('no_po_generated_products',$po)
                            ->where('potongan','Kanan')
                            ->where('np_users',null)
                            ->first();

        // Mengambil nomor rim terakhir untuk potongan Kiri dan Kanan
        $lastRimKiri  = $lastKiri !== null ? $lastKiri->no_rim : GeneratedLabels::where('no_po_generated_products',$po)->where('potongan','kiri')->latest('no_rim')->first()->no_rim;
        $lastRimKanan = $lastKanan !== null ? $lastKanan->no_rim : GeneratedLabels::where('no_po_generated_products',$po)->where('potongan','kanan')->latest('no_rim')->first()->no_rim;

        // Cek jika ada no_rim "999" dan "np_users" masih kosong, maka jadikan itu urutan pertama
        $rim999Kiri = GeneratedLabels::where('no_po_generated_products', $po)
            ->where('potongan', 'Kiri')
            ->where('no_rim', 999)
            ->where('np_users', null)
            ->first();

        $rim999Kanan = GeneratedLabels::where('no_po_generated_products', $po)
            ->where('potongan', 'Kanan')
            ->where('no_rim', 999)
            ->where('np_users', null)
            ->first();

        if ($rim999Kiri) {
            return [
                'noRim' => 999, // Nomor rim yang diambil
                'potongan' => 'Kiri' // Potongan yang diambil
            ];
        }

        if ($rim999Kanan) {
            return [
                'noRim' => 999, // Nomor rim yang diambil
                'potongan' => 'Kanan' // Potongan yang diambil
            ];
        }

        // Cek jika label bagian kiri dan kanan sudah terisi semua
        if (count($nullKiri) < 1 && count($nullKanan) < 1) {
            $noRim = 0;
            $potongan = 'Finished';
        } else {
            if (count($nullKiri) > 0) {
                if ($lastRimKiri == $lastRimKanan) {
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                } elseif ($lastRimKiri > $lastRimKanan) {
                    $noRim  = $lastKanan->no_rim;
                    $potongan = "Kanan";
                } else {
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                }
            } else {
                if ($lastRimKanan == $lastRimKiri) {
                    $noRim  = $lastKanan->no_rim;
                    $potongan  = "Kanan";
                } elseif ($lastRimKanan > $lastRimKiri) {
                    $noRim  = $lastKiri->no_rim;
                    $potongan = "Kiri";
                } else {
                    $noRim  = $lastKanan->no_rim;
                    $potongan  = "Kanan";
                }
            }
        }

        return [
            'noRim' => $noRim, // Nomor rim yang diambil
            'potongan' => $potongan // Potongan yang diambil
        ];
    }
}
