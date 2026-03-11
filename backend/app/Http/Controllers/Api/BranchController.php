<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;

class BranchController extends Controller
{
    /**
     * Deskripsi singkat:
     * Mengambil daftar seluruh cabang (Branch) dari gym Pipitnesan.
     *
     * Parameter:
     * (Tidak ada parameter)
     *
     * Return value:
     * @return \Illuminate\Http\JsonResponse Mengembalikan response JSON berisi array daftar cabang.
     *
     * Contoh penggunaan:
     * GET /api/branches
     */
    public function index()
    {
        return response()->json([
            'data' => Branch::all()
        ]);
    }
}
