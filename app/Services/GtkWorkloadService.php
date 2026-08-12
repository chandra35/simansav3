<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\PenugasanGtk;
use App\Models\TahunPelajaran;
use Illuminate\Support\Collection;

class GtkWorkloadService
{
    public function summarize(TahunPelajaran $year, int $semester, ?string $gtkId = null): Collection
    {
        $teaching = JadwalPelajaran::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->selectRaw('gtk_id, COUNT(*) AS total')
            ->groupBy('gtk_id')
            ->pluck('total', 'gtk_id');

        $assignments = PenugasanGtk::query()
            ->with('jenis')
            ->forPeriod($year->id, $semester)
            ->active()
            ->when($gtkId, fn ($query) => $query->where('gtk_id', $gtkId))
            ->get()
            ->groupBy('gtk_id');

        $homeroomUsers = Kelas::query()
            ->where('tahun_pelajaran_id', $year->id)
            ->where('is_active', true)
            ->whereNotNull('wali_kelas_id')
            ->pluck('nama_kelas', 'wali_kelas_id');

        $gtkIds = collect($teaching->keys())
            ->merge($assignments->keys())
            ->when($gtkId, fn ($ids) => $ids->push($gtkId))
            ->unique()
            ->values();

        if ($gtkIds->isEmpty()) {
            return collect();
        }

        return Gtk::query()
            ->with('user')
            ->whereIn('id', $gtkIds)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function (Gtk $gtk) use ($teaching, $assignments, $homeroomUsers) {
                $teachingJtm = (int) ($teaching[$gtk->id] ?? 0);
                $records = $assignments->get($gtk->id, collect())->sortBy(fn ($item) => [
                    $item->jenis?->kategori === 'lain' ? 1 : 0,
                    $item->jenis?->nama,
                ]);
                $main = $records->first(fn ($item) => in_array($item->jenis?->kategori, ['utama', 'penuh'], true));
                $tasks = collect();
                $recognized = 0;
                $warnings = collect();

                if ($main) {
                    $recognized = (int) $main->ekuivalensi_jtm;
                    $tasks->push($this->taskRow($main, $recognized, true));
                    $records->reject(fn ($item) => $item->is($main))->each(function ($item) use ($tasks) {
                        $tasks->push($this->taskRow($item, 0, false, 'Tidak diakumulasi bersama tugas tambahan utama.'));
                    });
                } else {
                    $otherTasks = $records->filter(fn ($item) => $item->jenis?->kategori === 'lain')->values();
                    if ($homeroomUsers->has($gtk->user_id) && ! $otherTasks->contains(fn ($item) => $item->jenis?->kode === 'wali_kelas')) {
                        $otherTasks->prepend((object) [
                            'ekuivalensi_jtm' => 6,
                            'unit_nama' => $homeroomUsers[$gtk->user_id],
                            'jenis' => (object) ['nama' => 'Wali Kelas', 'minimal_jtm_mengajar' => 18],
                        ]);
                    }

                    $remaining = 6;
                    foreach ($otherTasks as $item) {
                        $value = min((int) $item->ekuivalensi_jtm, $remaining);
                        $recognized += $value;
                        $remaining -= $value;
                        $tasks->push($this->taskRow($item, $value, $value > 0, $value < (int) $item->ekuivalensi_jtm ? 'Dibatasi akumulasi maksimal 6 JTM.' : null));
                    }
                }

                $minimumTeaching = (int) ($main?->jenis?->minimal_jtm_mengajar ?? ($tasks->isNotEmpty() ? 18 : 24));
                if ($recognized > 0 && $teachingJtm < $minimumTeaching) {
                    $warnings->push("Jam mengajar aktual belum mencapai prasyarat {$minimumTeaching} JTM untuk penugasan ini.");
                }

                $total = $teachingJtm + $recognized;

                return [
                    'gtk' => $gtk,
                    'jtm_mengajar' => $teachingJtm,
                    'jtm_ekuivalensi' => $recognized,
                    'jtm_total' => $total,
                    'tugas_tambahan' => $tasks->values(),
                    'warnings' => $warnings->values(),
                    'status' => $warnings->isNotEmpty() ? 'review' : ($total < 24 ? 'kurang' : ($total > 40 ? 'lebih' : 'memenuhi')),
                ];
            })
            ->sortByDesc('jtm_total')
            ->values();
    }

    private function taskRow(object $assignment, int $recognized, bool $isRecognized, ?string $note = null): array
    {
        $name = $assignment->jenis?->nama ?? 'Penugasan';
        if ($assignment->unit_nama) {
            $name .= ' · '.$assignment->unit_nama;
        }

        return [
            'label' => $name,
            'jtm' => (int) $assignment->ekuivalensi_jtm,
            'jtm_diakui' => $recognized,
            'diakui' => $isRecognized,
            'catatan' => $note,
        ];
    }
}
