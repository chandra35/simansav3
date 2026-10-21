<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SuratKeterangan;
use App\Models\SuratMasuk;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Tata Usaha & Surat
 *
 * APIs for querying active certificates (Surat Keterangan) and incoming letters (Surat Masuk).
 */
class SuratController extends Controller
{
    use ApiResponse;

    /**
     * List Surat Keterangan
     *
     * Retrieve list of generated student/GTK certificates with status filter and search query.
     */
    public function suratKeteranganIndex(Request $request): JsonResponse
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = SuratKeterangan::with(['siswa:id,nama_lengkap,nisn', 'gtk:id,nama_lengkap,nip'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('perihal', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn ($s) => $s->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $paginator = $query->paginate($perPage);

        $waLines = [];
        $waLines[] = "📜 *DAFTAR SURAT KETERANGAN*";
        if ($status) {
            $waLines[] = "📌 Status: *" . ucfirst($status) . "*";
        }
        $waLines[] = "📊 Total: *{$paginator->total()}* surat";
        $waLines[] = "--------------------------------";

        if ($paginator->isEmpty()) {
            $waLines[] = "ℹ️ _Tidak ada data surat keterangan yang cocok._";
        } else {
            foreach ($paginator->items() as $idx => $s) {
                $num = ($paginator->currentPage() - 1) * $paginator->perPage() + $idx + 1;
                $penerima = $s->siswa?->nama_lengkap ?? ($s->gtk?->nama_lengkap ?? '-');
                $nomor = $s->nomor_surat ?: '(Draft/Belum ada nomor)';
                $stat = ucfirst($s->status);
                $waLines[] = "{$num}. *{$nomor}*";
                $waLines[] = "   • Pemohon : {$penerima}";
                $waLines[] = "   • Keperluan: {$s->keperluan}";
                $waLines[] = "   • Status  : {$stat}";
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->paginatedResponse($paginator, 'Daftar surat keterangan berhasil dimuat.', implode("\n", $waLines));
    }

    /**
     * Detail Surat Keterangan
     *
     * Retrieve single surat keterangan record by UUID or nomor surat.
     */
    public function suratKeteranganShow(string $identifier): JsonResponse
    {
        $surat = SuratKeterangan::with(['siswa.kelasSaatIni', 'gtk', 'template'])
            ->where('id', $identifier)
            ->orWhere('nomor_surat', $identifier)
            ->first();

        if (!$surat) {
            return $this->errorResponse('Surat keterangan tidak ditemukan.', 404);
        }

        $penerima = $surat->siswa?->nama_lengkap ?? ($surat->gtk?->nama_lengkap ?? '-');
        $kls = $surat->siswa?->kelasSaatIni?->nama ?? '-';

        $waLines = [
            "📜 *DETAIL SURAT KETERANGAN*",
            "--------------------------------",
            "• *Nomor Surat* : " . ($surat->nomor_surat ?: '-'),
            "• *Perihal*     : {$surat->perihal}",
            "• *Keperluan*   : {$surat->keperluan}",
            "• *Pemohon*     : {$penerima} (Kelas: {$kls})",
            "• *Tanggal*     : " . ($surat->tanggal_surat ? Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : '-'),
            "• *Status*      : " . ucfirst($surat->status),
            "• *Penandatangan*: {$surat->jabatan_penandatangan}",
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ];

        return $this->successResponse($surat, 'Detail surat keterangan berhasil dimuat.', 200, [], implode("\n", $waLines));
    }

    /**
     * Recent Surat Masuk Overview
     *
     * List recent incoming letters and disposition status for administration tracking.
     */
    public function suratMasukIndex(Request $request): JsonResponse
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = min((int) $request->input('per_page', 10), 50);

        $query = SuratMasuk::query()->orderBy('diterima_tanggal', 'desc')->orderBy('nomor_urut', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_berkas', 'like', "%{$search}%")
                  ->orWhere('asal', 'like', "%{$search}%")
                  ->orWhere('isi_ringkasan', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        $waLines = [];
        $waLines[] = "📬 *SURAT MASUK TERBARU*";
        $waLines[] = "--------------------------------";

        if ($paginator->isEmpty()) {
            $waLines[] = "ℹ️ _Tidak ada data surat masuk._";
        } else {
            foreach ($paginator->items() as $idx => $sm) {
                $num = ($paginator->currentPage() - 1) * $paginator->perPage() + $idx + 1;
                $tgl = $sm->diterima_tanggal ? Carbon::parse($sm->diterima_tanggal)->format('d/m/Y') : '-';
                $waLines[] = "{$num}. *[{$tgl}]* Dari: {$sm->asal}";
                $waLines[] = "   • Hal: {$sm->isi_ringkasan}";
                $waLines[] = "   • Status: {$sm->status_label}";
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->paginatedResponse($paginator, 'Daftar surat masuk berhasil dimuat.', implode("\n", $waLines));
    }
}
