<?php
namespace App\Services;

use App\Models\Workstations;
use App\Models\GeneratedLabels;
use App\Models\GeneratedProducts;

class TeamVerifikasiService
{
    /**
     * Mengambil produk dan label berdasarkan ID tim
     *
     * @param string $teamId ID tim yang ingin dicari
     * @return array|null Mengembalikan array berisi produk, label, dan tim jika ditemukan, atau null jika tidak ada produk
     */
    public function getProductAndLabelsByTeam(string $teamId)
    {
        // Mencari produk yang ditugaskan ke tim tertentu dengan status aktif
        $product = GeneratedProducts::where('assigned_team', $teamId)
                                    ->where('status', 1)
                                    ->first();

        // Jika produk tidak ditemukan, kembalikan null
        if (!$product) {
            return null;
        }

        // Mengambil semua label yang terkait dengan produk berdasarkan nomor PO
        $labels = GeneratedLabels::where('no_po_generated_products', $product->no_po)->get();

        // Mengembalikan array yang berisi produk, label, dan tim yang terkait
        return [
            'product' => $product,
            'labels' => $labels,
            'team' => Workstations::where('id', $product->assigned_team)->firstOrFail(),
        ];
    }

    /**
     * Mengambil semua workstation yang tersedia
     *
     * @return \Illuminate\Database\Eloquent\Collection Koleksi semua workstation
     */
    public function getAllWorkstations()
    {
        return Workstations::all();
    }
}
