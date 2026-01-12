<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Agenda - Marison Regency</title>
    <style>
        @page {
            margin: 0.8cm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.3;
        }

        .header-container {
            border-bottom: 2px solid #006688;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo-placeholder {
            float: left;
            width: 180px;
        }

        .company-info {
            float: right;
            text-align: right;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #006688;
            margin-bottom: 2px;
        }

        .clear {
            clear: both;
        }

        .report-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-title h2 {
            margin: 0;
            text-decoration: underline;
            font-size: 14px;
            color: #000;
        }

        .table-header-custom {
            background-color: #00FF00;
            color: #000;
            padding: 8px 12px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
            font-size: 9px;
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background-color: #006688;
            color: white;
            padding: 6px 4px;
            font-size: 8px;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .status-badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            display: block;
        }

        .bg-done {
            background-color: #d1fae5;
            color: #065f46;
        }

        .bg-progress {
            background-color: #fef3c7;
            color: #92400e;
        }

        .text-red {
            color: #dc2626;
            font-weight: bold;
        }

        .text-green {
            color: #16a34a;
            font-weight: bold;
        }

        .text-muted {
            color: #999;
        }

        .step-note {
            font-size: 7px;
            color: #666;
            font-style: italic;
            margin-left: 10px;
            margin-bottom: 3px;
        }

        .step-time {
            font-size: 7px;
            color: #16a34a;
            font-weight: normal;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            font-size: 8px;
            color: #aaa;
            width: 100%;
            text-align: right;
        }

        /* Style Tambahan untuk Keterlambatan */
        .row-late {
            background-color: #fff1f2;
        }

        /* Merah sangat muda untuk 1 baris */
        .badge-late {
            background-color: #be123c;
            color: white;
            padding: 2px;
            border-radius: 2px;
            font-size: 6px;
            display: block;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $lastDate = null;
    @endphp

    <div class="header-container">
        <div class="logo-placeholder">
            @if (file_exists(public_path('images/logo.png')))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}"
                    style="height: 40px;">
            @else
                <div style="font-size: 18px; font-weight: bold; color: #006688;">MARISON REGENCY</div>
            @endif
        </div>
        <div class="company-info">
            <div class="company-name">MARISON REGENCY GROUP</div>
            <div style="font-size: 8px; color: #666;">Monitoring Agenda Kerja - Digital System</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="report-title">
        <h2>REKAPITULASI AGENDA KERJA HARIAN</h2>
        <p style="margin: 5px 0; font-weight: bold;">Periode: {{ strtoupper($month) }}</p>
        <p style="margin: 0; font-size: 9px; color: #555;">User: {{ $filterInfo ?? 'Seluruh Tim' }}</p>
    </div>

    <div class="table-header-custom">DAFTAR AGENDA & TAHAPAN PROGRES</div>
    <table>
        <thead>
            <tr>
                <th width="25px">No</th>
                <th width="90px">Hari & Tanggal</th>
                <th width="110px">Judul Agenda</th>
                <th width="175px">Tahapan Kerja & Catatan</th>
                <th width="40px">Dibuat</th>
                <th width="40px">Deadline</th>
                <th width="40px">Selesai</th>
                <th width="55px">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                @php
                    $carbonDate = \Carbon\Carbon::parse($item->display_date);
                    $currentDateString = $carbonDate->translatedFormat('l, d F Y');
                    $isSameDate = $currentDateString === $lastDate;
                    $lastDate = $currentDateString;

                    // Logika Cek Terlambat (di awal agar bisa dipakai di tag <tr>)
                    $isLate = false;
                    if (
                        $item->type === 'agenda' &&
                        $item->display_status === 'completed' &&
                        isset($item->jam_selesai) &&
                        $item->jam_deadline !== '--:--'
                    ) {
                        $isLate = $item->jam_selesai > $item->jam_deadline;
                    }
                @endphp

                @if ($item->type === 'weekend')
                    <tr style="background-color: #fef2f2;">
                        <td align="center" class="text-muted">{{ $index + 1 }}</td>
                        <td style="font-weight: bold; color: #dc2626;">{{ $currentDateString }}</td>
                        <td colspan="6" align="center"
                            style="padding: 8px; color: #dc2626; font-style: italic; font-weight: bold;">
                            --- LIBUR AKHIR PEKAN (SABTU/MINGGU) ---
                        </td>
                    </tr>
                @elseif($item->type === 'empty_day')
                    <tr>
                        <td align="center" class="text-muted">{{ $index + 1 }}</td>
                        <td style="font-weight: bold; background-color: #f9f9f9;">{{ $currentDateString }}</td>
                        <td colspan="6" align="center" style="color: #d1d5db; font-style: italic; padding: 8px;">
                            Tidak ada agenda kerja aktif
                        </td>
                    </tr>
                @else
                    {{-- PEWARNAAN BARIS DISINI --}}
                    <tr class="{{ $isLate ? 'row-late' : '' }}">
                        <td align="center" style="{{ $isSameDate ? 'color: #ccc;' : '' }}">{{ $index + 1 }}</td>
                        <td
                            style="{{ $isSameDate ? 'border-top: none; color: transparent;' : 'font-weight: bold; background-color: #f9f9f9;' }}">
                            {{ $isSameDate ? '' : $currentDateString }}
                        </td>
                        <td style="font-weight: bold;">
                            {{ $item->title }}
                            <div style="font-size: 7px; font-weight: normal; color: #666; margin-top: 2px;">PIC:
                                {{ $item->user->name }}</div>
                        </td>
                        <td>
                            @if (isset($item->display_steps) && count($item->display_steps) > 0)
                                @foreach ($item->display_steps as $step)
                                    @php
                                        // Logika baru:
                                        // Step dianggap SELESAI jika:
                                        // 1. Database mencatat sudah is_completed
                                        // 2. Tanggal selesai step tersebut (completed_at) <= tanggal baris laporan yang sedang dirender

                                        $stepIsFinished = false;
                                        if ($step->is_completed && $step->completed_at) {
                                            $stepFinishedDate = \Carbon\Carbon::parse($step->completed_at)->format(
                                                'Y-m-d',
                                            );
                                            $rowDate = \Carbon\Carbon::parse($item->display_date)->format('Y-m-d');

                                            if ($stepFinishedDate <= $rowDate) {
                                                $stepIsFinished = true;
                                            }
                                        }
                                    @endphp

                                    <div style="font-size: 8px; margin-bottom: 1px;">
                                        @if ($stepIsFinished)
                                            {{-- Menggunakan Entity HTML dan Font DejaVu Sans --}}
                                            <span class="text-green"
                                                style="font-family: DejaVu Sans, sans-serif;">&#10004;</span>
                                        @else
                                            {{-- Menggunakan Entity HTML untuk lingkaran --}}
                                            <span
                                                style="color: #ccc; font-family: DejaVu Sans, sans-serif;">&#9675;</span>
                                        @endif

                                        <span style="margin-left: 2px;">{{ $step->step_name }}</span>

                                        @if ($stepIsFinished && $step->completed_time)
                                            <span class="step-time">({{ $step->completed_time }})</span>
                                        @endif
                                    </div>

                                    @if ($step->notes)
                                        <div class="step-note">Ket: {{ $step->notes }}</div>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-muted italic">Tidak ada rincian tahapan</span>
                            @endif
                        </td>
                        <td align="center">{{ $item->jam_dibuat }}</td>
                        <td align="center" class="text-red">{{ $item->jam_deadline }}</td>
                        <td align="center">
                            @if ($item->jam_selesai)
                                <span class="{{ $isLate ? 'text-red' : 'text-green' }}">
                                    {{ $item->jam_selesai }}
                                </span>
                                @if ($isLate)
                                    <span class="badge-late">TERLAMBAT</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td align="center">
                            <span
                                class="status-badge {{ $item->display_status === 'completed' ? 'bg-done' : 'bg-progress' }}">
                                {{ $item->display_status === 'completed' ? 'SELESAI' : 'PROSES' }}
                            </span>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="footer">Dicetak otomatis oleh Sistem Monitoring Marison Regency | PIC: {{ auth()->user()->name }} |
        Waktu Cetak: {{ now()->translatedFormat('d F Y H:i:s') }}</div>
</body>

</html>
