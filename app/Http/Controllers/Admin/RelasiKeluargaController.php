<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\RelasiKeluarga;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelasiKeluargaController extends Controller
{
    public function index()
    {
        $ortu = DB::table('ortu')->whereNull('deleted_at')->get();
        $siswa = Siswa::with('kelasSaatIni')->whereNull('deleted_at')->get()->keyBy('id');
        $groups = collect(['no_kk', 'nik_ayah', 'nik_ibu'])->flatMap(function ($field) use ($ortu) {
            return $ortu->filter(fn ($o) => filled($o->{$field}))->groupBy($field)
                ->filter(fn ($items) => $items->count() > 1)
                ->map(fn ($items, $value) => compact('field', 'value', 'items'));
        });

        $siblings = [];
        foreach ($groups as $group) foreach ($group['items'] as $left) foreach ($group['items'] as $right) {
            if ($left->siswa_id >= $right->siswa_id || !isset($siswa[$left->siswa_id], $siswa[$right->siswa_id])) continue;
            $key = $left->siswa_id . ':' . $right->siswa_id;
            $siblings[$key] ??= ['siswa' => $siswa[$left->siswa_id], 'terkait' => $siswa[$right->siswa_id], 'bukti' => []];
            $siblings[$key]['bukti'][] = strtoupper(str_replace('_', ' ', $group['field'])) . ' sama';
        }

        $gtks = Gtk::whereNull('deleted_at')->whereNotNull('nik')->get()->keyBy('nik');
        $gtkCandidates = [];
        foreach ($ortu as $data) foreach (['nik_ayah' => 'Ayah', 'nik_ibu' => 'Ibu'] as $field => $peran) {
            if (filled($data->{$field}) && isset($gtks[$data->{$field}]) && isset($siswa[$data->siswa_id])) {
                $gtkCandidates[$data->siswa_id . ':' . $gtks[$data->{$field}]->id] = ['siswa' => $siswa[$data->siswa_id], 'gtk' => $gtks[$data->{$field}], 'peran' => $peran];
            }
        }

        return view('admin.relasi-keluarga.index', [
            'siblings' => collect($siblings)->values(), 'gtkCandidates' => collect($gtkCandidates)->values(),
            'verified' => RelasiKeluarga::with(['siswa', 'siswaTerkait', 'gtk'])->latest()->limit(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis_relasi' => 'required|in:saudara_kandung,anak_gtk', 'siswa_id' => 'required|exists:siswa,id',
            'siswa_terkait_id' => 'nullable|required_if:jenis_relasi,saudara_kandung|exists:siswa,id',
            'gtk_id' => 'nullable|required_if:jenis_relasi,anak_gtk|exists:gtks,id', 'bukti' => 'required|array',
        ]);
        abort_if(($data['jenis_relasi'] === 'saudara_kandung') === ($data['siswa_terkait_id'] === null), 422);
        RelasiKeluarga::updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'siswa_terkait_id' => $data['siswa_terkait_id'] ?? null, 'gtk_id' => $data['gtk_id'] ?? null, 'jenis_relasi' => $data['jenis_relasi']],
            ['bukti_kecocokan' => $data['bukti'], 'status' => 'terverifikasi', 'diverifikasi_oleh' => auth()->id(), 'diverifikasi_pada' => now()]
        );
        return back()->with('success', 'Relasi keluarga berhasil diverifikasi.');
    }
}
