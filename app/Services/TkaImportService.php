<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\TkaPeserta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class TkaImportService
{
    /**
     * Import TKA cards from a PDF file
     *
     * @param string $filePath Absolute path to the PDF file
     * @param string|null $overrideTahun Optional year override (e.g. "2026")
     * @param string|null $userId Optional ID of user performing import
     * @return array Summary of import results
     */
    public function importPdf(string $filePath, ?string $overrideTahun = null, ?string $userId = null): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File PDF tidak ditemukan: {$filePath}");
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        $extractedCards = [];
        $detectedYears = [];

        foreach ($pages as $page) {
            $text = $page->getText();
            $splits = preg_split('/KARTU\s+LOGIN/u', $text);

            foreach ($splits as $i => $cardText) {
                if ($i === 0) {
                    continue; // Header before first card
                }

                // Detect Year from card text if not overridden
                $tahun = $overrideTahun;
                if (!$tahun) {
                    if (preg_match('/TAHUN\s*(\d{4})/u', $cardText, $m)) {
                        $tahun = $m[1];
                        $detectedYears[$tahun] = ($detectedYears[$tahun] ?? 0) + 1;
                    } else {
                        $tahun = date('Y');
                    }
                }

                // NISN
                $nisn = '';
                if (preg_match('/NISN\s*:\s*(\d+)/u', $cardText, $m)) {
                    $nisn = trim($m[1]);
                }

                if (empty($nisn)) {
                    continue; // Skip if no NISN
                }

                // Nama Peserta
                $nama = '';
                if (preg_match('/Nama\s+Peserta\s*:\s*([^\r\n]+)/u', $cardText, $m)) {
                    $nama = trim($m[1]);
                }

                // Tempat, Tanggal Lahir
                $ttl = '';
                if (preg_match('/Tempat,\s*Tanggal\s+Lahir\s*:\s*([^\r\n]+)/u', $cardText, $m)) {
                    $ttl = trim($m[1]);
                }

                // Username
                $username = '';
                if (preg_match('/Username\s*:\s*([^\s\r\n]+)/u', $cardText, $m)) {
                    $username = trim($m[1]);
                }

                // Password
                $password = '';
                if (preg_match('/Password\s*:\s*([^\s\r\n]+)/u', $cardText, $m)) {
                    $password = trim($m[1]);
                }

                // ID Proktor / Ruang
                $idProktor = '';
                $ruang = '';
                if (preg_match('/ID\s+Proktor\s*\/\s*Ruang\s*:\s*([^\r\n]+)/u', $cardText, $m)) {
                    $idProktorRuang = trim($m[1]);
                    $parts = explode('/', $idProktorRuang);
                    $idProktor = trim($parts[0] ?? '');
                    $ruang = trim($parts[1] ?? $idProktorRuang);
                }

                // Gelombang
                $gelombang = '';
                if (preg_match('/Gelombang\s*:\s*([^\r\n]+)/u', $cardText, $m)) {
                    $gelombang = trim(preg_replace('/\s+/', ' ', str_replace(["\xc2\xa0", "&nbsp;"], ' ', $m[1])));
                }

                // Sesi
                $sesi = '';
                if (preg_match('/Sesi\s+(\d+)/u', $cardText, $m)) {
                    $sesi = 'Sesi ' . $m[1];
                }

                // Jadwal Table Extraction
                $jadwal = [];
                preg_match_all('/(\d{2}-\d{2}-\d{4})\s*[-–]?\s*(.*?)(?=Sesi\s+\d+)\s*Sesi\s+(\d+)\s*[\r\n]+\s*([\d:]+-[\d:]+)/su', $cardText, $jMatches, PREG_SET_ORDER);

                foreach ($jMatches as $jm) {
                    $mapelRaw = trim($jm[2]);
                    $mapelLines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $mapelRaw)))));
                    $cleanedMapel = [];
                    foreach ($mapelLines as $line) {
                        $cleaned = trim(ltrim($line, "- \t\r\n"));
                        if (!empty($cleaned)) {
                            $cleanedMapel[] = $cleaned;
                        }
                    }

                    $jadwal[] = [
                        'tanggal' => $jm[1],
                        'mapel' => $cleanedMapel,
                        'sesi' => 'Sesi ' . $jm[3],
                        'waktu' => $jm[4],
                    ];
                }

                $extractedCards[] = [
                    'tahun' => $tahun,
                    'nama_peserta' => $nama,
                    'nisn' => $nisn,
                    'tempat_tanggal_lahir' => $ttl,
                    'username' => $username,
                    'password' => $password,
                    'id_proktor' => $idProktor,
                    'ruang' => $ruang,
                    'gelombang' => $gelombang,
                    'sesi' => $sesi,
                    'jadwal' => $jadwal,
                ];
            }
        }

        if (empty($extractedCards)) {
            return [
                'success' => false,
                'message' => 'Tidak ada data kartu TKA yang berhasil diekstrak dari file PDF.',
                'total_cards' => 0,
            ];
        }

        // Determine dominant year if multiple detected
        $primaryYear = $overrideTahun;
        if (!$primaryYear && !empty($detectedYears)) {
            arsort($detectedYears);
            $primaryYear = array_key_first($detectedYears);
        }
        $primaryYear = $primaryYear ?: date('Y');

        // Collect all NISNs to match against SIMANSA Siswa table
        $allNisns = array_column($extractedCards, 'nisn');
        $siswaMap = Siswa::whereIn('nisn', $allNisns)
            ->pluck('id', 'nisn')
            ->toArray();

        $importedCount = 0;
        $updatedCount = 0;
        $matchedSiswaCount = 0;

        DB::beginTransaction();
        try {
            foreach ($extractedCards as $card) {
                $nisn = $card['nisn'];
                $siswaId = $siswaMap[$nisn] ?? null;

                if ($siswaId) {
                    $matchedSiswaCount++;
                }

                $cardTahun = $card['tahun'] ?: $primaryYear;

                $existing = TkaPeserta::where('tahun', $cardTahun)
                    ->where('nisn', $nisn)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'siswa_id' => $siswaId,
                        'nama_peserta' => $card['nama_peserta'] ?: $existing->nama_peserta,
                        'tempat_tanggal_lahir' => $card['tempat_tanggal_lahir'] ?: $existing->tempat_tanggal_lahir,
                        'username' => $card['username'] ?: $existing->username,
                        'password' => $card['password'] ?: $existing->password,
                        'id_proktor' => $card['id_proktor'] ?: $existing->id_proktor,
                        'ruang' => $card['ruang'] ?: $existing->ruang,
                        'gelombang' => $card['gelombang'] ?: $existing->gelombang,
                        'sesi' => $card['sesi'] ?: $existing->sesi,
                        'jadwal' => !empty($card['jadwal']) ? $card['jadwal'] : $existing->jadwal,
                        'is_active' => true,
                        'updated_by' => $userId,
                    ]);
                    $updatedCount++;
                } else {
                    TkaPeserta::create([
                        'tahun' => $cardTahun,
                        'siswa_id' => $siswaId,
                        'nisn' => $nisn,
                        'nama_peserta' => $card['nama_peserta'],
                        'tempat_tanggal_lahir' => $card['tempat_tanggal_lahir'],
                        'username' => $card['username'],
                        'password' => $card['password'],
                        'id_proktor' => $card['id_proktor'],
                        'ruang' => $card['ruang'],
                        'gelombang' => $card['gelombang'],
                        'sesi' => $card['sesi'],
                        'jadwal' => $card['jadwal'],
                        'is_active' => true,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                    $importedCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Berhasil memproses " . count($extractedCards) . " kartu TKA.",
                'total_cards' => count($extractedCards),
                'new_imported' => $importedCount,
                'updated' => $updatedCount,
                'matched_siswa' => $matchedSiswaCount,
                'unmatched_siswa' => count($extractedCards) - $matchedSiswaCount,
                'tahun' => $primaryYear,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TKA Import Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
