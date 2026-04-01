<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SpanPtkinMenu;
use App\Models\SpanPtkinRegistration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser;

class SpanPtkinPdfImportService
{
    private readonly Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?? new Parser();
    }

    public function previewImport(SpanPtkinMenu $menu, string $path, string $sourceFileName): array
    {
        $rows = $this->extractRows($path);

        if ($rows->isEmpty()) {
            throw new RuntimeException('Format PDF SPAN-PTKIN tidak dikenali atau tidak ada data siswa yang berhasil dibaca.');
        }

        return $this->buildPreviewData($menu, $rows, $sourceFileName);
    }

    public function confirmImport(SpanPtkinMenu $menu, array $previewData): array
    {
        $matchedRows = collect($previewData['matched_rows'] ?? []);
        $sourceFileName = (string) ($previewData['source_file_name'] ?? 'import-span-ptkin.pdf');
        $students = $this->getEligibleStudents($menu)->keyBy('nisn');
        $now = now();

        $matched = 0;
        $updated = 0;
        $created = 0;

        DB::transaction(function () use ($matchedRows, $students, $menu, $sourceFileName, $now, &$matched, &$updated, &$created) {
            foreach ($matchedRows as $row) {
                $siswa = $students->get($row['matched_nisn'] ?? $row['nisn'] ?? '');
                if (!$siswa) {
                    continue;
                }

                $matched++;

                $registration = SpanPtkinRegistration::query()
                    ->where('span_ptkin_menu_id', $menu->id)
                    ->where('siswa_id', $siswa->id)
                    ->first();

                $payload = [
                    'tahun_pelajaran_id' => $menu->tahun_pelajaran_id,
                    'nomor_pendaftaran' => $row['nomor_pendaftaran'],
                    'nama_pendaftar' => $row['nama_siswa'],
                    'jurusan_pendaftar' => $row['jurusan'],
                    'source_file_name' => $sourceFileName,
                    'imported_at' => $now,
                ];

                if ($registration) {
                    $registration->fill($payload)->save();
                    $updated++;
                    continue;
                }

                SpanPtkinRegistration::create([
                    'span_ptkin_menu_id' => $menu->id,
                    'siswa_id' => $siswa->id,
                    ...$payload,
                ]);
                $created++;
            }
        });

        return [
            'total_rows' => count($previewData['rows'] ?? []),
            'matched' => $matched,
            'created' => $created,
            'updated' => $updated,
            'unmatched' => collect($previewData['unmatched_rows'] ?? [])->values(),
        ];
    }

    public function extractRows(string $path): Collection
    {
        $text = $this->parser->parseFile($path)->getText();
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace('/SPAN-PTKIN\s+\d{4}\s+-\s+\d{2}\/\d{2}\/\d{4}\s+\d+/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        preg_match_all(
            '/NISN\s+(?<nisn>\d{8,15})\s+Nama Siswa\s+(?<nama>.+?)\s+Jurusan\s+(?<jurusan>.+?)\s+ID Pendaftaran\s+(?<nomor>\d{6,20})/i',
            (string) $text,
            $matches,
            PREG_SET_ORDER
        );

        return collect($matches)->map(function (array $match) {
            return [
                'nisn' => trim((string) ($match['nisn'] ?? '')),
                'nama_siswa' => trim((string) ($match['nama'] ?? '')),
                'jurusan' => trim((string) ($match['jurusan'] ?? '')),
                'nomor_pendaftaran' => trim((string) ($match['nomor'] ?? '')),
            ];
        })->filter(function (array $row) {
            return $row['nisn'] !== '' && $row['nama_siswa'] !== '' && $row['nomor_pendaftaran'] !== '';
        })->values();
    }

    private function getEligibleStudents(SpanPtkinMenu $menu): Collection
    {
        $kelas12Ids = Kelas::query()
            ->where('tahun_pelajaran_id', $menu->tahun_pelajaran_id)
            ->where('tingkat', 12)
            ->pluck('id');

        return Siswa::query()
            ->whereHas('kelasAktif', function ($query) use ($kelas12Ids) {
                $query->whereIn('kelas.id', $kelas12Ids);
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    private function buildNameMap(Collection $students): array
    {
        $map = [];

        foreach ($students as $student) {
            $normalized = $this->normalize((string) $student->nama_lengkap);

            if ($normalized === '' || isset($map[$normalized])) {
                continue;
            }

            $map[$normalized] = $student;
        }

        return $map;
    }

    private function normalize(string $value): string
    {
        $value = Str::upper(Str::ascii($value));
        $value = preg_replace('/[^A-Z0-9]+/', '', $value);

        return $value ?? '';
    }

    private function buildPreviewData(SpanPtkinMenu $menu, Collection $rows, string $sourceFileName): array
    {
        $students = $this->getEligibleStudents($menu)->keyBy('nisn');
        $nameMap = $this->buildNameMap($students);
        $existingRegistrations = SpanPtkinRegistration::query()
            ->where('span_ptkin_menu_id', $menu->id)
            ->get()
            ->keyBy('siswa_id');

        $matchedRows = [];
        $unmatchedRows = [];

        $previewRows = $rows->map(function (array $row) use ($students, $nameMap, $existingRegistrations, &$matchedRows, &$unmatchedRows) {
            $matchedBy = null;
            $siswa = $students->get($row['nisn']);

            if ($siswa) {
                $matchedBy = 'nisn';
            } else {
                $candidate = $nameMap[$this->normalize($row['nama_siswa'])] ?? null;
                if ($candidate instanceof Siswa) {
                    $siswa = $candidate;
                    $matchedBy = 'nama';
                }
            }

            if (!$siswa) {
                $preview = [
                    ...$row,
                    'matched' => false,
                    'matched_by' => null,
                    'matched_nisn' => null,
                    'matched_name' => null,
                    'kelas' => null,
                    'existing_number' => null,
                    'will_action' => 'skip',
                ];
                $unmatchedRows[] = $preview;

                return $preview;
            }

            $existing = $existingRegistrations->get($siswa->id);
            $willAction = $existing ? 'update' : 'create';
            $preview = [
                ...$row,
                'matched' => true,
                'matched_by' => $matchedBy,
                'matched_nisn' => $siswa->nisn,
                'matched_name' => $siswa->nama_lengkap,
                'kelas' => $siswa->kelasSaatIni?->nama_kelas,
                'existing_number' => $existing?->nomor_pendaftaran,
                'will_action' => $willAction,
            ];
            $matchedRows[] = $preview;

            return $preview;
        })->values();

        return [
            'source_file_name' => $sourceFileName,
            'rows' => $previewRows->all(),
            'matched_rows' => $matchedRows,
            'unmatched_rows' => $unmatchedRows,
            'summary' => [
                'total_rows' => $previewRows->count(),
                'matched' => count($matchedRows),
                'unmatched' => count($unmatchedRows),
                'create' => collect($matchedRows)->where('will_action', 'create')->count(),
                'update' => collect($matchedRows)->where('will_action', 'update')->count(),
            ],
        ];
    }
}
