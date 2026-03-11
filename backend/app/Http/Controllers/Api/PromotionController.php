<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;

class PromotionController extends Controller
{
    /**
     * Deskripsi singkat:
     * Mengambil daftar seluruh promosi (Promotion) harga keanggotaan/langganan gym yang tersedia.
     *
     * Parameter:
     * (Tidak ada parameter)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi array daftar promosi yang aktif.
     *
     * Contoh penggunaan:
     * GET /api/promotions
     */
    public function index()
    {
        return response()->json([
            'data' => Promotion::all()
        ]);
    }
}
