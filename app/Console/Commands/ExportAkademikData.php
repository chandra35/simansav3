<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\TahunPelajaran;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class ExportAkademikData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'akademik:export-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Kurikulum, Jurusan, Tahun Pelajaran, Kelas data before UUID conversion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📦 Exporting Akademik data for UUID conversion...');
        $this->newLine();

        // Create backup directory
        $backupPath = storage_path('app/uuid_conversion_backup');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // 1. Export Kurikulum
        $this->info('1️⃣  Exporting Kurikulum...');
        $kurikulumData = Kurikulum::with('jurusans')->get()->toArray();
        file_put_contents($backupPath . '/kurikulum.json', json_encode($kurikulumData, JSON_PRETTY_PRINT));
        $kurikulumCount = count($kurikulumData);
        $this->line("   ✓ {$kurikulumCount} records exported");

        // 2. Export Jurusan
        $this->info('2️⃣  Exporting Jurusan...');
        $jurusanData = Jurusan::all()->toArray();
        file_put_contents($backupPath . '/jurusan.json', json_encode($jurusanData, JSON_PRETTY_PRINT));
        $jurusanCount = count($jurusanData);
        $this->line("   ✓ {$jurusanCount} records exported");

        // 3. Export Tahun Pelajaran
        $this->info('3️⃣  Exporting Tahun Pelajaran...');
        $tahunPelajaranData = TahunPelajaran::all()->toArray();
        file_put_contents($backupPath . '/tahun_pelajaran.json', json_encode($tahunPelajaranData, JSON_PRETTY_PRINT));
        $tahunPelajaranCount = count($tahunPelajaranData);
        $this->line("   ✓ {$tahunPelajaranCount} records exported");

        // 4. Export Kelas
        $this->info('4️⃣  Exporting Kelas...');
        $kelasData = Kelas::all()->toArray();
        file_put_contents($backupPath . '/kelas.json', json_encode($kelasData, JSON_PRETTY_PRINT));
        $kelasCount = count($kelasData);
        $this->line("   ✓ {$kelasCount} records exported");

        // 5. Export Siswa Kelas (pivot)
        $this->info('5️⃣  Exporting Siswa Kelas (pivot)...');
        $siswaKelasData = DB::table('siswa_kelas')->get()->toArray();
        file_put_contents($backupPath . '/siswa_kelas.json', json_encode($siswaKelasData, JSON_PRETTY_PRINT));
        $siswaKelasCount = count($siswaKelasData);
        $this->line("   ✓ {$siswaKelasCount} records exported");

        $this->newLine();
        $this->info('✅ Data exported successfully!');
        $this->newLine();
        
        $this->table(
            ['Table', 'Records'],
            [
                ['Kurikulum', $kurikulumCount],
                ['Jurusan', $jurusanCount],
                ['Tahun Pelajaran', $tahunPelajaranCount],
                ['Kelas', $kelasCount],
                ['Siswa Kelas', $siswaKelasCount],
            ]
        );

        $this->newLine();
        $this->comment('📁 Backup location: ' . $backupPath);
        $this->newLine();
        $this->warn('⚠️  Next steps:');
        $this->line('   1. Run: php artisan migrate --path=database/migrations/uuid_conversion');
        $this->line('   2. Run: php artisan akademik:import-data');
        
        return Command::SUCCESS;
    }
}
