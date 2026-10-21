<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Keuangan
 *
 * APIs for student billing records, payments, and daily collection summaries.
 */
class KeuanganController extends Controller
{
    use ApiResponse;

    /**
     * Get Student Billing & Payment Status
     *
     * Check unpaid and paid bills for a student by NISN, NIS, or UUID.
     */
    public function tagihanSiswa(string $identifier): JsonResponse
    {
        $siswa = Siswa::with(['kelasSaatIni:id,nama'])
            ->where('id', $identifier)
            ->orWhere('nisn', $identifier)
            ->orWhere('nis_lokal', $identifier)
            ->first();

        if (!$siswa) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        $tagihans = Tagihan::with(['jenisPembayaran:id,nama'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTagihan = $tagihans->sum('nominal_tagihan');
        $totalTerbayar = $tagihans->sum('nominal_terbayar');
        $sisaTagihan = $totalTagihan - $totalTerbayar;

        $unpaid = $tagihans->whereIn('status', ['belum_bayar', 'cicilan']);

        $waLines = [];
        $kls = $siswa->kelasSaatIni?->nama ?? '-';
        $waLines[] = "💳 *INFORMASI TAGIHAN SISWA*";
        $waLines[] = "--------------------------------";
        $waLines[] = "• *Nama*: {$siswa->nama_lengkap}";
        $waLines[] = "• *Kelas*: {$kls}";
        $waLines[] = "• *NISN*: " . ($siswa->nisn ?: '-');
        $waLines[] = "--------------------------------";
        $waLines[] = "💰 *Total Tagihan* : Rp " . number_format($totalTagihan, 0, ',', '.');
        $waLines[] = "✅ *Sudah Dibayar* : Rp " . number_format($totalTerbayar, 0, ',', '.');
        $waLines[] = "⚠️ *Sisa Tunggakan*: Rp " . number_format($sisaTagihan, 0, ',', '.');
        $waLines[] = "--------------------------------";

        if ($unpaid->isEmpty()) {
            $waLines[] = "🎉 *Alhamdulillah, semua tagihan siswa ini LUNAS!*";
        } else {
            $waLines[] = "📋 *Rincian Belum Lunas:*";
            foreach ($unpaid as $t) {
                $jenis = $t->jenisPembayaran?->nama ?? 'Tagihan';
                $kurang = $t->nominal_tagihan - $t->nominal_terbayar;
                $waLines[] = "• {$jenis}: Rp " . number_format($kurang, 0, ',', '.') . " (" . ucfirst($t->status) . ")";
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'siswa' => [
                'id' => $siswa->id,
                'nama' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn,
                'kelas' => $kls,
            ],
            'ringkasan' => [
                'total_tagihan' => (float) $totalTagihan,
                'total_terbayar' => (float) $totalTerbayar,
                'sisa_tunggakan' => (float) $sisaTagihan,
            ],
            'tagihan' => $tagihans,
        ], 'Data tagihan siswa berhasil dimuat.', 200, [], implode("\n", $waLines));
    }

    /**
     * Daily Payment Collection Overview
     *
     * Retrieve total verified payments received on a given date.
     */
    public function dailyPayments(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        $pembayarans = Pembayaran::with(['tagihan.siswa:id,nama_lengkap,nisn', 'tagihan.jenisPembayaran:id,nama'])
            ->whereDate('tanggal_bayar', $tanggal)
            ->where('status', 'verified')
            ->get();

        $totalNominal = $pembayarans->sum('jumlah_bayar');

        $waLines = [];
        $waLines[] = "💰 *REKAP PENERIMAAN PEMBAYARAN*";
        $waLines[] = "🗓️ Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        $waLines[] = "💵 *Total Penerimaan*: Rp " . number_format($totalNominal, 0, ',', '.');
        $waLines[] = "🧾 *Jumlah Transaksi*: " . $pembayarans->count() . " transaksi";
        $waLines[] = "--------------------------------";

        if ($pembayarans->isEmpty()) {
            $waLines[] = "ℹ️ _Belum ada transaksi pembayaran terverifikasi untuk tanggal ini._";
        } else {
            foreach ($pembayarans->take(10) as $p) {
                $siswaNama = $p->tagihan?->siswa?->nama_lengkap ?? '-';
                $jenis = $p->tagihan?->jenisPembayaran?->nama ?? '-';
                $waLines[] = "• {$siswaNama} - {$jenis}: Rp " . number_format($p->jumlah_bayar, 0, ',', '.');
            }
            if ($pembayarans->count() > 10) {
                $waLines[] = "• _... dan " . ($pembayarans->count() - 10) . " transaksi lainnya._";
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'tanggal' => $tanggal,
            'total_nominal' => (float) $totalNominal,
            'total_transaksi' => $pembayarans->count(),
            'transaksi' => $pembayarans,
        ], 'Rekap penerimaan harian berhasil dimuat.', 200, [], implode("\n", $waLines));
    }
}
