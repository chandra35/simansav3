<?php

namespace App\Console\Commands;

use App\Services\AcademicAuditService;
use Illuminate\Console\Command;

class AuditAkademikCommand extends Command
{
    protected $signature = 'akademik:audit {--json : Tampilkan hasil audit dalam format JSON}';

    protected $description = 'Audit kesehatan data akademik tahunan untuk siswa, kelas, dan histori siswa_kelas';

    public function __construct(private readonly AcademicAuditService $auditService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $audit = $this->auditService->run();

        if (!($audit['ok'] ?? false)) {
            $this->error($audit['message'] ?? 'Tidak ada tahun pelajaran aktif.');
            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line(json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $this->resolveExitCode($audit);
        }

        $this->info('Audit Akademik SIMANSA');
        $this->line('Tahun pelajaran aktif: ' . $audit['tahun_pelajaran_aktif']);
        $this->newLine();

        $this->table(
            ['Check', 'Jumlah', 'Status'],
            collect($audit['checks'])->map(function (array $check) {
                return [
                    $check['label'],
                    $check['count'],
                    $check['count'] === 0 ? 'OK' : 'PERLU DICEK',
                ];
            })->all()
        );

        if (!empty($audit['samples'])) {
            $this->newLine();
            $this->warn('Contoh data yang perlu dicek:');

            foreach ($audit['samples'] as $title => $rows) {
                if (empty($rows)) {
                    continue;
                }

                $this->newLine();
                $this->line($title);
                $this->table(
                    array_keys((array) $rows[0]),
                    array_map(fn ($row) => array_values((array) $row), $rows)
                );
            }
        }

        $this->newLine();
        if ($this->resolveExitCode($audit) === self::SUCCESS) {
            $this->info('Audit selesai. Tidak ditemukan masalah prioritas tinggi.');
        } else {
            $this->warn('Audit selesai. Ada data yang perlu dibersihkan sebelum rollover tahun ajaran.');
        }

        return $this->resolveExitCode($audit);
    }

    private function resolveExitCode(array $audit): int
    {
        foreach ($audit['checks'] as $check) {
            if (($check['count'] ?? 0) > 0) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
