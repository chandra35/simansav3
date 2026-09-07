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
        $verifiedPairs = RelasiKeluarga::where('jenis_relasi', 'saudara_kandung')->where('status', 'terverifikasi')->get()
            ->mapWithKeys(fn ($row) => [collect([$row->siswa_id, $row->siswa_terkait_id])->sort()->implode(':') => true]);
        $families = $ortu->filter(fn ($o) => isset($siswa[$o->siswa_id]))->groupBy(function ($o) {
            $parents = collect([$o->nik_ayah, $o->nik_ibu])->filter()->sort()->values();
            return $o->no_kk ?: ($parents->count() ? 'ortu:' . $parents->implode('|') : null);
        })->filter(fn ($items, $key) => $key && $items->count() > 1);
        $isAlumni = fn ($student) => str_contains(strtolower((string) ($student->status_siswa ?? '')), 'alumni')
            || str_contains(strtolower((string) ($student->status_siswa ?? '')), 'lulus');
        $siblings = collect();
        foreach ($families as $family) {
            foreach ($family->groupBy(fn ($o) => $isAlumni($siswa[$o->siswa_id]) ? 'alumni' : 'aktif') as $status => $members) {
                $students = $members->map(fn ($o) => $siswa[$o->siswa_id])->unique('id')->values();
                if ($students->count() < 2) continue;
                $pairs = [];
                foreach ($students as $i => $left) foreach ($students->slice($i + 1) as $right) {
                    $pairKey = collect([$left->id, $right->id])->sort()->implode(':');
                    if (!$verifiedPairs->has($pairKey)) $pairs[] = [$left, $right];
                }
                if ($pairs) $siblings->push(['students' => $students, 'status' => $status, 'bukti' => ['Data orang tua identik'], 'pairs' => $pairs]);
            }
        }

        $gtks = Gtk::whereNull('deleted_at')->whereNotNull('nik')->get()->keyBy('nik');
        $gtkCandidates = [];
        foreach ($ortu as $data) foreach (['nik_ayah' => 'Ayah', 'nik_ibu' => 'Ibu'] as $field => $peran) {
            if (filled($data->{$field}) && isset($gtks[$data->{$field}]) && isset($siswa[$data->siswa_id])) {
                $gtkCandidates[$data->siswa_id] ??= ['siswa' => $siswa[$data->siswa_id], 'orang_tua' => []];
                $gtkCandidates[$data->siswa_id]['orang_tua'][] = ['gtk' => $gtks[$data->{$field}], 'peran' => $peran];
            }
        }

        $siblingRows = $siblings->flatMap(fn ($group) => collect($group['pairs'])->map(fn ($pair) => ['left' => $pair[0], 'right' => $pair[1], 'status' => $group['status'], 'bukti' => $group['bukti']]))->values();
        return view('admin.relasi-keluarga.index', [
            'siblings' => $siblingRows, 'gtkCandidates' => collect($gtkCandidates)->values(),
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
        return back()->with('toastr_success', 'Relasi keluarga berhasil diverifikasi.');
    }
}
