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
            font-size: 8px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .school-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            margin: 2px 0 1px;
        }
        .doc-subtitle {
            font-size: 7.5px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .meta-card {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 3px 6px;
        }
        .meta-card td {
            padding: 1px 4px;
            vertical-align: top;
            font-size: 7.5px;
            color: #334155;
        }
        .meta-label {
            width: 82px;
            font-weight: bold;
            color: #475569;
        }
        .meta-val {
            font-weight: normal;
            color: #0f172a;
        }
        .summary-table {
            width: 100%;
            margin: 6px 0 8px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .summary-table td {
            padding: 0;
            vertical-align: top;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: 2.5px solid #334155;
            padding: 4px 6px;
            text-align: center;
        }
        .summary-val {
            display: block;
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-label {
            display: block;
            font-size: 6.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            margin-top: 1px;
        }
        table.journal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
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
            padding: 5px 4px;
            border: 1px solid #94a3b8;
            text-align: center;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.journal-table td {
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 7.5px;
            color: #1e293b;
            line-height: 1.3;
        }
        table.journal-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { font-size: 7px; color: #64748b; }
        .text-slate-900 { color: #0f172a; }
        .text-slate-600 { color: #475569; }
        .signature-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 8px;
            line-height: 1.4;
            color: #1e293b;
        }
        .signature-space {
            height: 38px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td style="width: 55%; padding-right: 12px;">
            <div class="school-name">{{ $setting?->nama_sekolah ?: 'MAN 1 METRO' }}</div>
            <div class="doc-title">LAPORAN JURNAL KEGIATAN & PRESENSI PEMBELAJARAN GURU</div>
            <div class="doc-subtitle">Dokumen Bukti Kinerja Guru (EKIN / SKP) &middot; Sistem Informasi Manajemen Madrasah</div>
        </td>
        <td style="width: 45%;">
            <table class="meta-card">
                <tr>
                    <td class="meta-label">Nama Guru</td>
                    <td class="meta-val">: <strong>{{ $gtk?->nama_lengkap ?? $user->name }}</strong></td>
                    <td class="meta-label">Tahun Pelajaran</td>
                    <td class="meta-val">: {{ $year?->nama ?? '-' }} ({{ ucfirst($year?->semester_aktif ?? 'Ganjil') }})</td>
                </tr>
                <tr>
                    <td class="meta-label">NIP / NPK</td>
                    <td class="meta-val">: {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}</td>
                    <td class="meta-label">Periode Laporan</td>
                    <td class="meta-val">: {{ $dari->translatedFormat('d/m/Y') }} s.d. {{ $sampai->translatedFormat('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Jabatan / PTK</td>
                    <td class="meta-val">: {{ $gtk?->jenis_ptk ?: ($gtk?->jabatan ?: 'Guru Mata Pelajaran') }}</td>
                    <td class="meta-label">Waktu Cetak</td>
                    <td class="meta-val">: {{ now()->translatedFormat('d/m/Y H:i') }} WIB</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="summary-table">
    <tr>
        <td width="25%">
            <div class="stat-card">
                <span class="summary-val">{{ $stats['total_entries'] }}</span>
                <span class="summary-label">Total Entri Kegiatan</span>
            </div>
        </td>
        <td width="25%">
            <div class="stat-card">
                <span class="summary-val">{{ $stats['total_hours'] }} Jam</span>
                <span class="summary-label">Akumulasi Durasi Tatap Muka</span>
            </div>
        </td>
        <td width="25%">
            <div class="stat-card">
                <span class="summary-val">{{ $stats['teaching_count'] }} Mengajar &middot; {{ $stats['homeroom_count'] }} Wali</span>
                <span class="summary-label">Rincian Peran</span>
            </div>
        </td>
        <td width="25%">
            <div class="stat-card">
                <span class="summary-val">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '-' }}</span>
                <span class="summary-label">Rerata Kehadiran Siswa</span>
            </div>
        </td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr>
            <th width="3%" class="text-center">No</th>
            <th width="11%" class="text-center">Hari / Tanggal</th>
            <th width="9%" class="text-center">Peran & Durasi</th>
            <th width="13%" class="text-center">Kelas & Mapel</th>
            <th width="33%" class="text-center">Uraian Topik & Kegiatan Pembelajaran</th>
            <th width="22%" class="text-center">Presensi Siswa</th>
            <th width="9%" class="text-center">Paraf / Ket.</th>
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
                        <div class="text-muted">{{ $journal->durasi_menit }} menit</div>
                    @endif
                </td>
                <td>
                    <span class="font-bold text-slate-900">{{ $journal->kelas_snapshot ?: '-' }}</span>
                    @if($journal->mapel_snapshot)
                        <div class="text-slate-600">{{ $journal->mapel_snapshot }}</div>
                    @endif
                </td>
                <td>
                    <div class="font-bold text-slate-900" style="margin-bottom: 2px;">{{ $journal->topik }}</div>
                    <div style="color: #334155;">{{ $journal->kegiatan }}</div>
                    @if($journal->asesmen_tindak_lanjut)
                        <div style="margin-top: 4px; padding: 2px 4px; background: #f1f5f9; border-left: 2px solid #94a3b8; font-size: 7px;">
                            <strong>Asesmen/Tindak Lanjut:</strong> {{ $journal->asesmen_tindak_lanjut }}
                        </div>
                    @endif
                    @if($journal->catatan)
                        <div style="margin-top: 2px; font-style: italic; color: #64748b; font-size: 7px;">
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
                            <div style="margin-top: 2px;">
                                <strong>Tidak Hadir ({{ count($att['absent_students']) }}):</strong>
                                @foreach($att['absent_students'] as $absent)
                                    <div style="padding-left: 2px; line-height: 1.25;">
                                        &bull; {{ $absent['nama'] }} ({{ ucfirst($absent['status']) }}{{ !empty($absent['catatan']) ? ' - '.$absent['catatan'] : '' }})
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="margin-top: 2px; color: #166534;">Semua Hadir</div>
                        @endif
                    @elseif($journal->jenis === 'mengajar')
                        <div class="text-center text-muted">-</div>
                    @else
                        <div class="text-center text-muted">-</div>
                    @endif
                </td>
                <td class="text-center" style="vertical-align: middle;">
                    <div style="height: 28px;"></div>
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
            NIP. {{ $kepalaMadrasah?->nip ?? '197702222005011004' }}
        </td>
        <td>
            {{ config('app.city', 'Metro') }}, {{ $sampai->translatedFormat('d F Y') }}<br>
            Guru Pengampu / Wali Kelas
            <div class="signature-space"></div>
            <span class="signature-name">{{ $gtk?->nama_lengkap ?? $user->name }}</span><br>
            NIP. {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}
        </td>
    </tr>
</table>

</body>
</html>