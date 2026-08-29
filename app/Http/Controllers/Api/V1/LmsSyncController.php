<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\Siswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsSyncController extends Controller
{
    public function students(Request $request): JsonResponse
    {
        $rows = Siswa::query()->where('status_siswa', 'aktif')->select(['id', 'user_id', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'updated_at'])->orderBy('id')->paginate($request->integer('per_page', 100));
        return response()->json($rows);
    }

    public function teachers(Request $request): JsonResponse
    {
        $rows = Gtk::query()->where('status_aktif', true)->select(['id', 'user_id', 'nama_lengkap', 'nip', 'nik', 'email', 'jenis_ptk', 'updated_at'])->orderBy('id')->paginate($request->integer('per_page', 100));
        return response()->json($rows);
    }
}
