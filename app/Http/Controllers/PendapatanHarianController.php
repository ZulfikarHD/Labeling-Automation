<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GeneratedLabels;
use App\Models\DataInschiet;

class PendapatanHarianController extends Controller
{
    public function gradeHarian(Request $request)
    {
        $date = $request->date !== null ? carbon::parse($request->date)->startOfDay() : today();

        $verifPegawai = $this->verifHarian($date,$request->team);

        return $verifPegawai;
    }

    /**
     * Kalkulasi hasil verifikasi harian pegawai
     */
    /**
     * Fungsi ini digunakan untuk melakukan verifikasi harian pegawai berdasarkan tanggal dan tim.
     *
     * @param String $date Tanggal yang digunakan untuk verifikasi.
     * @param String $team ID tim yang digunakan untuk filter.
     * @return Collection Hasil verifikasi pegawai yang sudah dihitung.
     */
    public function verifHarian(String $date, String $team)
    {
        // Menentukan filter tim, jika tim adalah 0 maka filter menggunakan '!=' (tidak sama dengan),
        // jika tidak, menggunakan '=' (sama dengan).
        $teamFilter = $team == 0 ? '!=' : '=';

        // Mengambil data dari tabel GeneratedLabels berdasarkan rentang tanggal dan filter tim,
        // kemudian mengelompokkan hasil berdasarkan np_users.
        return GeneratedLabels::whereDate('start', $date)
                        ->where('workstation', $teamFilter, $team)
                        ->where('np_users', 'not like', '%mesin%')
                        ->get()
                        ->groupBy('np_users')
                        ->map(function($q, $key) {
                            // Menghitung rentang tanggal untuk inschiet berdasarkan data yang diambil.
                            $ins_date = [
                                carbon::parse($q->value('start'))->startOfDay(),
                                carbon::parse($q->value('start'))->endOfDay()
                            ];

                            // Menghitung total inschiet untuk sisi kiri berdasarkan np_kiri.
                            $sum_ins_kiri = DataInschiet::where('np_kiri', $key)
                                                    ->whereBetween('updated_at', $ins_date)
                                                    ->sum('inschiet');

                            // Menghitung total inschiet untuk sisi kanan berdasarkan np_kanan.
                            $sum_ins_kanan = DataInschiet::where('np_kanan', $key)
                                                    ->whereBetween('updated_at', $ins_date)
                                                    ->sum('inschiet');

                            // Menghitung verifikasi berdasarkan jumlah rim yang tidak sama dengan 999.
                            $calculate_verif = count($q->whereNotIn('no_rim', 999)) * 500;

                            // Menghitung total inschiet dengan membagi dan membulatkan hasilnya.
                            $sum_inschiet = round(divnum($sum_ins_kiri, 2)) + round(divnum($sum_ins_kanan, 2));

                            // Mengembalikan data pegawai dan total verifikasi.
                            return [
                                'pegawai'    => $key,
                                'verifikasi' => $calculate_verif + $sum_inschiet,
                            ];
                        })->sortByDesc('verifikasi')->values(); // Mengurutkan hasil berdasarkan verifikasi secara menurun.
    }
}
