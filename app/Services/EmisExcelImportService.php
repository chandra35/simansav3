<?php

namespace App\Services;

use App\Models\Ortu;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmisExcelImportService
{
    /**
     * Mapping: internal field name → possible EMIS column header aliases (lowercase).
     */
    protected array $colAliases = [
        'nama_lengkap'  => ['nama lengkap', 'nama', 'full name', 'nama siswa'],
        'nisn'          => ['nisn'],
        'nik'           => ['nik', 'nomor induk kependudukan', 'no nik'],
        'tempat_lahir'  => ['tempat lahir', 'tempat_lahir', 'kota lahir'],
        'tanggal_lahir' => ['tanggal lahir', 'tgl lahir', 'tanggal_lahir', 'tgl. lahir', 'tgl lahir'],
        'jenis_kelamin' => ['jenis kelamin', 'jk', 'gender', 'l/p', 'kelamin'],
        'rombel'        => ['tingkat - rombel', 'rombel', 'kelas', 'nama kelas', 'nama rombel'],
        'status'        => ['status', 'status siswa'],
        'alamat'        => ['alamat', 'alamat lengkap', 'alamat tinggal'],
        'no_hp'         => ['no telepon', 'no hp', 'nomor telepon', 'telepon', 'hp', 'no. hp', 'no. telepon'],
        'nama_ayah'     => ['nama ayah kandung', 'nama ayah', 'ayah', 'nama bapak'],
        'nama_ibu'      => ['nama ibu kandung', 'nama ibu', 'ibu'],
        'nama_wali'     => ['nama wali', 'wali', 'nama wali murid'],
        'nomor_kip'     => ['nomor kip/pip', 'kip', 'pip', 'nomor kip', 'no kip'],
    ];

    // ─────────────────────────────────────────────
    //  PUBLIC: Parse file → preview array
    // ─────────────────────────────────────────────

    public function parse(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());

        $allRows = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetRows = $this->parseSheet($sheet);
            $allRows   = array_merge($allRows, $sheetRows);
        }

        return $this->matchWithDb($allRows);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC: Execute confirmed rows
    // ─────────────────────────────────────────────

    /**
     * @param  array  $items  Subset of preview items (already filtered by user selection)
     * @return array  { done: int, errors: array }
     */
    public function execute(array $items): array
    {
        $done   = 0;
        $errors = [];

        foreach ($items as $idx => $item) {
            try {
                DB::transaction(function () use ($item, &$done) {
                    $action = $item['action'];
                    $emis   = $item['emis'];

                    if ($action === 'baru') {
                        $this->createNew($emis);
                    } else {
                        // update or fuzzy
                        $this->updateExisting($item['existing']['id'], $emis);
                    }
                    $done++;
                });
            } catch (\Throwable $e) {
                Log::error('EMIS Import error baris ' . ($idx + 1) . ': ' . $e->getMessage(), [
                    'nama' => $item['emis']['nama_lengkap'] ?? '?',
                    'nisn' => $item['emis']['nisn'] ?? '?',
                ]);
                $errors[] = [
                    'row'   => $idx + 1,
                    'nama'  => $item['emis']['nama_lengkap'] ?? '?',
                    'nisn'  => $item['emis']['nisn'] ?? '-',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ['done' => $done, 'errors' => $errors];
    }

    // ─────────────────────────────────────────────
    //  PROTECTED: Sheet parsing
    // ─────────────────────────────────────────────

    protected function parseSheet($sheet): array
    {
        $sheetName = $sheet->getTitle();

        // toArray: nullVal=null, calcFormulas=true, formatData=true, returnCellRef=false
        $rawData = $sheet->toArray(null, true, true, false);
        if (empty($rawData)) {
            return [];
        }

        // Find header row: first row that contains 'nisn' or 'nama lengkap'
        $headerRowIdx = null;
        foreach ($rawData as $idx => $row) {
            $flat = strtolower(implode(' ', array_filter(array_map('strval', $row))));
            if (str_contains($flat, 'nisn') || str_contains($flat, 'nama lengkap')) {
                $headerRowIdx = $idx;
                break;
            }
        }
        if ($headerRowIdx === null) {
            return []; // Sheet doesn't look like student data
        }

        $headers  = $rawData[$headerRowIdx];
        $fieldMap = $this->buildFieldMap($headers);

        // Detect tingkat from sheet name (e.g. "Kelas 12 - XII A 1" → 12)
        $tingkatSheet = $this->detectTingkat($sheetName);

        $rows = [];
        for ($i = $headerRowIdx + 1; $i < count($rawData); $i++) {
            $raw = $rawData[$i];

            // Extract fields, passing sheet+row for date serial detection
            $row = $this->extractRow($raw, $fieldMap, $sheet, $i + 1); // +1: sheet row is 1-indexed

            // Skip fully empty rows
            if (empty($row['nisn']) && empty($row['nama_lengkap'])) {
                continue;
            }
            // Skip non-aktif students
            if (!empty($row['status']) && strtolower(trim($row['status'])) !== 'aktif') {
                continue;
            }

            // Resolve tingkat: sheet name first, then rombel column
            $tingkat = $tingkatSheet;
            if (!$tingkat && !empty($row['rombel'])) {
                $tingkat = $this->detectTingkat($row['rombel']);
            }

            $row['tingkat_emis'] = $tingkat;
            $row['sheet_name']   = $sheetName;

            $rows[] = $row;
        }

        return $rows;
    }

    protected function buildFieldMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $colIdx => $header) {
            if ($header === null || $header === '') {
                continue;
            }
            $normalized = strtolower(trim(preg_replace('/\s+/', ' ', (string) $header)));
            foreach ($this->colAliases as $field => $aliases) {
                if (in_array($normalized, $aliases) && !isset($map[$field])) {
                    $map[$field] = $colIdx;
                    break;
                }
            }
        }
        return $map;
    }

    protected function extractRow(array $raw, array $fieldMap, $sheet, int $sheetRowNum): array
    {
        $row = [];
        foreach ($this->colAliases as $field => $_aliases) {
            if (!isset($fieldMap[$field])) {
                $row[$field] = null;
                continue;
            }
            $colIdx = $fieldMap[$field];
            $rawVal = $raw[$colIdx] ?? null;

            // Date fields: when toArray returns formatted string, Carbon handles it.
            // But if it's still a raw serial (numeric), detect via cell object.
            if ($field === 'tanggal_lahir' && is_numeric($rawVal) && $rawVal > 1000) {
                try {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $cell      = $sheet->getCell("{$colLetter}{$sheetRowNum}");
                    if (ExcelDate::isDateTime($cell)) {
                        $dt     = ExcelDate::excelToDateTimeObject((float) $rawVal);
                        $rawVal = $dt->format('Y-m-d');
                    }
                } catch (\Throwable $e) {
                    // leave rawVal as-is, cleanValue will try Carbon::parse
                }
            }

            $row[$field] = $this->cleanValue($field, $rawVal);
        }
        return $row;
    }

    protected function cleanValue(string $field, $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        switch ($field) {
            case 'nisn':
            case 'nik':
            case 'nomor_kip':
                // Strip apostrophes and any non-digit character
                return preg_replace('/[^\d]/', '', $value) ?: null;

            case 'tanggal_lahir':
                try {
                    return Carbon::parse($value)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return $value;
                }

            case 'jenis_kelamin':
                $v = strtolower($value);
                if (in_array($v, ['l', 'laki-laki', 'laki laki', 'male', 'laki'])) return 'L';
                if (in_array($v, ['p', 'perempuan', 'female', 'wanita']))           return 'P';
                return strtoupper(substr($value, 0, 1));

            default:
                return $value;
        }
    }

    protected function detectTingkat(?string $text): ?int
    {
        if (!$text) return null;

        // e.g. "10", "11", "12" as standalone word
        if (preg_match('/\b(10|11|12)\b/', $text, $m)) {
            return (int) $m[1];
        }
        // Roman numerals: XII, XI, X (case-insensitive, whole word)
        if (preg_match('/\b(XII|XI|X)\b/i', $text, $m)) {
            return match (strtolower($m[1])) {
                'xii' => 12,
                'xi'  => 11,
                'x'   => 10,
                default => null,
            };
        }
        return null;
    }

    // ─────────────────────────────────────────────
    //  PROTECTED: DB matching
    // ─────────────────────────────────────────────

    protected function matchWithDb(array $rows): array
    {
        // Eager-load all active siswa for matching (preload into memory)
        $siswas = Siswa::with(['ortu', 'kelasSaatIni'])
            ->whereNull('deleted_at')
            ->get();

        // Build lookup maps
        $byNisn = [];
        $byNik  = [];
        foreach ($siswas as $s) {
            if ($s->nisn) $byNisn[$s->nisn] = $s;
            if ($s->nik)  $byNik[$s->nik]   = $s;
        }

        $result  = [];
        $seenKey = []; // de-duplicate within the uploaded file

        foreach ($rows as $row) {
            $nisn = $row['nisn'] ?? null;
            $nik  = $row['nik']  ?? null;

            $match      = null;
            $action     = 'baru';
            $confidence = null;
            $fuzzyNote  = null;

            // 1. Exact NISN
            if ($nisn && isset($byNisn[$nisn])) {
                $match      = $byNisn[$nisn];
                $action     = 'update';
                $confidence = 'NISN';
            }
            // 2. Exact NIK
            elseif ($nik && isset($byNik[$nik])) {
                $match      = $byNik[$nik];
                $action     = 'update';
                $confidence = 'NIK';
            }
            // 3. Fuzzy name + tingkat
            elseif (!empty($row['nama_lengkap'])) {
                $best     = null;
                $bestPct  = 0.0;
                $namaEmis = mb_strtolower($row['nama_lengkap']);

                foreach ($siswas as $s) {
                    similar_text($namaEmis, mb_strtolower($s->nama_lengkap), $pct);

                    $tingkatOk = !$row['tingkat_emis']
                        || ($s->kelasSaatIni && (int)$s->kelasSaatIni->tingkat === (int)$row['tingkat_emis']);

                    if ($pct > $bestPct && $pct >= 80 && $tingkatOk) {
                        $bestPct = $pct;
                        $best    = $s;
                    }
                }

                if ($best) {
                    $match      = $best;
                    $action     = 'fuzzy';
                    $confidence = round($bestPct) . '%';
                    $fuzzyNote  = 'EMIS: "' . $row['nama_lengkap'] . '" ≈ Simansa: "' . $best->nama_lengkap . '"';
                }
            }

            // De-duplicate rows within the file itself
            $dedupeKey = $nisn ?: ($nik ? 'nik_' . $nik : 'nm_' . md5(mb_strtolower($row['nama_lengkap'] ?? '')));
            if (isset($seenKey[$dedupeKey])) {
                $action     = 'skip';
                $confidence = 'duplikat dalam file';
            }
            $seenKey[$dedupeKey] = true;

            // Build existing snapshot for display
            $existing = null;
            if ($match) {
                $existing = [
                    'id'            => $match->id,
                    'nisn'          => $match->nisn,
                    'nik'           => $match->nik,
                    'nama_lengkap'  => $match->nama_lengkap,
                    'jenis_kelamin' => $match->jenis_kelamin,
                    'tempat_lahir'  => $match->tempat_lahir,
                    'tanggal_lahir' => $match->tanggal_lahir?->format('Y-m-d'),
                    'kelas'         => $match->kelasSaatIni?->nama_kelas,
                    'nama_ayah'     => $match->ortu?->nama_ayah,
                    'nama_ibu'      => $match->ortu?->nama_ibu,
                ];
            }

            // Data "lengkap" = semua field penting sudah terisi di Simansa
            // (hanya relevan untuk update/fuzzy, bukan baru)
            $existingComplete = false;
            if ($existing && in_array($action, ['update', 'fuzzy'])) {
                $existingComplete = !empty($existing['nisn'])
                    && !empty($existing['nik'])
                    && !empty($existing['tempat_lahir'])
                    && !empty($existing['tanggal_lahir'])
                    && !empty($existing['nama_ayah'])
                    && !empty($existing['nama_ibu']);
            }

            $result[] = [
                'action'            => $action,      // baru | update | fuzzy | skip
                'confidence'        => $confidence,
                'fuzzy_note'        => $fuzzyNote,
                'emis'              => $row,
                'existing'          => $existing,
                'existing_complete' => $existingComplete,
                'selected'          => ($action !== 'skip' && !$existingComplete),
            ];
        }

        return $result;
    }

    // ─────────────────────────────────────────────
    //  PROTECTED: DB write operations
    // ─────────────────────────────────────────────

    protected function createNew(array $emis): void
    {
        $nisn = $emis['nisn'] ?? null;

        if ($nisn && Siswa::where('nisn', $nisn)->exists()) {
            throw new \Exception("NISN {$nisn} sudah terdaftar");
        }

        $username = $nisn ?? 'emis_' . Str::random(8);

        $user = User::create([
            'name'           => $emis['nama_lengkap'],
            'username'       => $username,
            'email'          => $username . '@siswa.simansa.sch.id',
            'password'       => Hash::make($nisn ?? Str::random(12)),
            'is_first_login' => true,
        ]);
        $user->assignRole('Siswa');

        $siswa = Siswa::create([
            'user_id'             => $user->id,
            'nisn'                => $nisn,
            'nik'                 => $emis['nik'],
            'nama_lengkap'        => $emis['nama_lengkap'],
            'jenis_kelamin'       => $emis['jenis_kelamin'],
            'tempat_lahir'        => $emis['tempat_lahir'],
            'tanggal_lahir'       => $emis['tanggal_lahir'],
            'alamat_siswa'        => $emis['alamat'],
            'nomor_hp'            => $emis['no_hp'],
            'data_diri_completed' => false,
            'data_ortu_completed' => false,
        ]);

        Ortu::create([
            'siswa_id'  => $siswa->id,
            'nama_ayah' => $emis['nama_ayah'],
            'nama_ibu'  => $emis['nama_ibu'],
        ]);

        // Set flag jika nama_ayah DAN nama_ibu sudah ada dari EMIS
        if (!empty($emis['nama_ayah']) && !empty($emis['nama_ibu'])) {
            $siswa->update(['data_ortu_completed' => true]);
        }
    }

    protected function updateExisting(string $siswaId, array $emis): void
    {
        $siswa = Siswa::with('ortu')->findOrFail($siswaId);

        // Update siswa: only fill empty fields (don't overwrite admin-corrected data)
        $updates = [];
        if ($emis['nik']           && !$siswa->nik)           $updates['nik']           = $emis['nik'];
        if ($emis['tempat_lahir']  && !$siswa->tempat_lahir)  $updates['tempat_lahir']  = $emis['tempat_lahir'];
        if ($emis['tanggal_lahir'] && !$siswa->tanggal_lahir) $updates['tanggal_lahir'] = $emis['tanggal_lahir'];
        if ($emis['jenis_kelamin'] && !$siswa->jenis_kelamin) $updates['jenis_kelamin'] = $emis['jenis_kelamin'];
        if ($emis['no_hp']         && !$siswa->nomor_hp)       $updates['nomor_hp']      = $emis['no_hp'];
        if ($emis['alamat']        && !$siswa->alamat_siswa)   $updates['alamat_siswa']  = $emis['alamat'];
        if ($emis['nisn']          && !$siswa->nisn)           $updates['nisn']          = $emis['nisn'];

        if ($updates) {
            $siswa->update($updates);
        }

        // Update ortu: EMIS is authoritative for parent names (always overwrite if EMIS has value)
        $ortuData = [];
        if (!empty($emis['nama_ayah'])) $ortuData['nama_ayah'] = $emis['nama_ayah'];
        if (!empty($emis['nama_ibu']))  $ortuData['nama_ibu']  = $emis['nama_ibu'];

        if ($ortuData) {
            if ($siswa->ortu) {
                $siswa->ortu->update($ortuData);
            } else {
                Ortu::create(array_merge(['siswa_id' => $siswa->id], $ortuData));
            }
        }

        // Update flag data_ortu_completed berdasarkan kondisi aktual setelah import:
        // true  → nama_ayah DAN nama_ibu sudah terisi (dari EMIS atau sebelumnya)
        // false → salah satu / keduanya masih kosong → tampil "Belum Lengkap"
        $siswa->refresh();
        $namaAyahFinal = $siswa->ortu?->nama_ayah ?? null;
        $namaIbuFinal  = $siswa->ortu?->nama_ibu  ?? null;
        $siswa->update([
            'data_ortu_completed' => !empty($namaAyahFinal) && !empty($namaIbuFinal),
        ]);
    }
}
