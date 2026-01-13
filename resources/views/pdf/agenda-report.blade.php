<!DOCTYPE html>
<html>

<head>
    <title>Laporan Agenda Kerja</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }

        @page {
            margin: 1cm;
        }

        /* Header Styling */
        .header-container {
            width: 100%;
            border: 2px solid #1e3a8a;
            /* Biru Gelap sesuai Logo */
            padding: 10px;
            margin-bottom: 20px;
        }

        .header-container table {
            border: none;
            width: 100%;
        }

        .logo-box {
            width: 180px;
            /* Diperlebar untuk menampung logo horizontal */
            vertical-align: middle;
            border-right: 1px solid #e2e8f0;
            padding-right: 15px;
        }

        .title-box {
            text-align: left;
            padding-left: 15px;
            vertical-align: middle;
        }

        .title-box h1 {
            text-transform: uppercase;
            color: #111827;
            margin: 0;
            font-size: 18px;
            font-weight: 800;
        }

        .title-box p {
            margin: 5px 0 0;
            font-size: 10px;
            color: #4b5563;
        }

        .highlight-text {
            color: #4f46e5;
            font-weight: bold;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background-color: #cbd5e1;
            /* Abu-abu header sesuai gambar */
            color: #1e293b;
            font-weight: bold;
            text-transform: capitalize;
            font-size: 10px;
            padding: 8px;
            border: 1px solid #94a3b8;
            text-align: left;
        }

        td {
            padding: 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
        }

        .date-row {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 10px;
            color: #1e3a8a;
        }

        .weekend {
            background-color: #fff1f2;
            color: #e11d48;
        }

        /* Status & Step Colors */
        .text-green {
            color: #16a34a;
            font-weight: bold;
        }

        .text-red {
            color: #e11d48;
            font-weight: bold;
        }

        .text-blue {
            color: #2563eb;
            font-weight: bold;
        }

        .text-muted {
            color: #64748b;
        }

        .step-container {
            margin-bottom: 5px;
            padding: 4px;
            border-bottom: 1px dotted #e2e8f0;
        }

        .overdue-bg {
            background-color: #fef2f2;
            border-left: 3px solid #ef4444;
        }

        .symbol {
            font-size: 12px;
            display: inline-block;
            width: 15px;
        }

        /* Footer Styling */
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        .time-label {
            font-size: 9px;
            color: #64748b;
            font-weight: normal;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <table>
            <tr>
                <td class="logo-box" style="border:none; border-right: 1px solid #e2e8f0;">
                    <img src="{{ public_path('images/logo.png') }}" width="160">
                </td>
                <td class="title-box" style="border:none;">
                    <h1>Laporan Monitoring Agenda</h1>
                    <p>Periode : <span class="highlight-text">{{ $month }}</span> | PIC : <span
                            class="highlight-text">{{ $filterInfo }}</span></p>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="18%">Waktu / PIC</th>
                <th width="25%">Agenda Kerja</th>
                <th width="37%">Tahapan & Durasi</th>
                <th width="20%">Status Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php $currentDate = null; @endphp
            @foreach ($data as $item)
                @if ($currentDate !== $item->display_date)
                    <tr class="date-row {{ \Carbon\Carbon::parse($item->display_date)->isWeekend() ? 'weekend' : '' }}">
                        <td colspan="4">
                            {{ \Carbon\Carbon::parse($item->display_date)->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>
                    @php $currentDate = $item->display_date; @endphp
                @endif

                @if ($item->type === 'agenda')
                    <tr>
                        <td>
                            <div class="time-label">PIC:</div>
                            <div style="font-weight: bold; color: #1e3a8a; margin-bottom: 5px;">{{ $item->user->name }}
                            </div>
                            <div class="time-label">Mulai: <b>{{ $item->jam_dibuat }}</b></div>
                            <div class="time-label">Limit: <b
                                    style="color: {{ str_contains($item->jam_deadline, ' ') ? '#e11d48' : '#333' }};">{{ $item->jam_deadline }}</b>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: bold; font-size: 10px;">{{ $item->title }}</div>
                        </td>
                        <td>
                            @foreach ($item->display_steps as $step)
                                <div class="step-container {{ $step->is_overdue ? 'overdue-bg' : '' }}">
                                    <span class="symbol">
                                        @if ($step->is_completed)
                                            <span class="text-green">✔</span>
                                        @else
                                            <span class="text-muted">○</span>
                                        @endif
                                    </span>
                                    <span style="{{ $step->is_completed ? 'color: #64748b;' : 'font-weight: bold;' }}">
                                        {{ $step->step_name }}
                                    </span>

                                    @if ($step->duration)
                                        <div style="margin-left: 20px; font-size: 8px;">
                                            <span class="{{ $step->is_overdue ? 'text-red' : 'text-blue' }}">
                                                [{{ $step->duration }}]
                                                @if ($step->is_overdue)
                                                    <span>⚠ (Terlambat {{ $step->overdue_label }})</span>
                                                @endif
                                            </span>
                                            @if ($step->is_completed)
                                                <span class="text-muted">| Selesai: {{ $step->completed_time }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td align="center">
                            @if ($item->display_status === 'completed')
                                <div class="text-green">SELESAI</div>
                                <div style="font-size: 8px; color: #64748b;">Pukul {{ $item->jam_selesai }}</div>
                            @else
                                <div class="text-blue">ONGOING</div>
                                <div style="font-size: 8px; color: #64748b;">Sedang Diproses</div>
                            @endif
                        </td>
                    </tr>
                @elseif ($item->type === 'empty_day')
                    <tr>
                        <td colspan="4" align="center" style="color: #94a3b8; font-style: italic; padding: 15px;">
                            -- Tidak ada agenda kerja yang tercatat --
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table style="border:none; width:100%">
            <tr>
                <td style="border:none; text-align:left;">Marison Regency Group - Monitoring System</td>
                <td style="border:none; text-align:right;">Dicetak: {{ now()->translatedFormat('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
