<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\Ortu;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $students = collect();
        $gtks = collect();
        $parents = collect();

        if (mb_strlen($query) >= 2) {
            $like = '%'.addcslashes($query, '%_\\').'%';

            $students = Siswa::query()
                ->with(['kelasSaatIni', 'ortu'])
                ->where(function ($builder) use ($like) {
                    $builder->where('nama_lengkap', 'like', $like)
                        ->orWhere('nisn', 'like', $like)
                        ->orWhere('nik', 'like', $like)
                        ->orWhere('nomor_tes', 'like', $like);
                })
                ->orderBy('nama_lengkap')
                ->limit(40)
                ->get();

            $gtks = Gtk::query()
                ->where(function ($builder) use ($like) {
                    $builder->where('nama_lengkap', 'like', $like)
                        ->orWhere('nik', 'like', $like)
                        ->orWhere('nuptk', 'like', $like)
                        ->orWhere('nip', 'like', $like)
                        ->orWhere('peg_id', 'like', $like)
                        ->orWhere('kode_gtk', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->orderBy('nama_lengkap')
                ->limit(40)
                ->get();

            $parents = Ortu::query()
                ->with('siswa.kelasSaatIni')
                ->where(function ($builder) use ($like) {
                    $builder->where('nama_ayah', 'like', $like)
                        ->orWhere('nik_ayah', 'like', $like)
                        ->orWhere('nama_ibu', 'like', $like)
                        ->orWhere('nik_ibu', 'like', $like)
                        ->orWhere('no_kk', 'like', $like)
                        ->orWhere('hp_ayah', 'like', $like)
                        ->orWhere('hp_ibu', 'like', $like);
                })
                ->limit(40)
                ->get()
                ->map(function (Ortu $ortu) use ($query) {
                    $needle = Str::lower($query);
                    $matches = collect([
                        ['peran' => 'Ayah', 'nama' => $ortu->nama_ayah, 'identitas' => $ortu->nik_ayah],
                        ['peran' => 'Ibu', 'nama' => $ortu->nama_ibu, 'identitas' => $ortu->nik_ibu],
                    ])->filter(fn ($parent) => str_contains(Str::lower((string) $parent['nama']), $needle)
                        || str_contains((string) $parent['identitas'], $query));

                    return [
                        'ortu' => $ortu,
                        'matches' => $matches->values(),
                    ];
                })
                ->filter(fn ($row) => $row['matches']->isNotEmpty())
                ->values();
        }

        return view('admin.global-search.index', compact('query', 'students', 'gtks', 'parents'));
    }
}
