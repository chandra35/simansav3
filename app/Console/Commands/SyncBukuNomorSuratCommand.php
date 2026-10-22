<?php

namespace App\Console\Commands;

use App\Models\BukuNomorSurat;
use App\Models\MutasiSiswa;
use App\Models\SuratKeterangan;
use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Services\SuratNumberService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncBukuNomorSuratCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surat:sync-buku-agenda {--dry-run : Menampilkan rencana sinkronisasi tanpa menyimpan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data surat keluar eksisting (Surat Keterangan & Mutasi) ke Buku Agenda Nomor Surat';

    /**
     * Execute the console command.
     */
    public function handle(SuratNumberService $service)
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Menjalankan dalam mode [DRY-RUN] - Data tidak akan disimpan.');
        }

        $this->info('Memulai sinkronisasi data surat ke Buku Agenda...');

        $syncedSk = 0;
        $syncedMutasi = 0;
        $highestNomor = [];

        // 1. Sinkronisasi Surat Keterangan
        $suratKeterangans = SuratKeterangan::with(['siswa', 'template', 'penandatangan'])->get();
        foreach ($suratKeterangans as $sk) {
            if (empty($sk->nomor_surat)) {
                continue;
            }

            $exists = BukuNomorSurat::where('nomor_surat', $sk->nomor_surat)
                ->orWhere(function ($q) use ($sk) {
                    $q->where('suratable_type', SuratKeterangan::class)
                      ->where('suratable_id', $sk->id);
                })->first();

            if ($exists) {
                // Pastikan keterkaitan polimorfik terpasang
                if (empty($exists->suratable_id) && !$dryRun) {
                    $exists->update([
                        'suratable_type' => SuratKeterangan::class,
                        'suratable_id' => $sk->id,
                    ]);
                }
                continue;
            }

            $parsed = $this->parseNomorSurat($sk->nomor_surat, $sk->tanggal_surat, $sk->template?->kode_klasifikasi);
            $config = $service->findConfig($parsed['kode_klasifikasi']);

            $perihal = $sk->perihal ?: 'Surat Keterangan Siswa';
            if ($sk->siswa) {
                $perihal .= ' a.n ' . $sk->siswa->nama_lengkap;
            }
            $tujuan = $sk->siswa?->nama_lengkap ?: 'Siswa Bersangkutan';

            $tahun = $parsed['tahun'];
            $nomorUrut = $parsed['nomor_urut'];

            $highestNomor[$tahun] = max($highestNomor[$tahun] ?? 0, $nomorUrut);

            $this->line("→ [Surat Keterangan] Menambahkan: {$sk->nomor_surat} ({$perihal})");

            if (!$dryRun) {
                BukuNomorSurat::create([
                    'id' => Str::uuid(),
                    'surat_nomor_config_id' => $config?->id,
                    'tahun' => $tahun,
                    'nomor_urut' => $nomorUrut,
                    'nomor_surat' => $sk->nomor_surat,
                    'kode_klasifikasi' => $parsed['kode_klasifikasi'],
                    'tanggal_surat' => $sk->tanggal_surat ? Carbon::parse($sk->tanggal_surat) : now(),
                    'perihal' => $perihal,
                    'tujuan' => $tujuan,
                    'modul_asal' => 'surat_keterangan',
                    'suratable_type' => SuratKeterangan::class,
                    'suratable_id' => $sk->id,
                    'penandatangan_id' => $sk->gtk_id,
                    'nama_penandatangan' => $sk->penandatangan?->nama_lengkap ?? 'Kepala Madrasah',
                    'jabatan_penandatangan' => 'Kepala Madrasah',
                    'status' => 'aktif',
                    'created_by' => $sk->created_by,
                ]);
            }
            $syncedSk++;
        }

        // 2. Sinkronisasi Mutasi Siswa Keluar
        $mutasis = MutasiSiswa::with('siswa')->whereNotNull('nomor_surat_mutasi')->get();
        foreach ($mutasis as $mutasi) {
            $nomor = $mutasi->nomor_surat_mutasi;
            if (empty($nomor)) {
                continue;
            }

            $exists = BukuNomorSurat::where('nomor_surat', $nomor)
                ->orWhere(function ($q) use ($mutasi) {
                    $q->where('suratable_type', MutasiSiswa::class)
                      ->where('suratable_id', $mutasi->id);
                })->first();

            if ($exists) {
                if (empty($exists->suratable_id) && !$dryRun) {
                    $exists->update([
                        'suratable_type' => MutasiSiswa::class,
                        'suratable_id' => $mutasi->id,
                    ]);
                }
                continue;
            }

            $parsed = $this->parseNomorSurat($nomor, $mutasi->tanggal_mutasi, 'PP.00.6');
            $config = $service->findConfig($parsed['kode_klasifikasi']);

            $namaSiswa = $mutasi->siswa?->nama_lengkap ?: 'Siswa';
            $perihal = 'Surat Keterangan Mutasi ' . ucfirst($mutasi->jenis_mutasi) . ' Siswa: ' . $namaSiswa;
            $tujuan = $mutasi->jenis_mutasi === 'keluar' && !empty($mutasi->sekolah_tujuan)
                ? "{$namaSiswa} (ke {$mutasi->sekolah_tujuan})"
                : $namaSiswa;

            $tahun = $parsed['tahun'];
            $nomorUrut = $parsed['nomor_urut'];
            $highestNomor[$tahun] = max($highestNomor[$tahun] ?? 0, $nomorUrut);

            $this->line("→ [Mutasi Siswa] Menambahkan: {$nomor} ({$perihal})");

            if (!$dryRun) {
                BukuNomorSurat::create([
                    'id' => Str::uuid(),
                    'surat_nomor_config_id' => $config?->id,
                    'tahun' => $tahun,
                    'nomor_urut' => $nomorUrut,
                    'nomor_surat' => $nomor,
                    'kode_klasifikasi' => $parsed['kode_klasifikasi'],
                    'tanggal_surat' => $mutasi->tanggal_mutasi ? Carbon::parse($mutasi->tanggal_mutasi) : now(),
                    'perihal' => $perihal,
                    'tujuan' => $tujuan,
                    'modul_asal' => 'mutasi_siswa',
                    'suratable_type' => MutasiSiswa::class,
                    'suratable_id' => $mutasi->id,
                    'nama_penandatangan' => 'Kepala Madrasah',
                    'jabatan_penandatangan' => 'Kepala Madrasah',
                    'status' => 'aktif',
                    'created_by' => $mutasi->created_by,
                ]);
            }
            $syncedMutasi++;
        }

        // 3. Update counter jika ada nomor yang lebih tinggi
        if (!$dryRun) {
            foreach ($highestNomor as $thn => $maxNum) {
                $counter = SuratNomorCounter::firstOrCreate(
                    ['tahun' => $thn],
                    ['id' => Str::uuid(), 'nomor_terakhir' => 0]
                );

                if ($maxNum > $counter->nomor_terakhir) {
                    $counter->update(['nomor_terakhir' => $maxNum]);
                    $this->info("Counter tahun {$thn} dinaikkan ke {$maxNum}");
                }
            }
        }

        $this->info("Sinkronisasi selesai! Surat Keterangan: {$syncedSk}, Mutasi Siswa: {$syncedMutasi}");

        return Command::SUCCESS;
    }

    /**
     * Parse nomor surat Kemenag format B-001/Ma.08.01/PP.00.6/10/2026
     */
    protected function parseNomorSurat(string $nomorSurat, $tanggal = null, ?string $fallbackKode = 'PP.00.6'): array
    {
        $nomorUrut = 1;
        $tahun = $tanggal ? (int) Carbon::parse($tanggal)->format('Y') : (int) date('Y');
        $kodeKlasifikasi = $fallbackKode ?: 'PP.00.6';

        // Pola standar Kemenag: B-XXX/.../KODE/BB/YYYY
        if (preg_match('/^B-(\d+)\/[^\/]+\/([^\/]+)\/[^\/]+\/(\d{4})/i', $nomorSurat, $matches)) {
            $nomorUrut = (int) $matches[1];
            $kodeKlasifikasi = $matches[2];
            $tahun = (int) $matches[3];
        } elseif (preg_match('/B-(\d+)/i', $nomorSurat, $matches)) {
            $nomorUrut = (int) $matches[1];
        }

        return [
            'nomor_urut' => $nomorUrut,
            'kode_klasifikasi' => $kodeKlasifikasi,
            'tahun' => $tahun,
        ];
    }
}
