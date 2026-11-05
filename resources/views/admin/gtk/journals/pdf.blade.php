<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jurnal Guru - {{ $user->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 7.2pt;
            color: #1e293b;
            line-height: 1.28;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .school-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            margin: 1px 0;
        }
        .doc-subtitle {
            font-size: 6.8pt;
            color: #475569;
        }
        .doc-period {
            font-size: 7pt;
            color: #334155;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 6.8pt;
        }
        .meta-label {
            width: 78px;
            font-weight: 600;
            color: #475569;
        }
        .meta-sep {
            width: 10px;
            text-align: center;
            color: #64748b;
        }
        .meta-val {
            color: #0f172a;
        }
        .summary-table {
            width: 100%;
            margin: 0 0 6px 0;
            border-collapse: collapse;
        }
        .summary-table td {
            width: 25%;
            border: 1px solid #cbd5e1;
            border-top: 2px solid #334155;
            background: #f8fafc;
            padding: 3px 6px;
            text-align: center;
            vertical-align: middle;
        }
        .summary-val {
            display: block;
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.15;
        }
        .summary-label {
            display: block;
            font-size: 6.2pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-top: 1px;
        }
        table.journal-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.journal-table thead {
            display: table-header-group;
        }
        table.journal-table tr {
            page-break-inside: avoid;
        }
        table.journal-table th {
            background: #1e293b;
            color: #ffffff;
            font-weight: bold;
            padding: 4px 4px;
            border: 1px solid #475569;
            text-align: center;
            font-size: 6.8pt;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        table.journal-table td {
            padding: 3px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 6.8pt;
            color: #1e293b;
            line-height: 1.25;
        }
        table.journal-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { font-size: 6.4pt; color: #64748b; }
        .text-slate-900 { color: #0f172a; }
        .text-slate-600 { color: #475569; }
        .signature-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 7.2pt;
            line-height: 1.35;
            color: #1e293b;
        }
        .signature-space {
            height: 34px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-nip {
            font-size: 6.8pt;
            color: #475569;
        }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td style="width: 58%; padding-right: 14px;">
            <div class="school-name">{{ $setting?->nama_sekolah ?: 'MAN 1 METRO' }}</div>
            <div class="doc-title">LAPORAN JURNAL KEGIATAN & PRESENSI PEMBELAJARAN GURU</div>
            <div class="doc-subtitle">Dokumen Bukti Kinerja Guru (EKIN / SKP) &middot; Sistem Informasi Manajemen Madrasah</div>
            <div class="doc-period">
                Tahun Pelajaran: <strong>{{ $year?->nama ?? '-' }} ({{ ucfirst($year?->semester_aktif ?? 'Ganjil') }})</strong>
                &nbsp;&bull;&nbsp;
                Periode: <strong>{{ $dari->translatedFormat('d/m/Y') }} s.d. {{ $sampai->translatedFormat('d/m/Y') }}</strong>
            </div>
        </td>
        <td style="width: 42%;">
            <table class="meta-table">
                <tr>
                    <td class="meta-label">Nama Guru</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val"><strong>{{ $gtk?->nama_lengkap ?? $user->name }}</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">NIP / NPK</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">{{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Jabatan / PTK</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">{{ $gtk?->jenis_ptk ?: ($gtk?->jabatan ?: 'Guru Mata Pelajaran') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Waktu Cetak</td>
                    <td class="meta-sep">:</td>
                    <td class="meta-val">{{ now()->translatedFormat('d/m/Y H:i') }} WIB</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td>
            <span class="summary-val">{{ $stats['total_entries'] }}</span>
            <span class="summary-label">Total Entri Kegiatan</span>
        </td>
        <td>
            <span class="summary-val">{{ $stats['total_hours'] > 0 ? $stats['total_hours'].' Jam' : '0 Jam' }}</span>
            <span class="summary-label">Akumulasi Durasi Tatap Muka</span>
        </td>
        <td>
            <span class="summary-val">{{ $stats['teaching_count'] }} Mengajar &middot; {{ $stats['homeroom_count'] }} Wali</span>
            <span class="summary-label">Rincian Peran</span>
        </td>
        <td>
            <span class="summary-val">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '-' }}</span>
            <span class="summary-label">Rerata Kehadiran Siswa</span>
        </td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr>
            <th style="width: 3.5%;" class="text-center">No</th>
            <th style="width: 10.5%;" class="text-center">Hari / Tanggal</th>
            <th style="width: 8.5%;" class="text-center">Peran & Durasi</th>
            <th style="width: 13.5%;" class="text-center">Kelas & Mapel</th>
            <th style="width: 36%;" class="text-center">Uraian Topik & Kegiatan Pembelajaran</th>
            <th style="width: 22%;" class="text-center">Presensi Siswa</th>
            <th style="width: 6%;" class="text-center">Paraf</th>
        </tr>
    </thead>
    <tbody>
        @forelse($journals as $index => $journal)
            @php
                $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$journal->tanggal->dayOfWeek] ?? $journal->tanggal->translatedFormat('l');
            @endphp
            <tr>
                <td class="text-center font-bold text-slate-600">{{ $index + 1 }}</td>
                <td class="text-center">
                    <span class="font-bold text-slate-900">{{ $hariIndo }}</span><br>
                    <span class="text-slate-600">{{ $journal->tanggal->format('d/m/Y') }}</span>
                </td>
                <td class="text-center">
                    <span class="font-bold">{{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali Kelas' }}</span>
                    @if($journal->durasi_menit)
                        <div class="text-muted">{{ $journal->durasi_menit }} mnt</div>
                    @endif
                </td>
                <td>
                    <span class="font-bold text-slate-900">{{ $journal->kelas_snapshot ?: '-' }}</span>
                    @if($journal->mapel_snapshot)
                        <div class="text-slate-600">{{ $journal->mapel_snapshot }}</div>
                    @endif
                </td>
                <td>
                    <div class="font-bold text-slate-900" style="margin-bottom: 1px;">{{ $journal->topik }}</div>
                    <div style="color: #334155;">{{ $journal->kegiatan }}</div>
                    @if($journal->asesmen_tindak_lanjut)
                        <div style="margin-top: 3px; padding: 2px 4px; background: #f1f5f9; border-left: 2px solid #94a3b8; font-size: 6.4pt;">
                            <strong>Asesmen/Tindak Lanjut:</strong> {{ $journal->asesmen_tindak_lanjut }}
                        </div>
                    @endif
                    @if($journal->catatan)
                        <div style="margin-top: 2px; font-style: italic; color: #64748b; font-size: 6.4pt;">
                            Catatan: {{ $journal->catatan }}
                        </div>
                    @endif
                </td>
                <td>
                    @if(!empty($journal->attendance_summary))
                        @php $att = $journal->attendance_summary; @endphp
                        <div>
                            <strong>Hadir: {{ $att['hadir'] ?? 0 }}/{{ $att['total'] ?? 0 }} ({{ $att['persentase'] ?? 100 }}%)</strong>
                        </div>
                        @if(!empty($att['absent_students']))
                            <div style="margin-top: 2px; color: #b91c1c; font-size: 6.4pt;">
                                <strong>Tidak Hadir ({{ count($att['absent_students']) }}):</strong>
                            </div>
                            <div style="font-size: 6.4pt; line-height: 1.25; color: #334155;">
                                @foreach($att['absent_students'] as $absent)
                                    <div style="padding-left: 2px;">
                                        &bull; {{ $absent['nama'] }} ({{ ucfirst($absent['status']) }}{{ !empty($absent['catatan']) ? ' - '.$absent['catatan'] : '' }})
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="margin-top: 1px; color: #166534; font-size: 6.4pt;">Semua Hadir</div>
                        @endif
                    @elseif($journal->jenis === 'mengajar')
                        <div class="text-center text-muted">-</div>
                    @else
                        <div class="text-center text-muted">-</div>
                    @endif
                </td>
                <td class="text-center" style="vertical-align: middle;">
                    <div style="height: 12px;"></div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px 0; color: #64748b;">
                    Tidak ada catatan jurnal kegiatan pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="signature-table">
    <tr>
        <td>
            Mengetahui,<br>
            Kepala Madrasah
            <div class="signature-space"></div>
            <span class="signature-name">{{ $kepalaMadrasah?->nama_lengkap ?? 'H. Gunawan Susanto, S.Pd., M.Pd.' }}</span><br>
            <span class="signature-nip">NIP. {{ $kepalaMadrasah?->nip ?? '197702222005011004' }}</span>
        </td>
        <td>
            {{ config('app.city', 'Metro') }}, {{ $sampai->translatedFormat('d F Y') }}<br>
            Guru Pengampu / Wali Kelas
            <div class="signature-space"></div>
            <span class="signature-name">{{ $gtk?->nama_lengkap ?? $user->name }}</span><br>
            <span class="signature-nip">NIP. {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}</span>
        </td>
    </tr>
</table>

</body>
</html>