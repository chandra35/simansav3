<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jurnal Guru - {{ $user->name }}</title>
    <style>
        @page {
            margin: 20px 24px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            color: #000000;
            line-height: 1.35;
        }
        .header {
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .school-title {
            font-size: 13px;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            margin: 2px 0 1px;
        }
        .doc-subtitle {
            font-size: 7.5px;
            color: #333333;
        }
        .meta-table {
            width: 100%;
            margin-top: 6px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 8px;
            color: #000000;
        }
        .meta-label {
            width: 90px;
            font-weight: bold;
        }
        .meta-val {
            font-weight: normal;
        }
        .summary-table {
            width: 100%;
            margin: 6px 0 8px;
            border-collapse: collapse;
        }
        .summary-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            text-align: center;
            background: #f7f7f7;
            vertical-align: middle;
            color: #000000;
        }
        .summary-label {
            display: block;
            font-size: 7px;
            text-transform: uppercase;
            color: #333333;
        }
        .summary-val {
            display: block;
            font-size: 9px;
            font-weight: bold;
            color: #000000;
        }
        table.journal-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.journal-table thead {
            display: table-header-group;
        }
        table.journal-table tr {
            page-break-inside: avoid;
        }
        table.journal-table th {
            background: #f0f0f0;
            color: #000000;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #000000;
            text-align: center;
            font-size: 8px;
        }
        table.journal-table td {
            padding: 4px 5px;
            border: 1px solid #000000;
            vertical-align: top;
            font-size: 8px;
            color: #000000;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { font-size: 7.5px; }
        .signature-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 8px;
            line-height: 1.4;
            color: #000000;
        }
        .signature-space {
            height: 48px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #000000;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="school-title">{{ $setting?->nama_sekolah ?: 'MAN 1 METRO' }}</div>
    <div class="doc-title">LAPORAN JURNAL KEGIATAN & PRESENSI PEMBELAJARAN GURU</div>
    <div class="doc-subtitle">Dokumen Bukti Kinerja Guru (EKIN / SKP) &middot; Sistem Informasi Manajemen Madrasah</div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Guru</td>
            <td class="meta-val">: {{ $gtk?->nama_lengkap ?? $user->name }}</td>
            <td class="meta-label">Tahun Pelajaran</td>
            <td class="meta-val">: {{ $year?->nama ?? '-' }} (Semester {{ ucfirst($year?->semester_aktif ?? 'Ganjil') }})</td>
        </tr>
        <tr>
            <td class="meta-label">NIP / NPK</td>
            <td class="meta-val">: {{ $gtk?->nip ?: ($gtk?->npk ?: '-') }}</td>
            <td class="meta-label">Periode Laporan</td>
            <td class="meta-val">: {{ $dari->translatedFormat('d F Y') }} s.d. {{ $sampai->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jabatan / PTK</td>
            <td class="meta-val">: {{ $gtk?->jenis_ptk ?: ($gtk?->jabatan ?: 'Guru Mata Pelajaran') }}</td>
            <td class="meta-label">Waktu Cetak</td>
            <td class="meta-val">: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>
</div>

<table class="summary-table">
    <tr>
        <td width="25%">
            <span class="summary-val">{{ $stats['total_entries'] }}</span>
            <span class="summary-label">Total Entri Kegiatan</span>
        </td>
        <td width="25%">
            <span class="summary-val">{{ $stats['total_hours'] }} Jam</span>
            <span class="summary-label">Akumulasi Durasi Tatap Muka</span>
        </td>
        <td width="25%">
            <span class="summary-val">{{ $stats['teaching_count'] }} Mengajar &middot; {{ $stats['homeroom_count'] }} Wali</span>
            <span class="summary-label">Rincian Peran</span>
        </td>
        <td width="25%">
            <span class="summary-val">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '-' }}</span>
            <span class="summary-label">Rerata Kehadiran Siswa</span>
        </td>
    </tr>
</table>

<table class="journal-table">
    <thead>
        <tr>
            <th width="20" class="text-center">No</th>
            <th width="62" class="text-center">Hari / Tanggal</th>
            <th width="65" class="text-center">Jenis / Durasi</th>
            <th width="95" class="text-center">Kelas & Mapel</th>
            <th class="text-center">Uraian Topik & Kegiatan</th>
            <th width="128" class="text-center">Presensi Siswa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($journals as $index => $journal)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    <span class="font-bold">{{ $journal->tanggal->format('d/m/Y') }}</span><br>
                    <span>{{ $journal->tanggal->translatedFormat('l') }}</span>
                </td>
                <td class="text-center">
                    <span class="font-bold">{{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali Kelas' }}</span>
                    @if($journal->durasi_menit)
                        <br><span class="text-muted">{{ $journal->durasi_menit }} menit</span>
                    @endif
                </td>
                <td>
                    <span class="font-bold">{{ $journal->kelas_snapshot ?: '-' }}</span>
                    @if($journal->mapel_snapshot)
                        <br><span class="text-muted">{{ $journal->mapel_snapshot }}</span>
                    @endif
                </td>
                <td>
                    <span class="font-bold">{{ $journal->topik }}</span><br>
                    {{ $journal->kegiatan }}
                    @if($journal->asesmen_tindak_lanjut)
                        <div style="margin-top: 3px;">
                            <strong>Asesmen/Tindak Lanjut:</strong> {{ $journal->asesmen_tindak_lanjut }}
                        </div>
                    @endif
                    @if($journal->catatan)
                        <div style="margin-top: 2px; font-style: italic;">
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
                            <div style="margin-top: 2px;">Semua Hadir</div>
                        @endif
                    @elseif($journal->jenis === 'mengajar')
                        <div class="text-center text-muted">-</div>
                    @else
                        <div class="text-center text-muted">-</div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 18px 0;">
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