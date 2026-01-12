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

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }

        .header h1 {
            text-transform: uppercase;
            font-style: italic;
            color: #4f46e5;
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 8px;
            border: 1px solid #e2e8f0;
        }

        td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            word-wrap: break-word;
        }

        .date-row {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 9px;
        }

        .weekend {
            background-color: #fff1f2;
            color: #e11d48;
        }

        .text-green {
            color: #16a34a;
        }

        .text-red {
            color: #e11d48;
        }

        .text-blue {
            color: #2563eb;
        }

        .text-muted {
            color: #94a3b8;
        }

        .step-container {
            margin-bottom: 4px;
            padding: 3px;
            border-radius: 3px;
        }

        .overdue-bg {
            background-color: #fff1f2;
            border-left: 3px solid #e11d48;
        }

        .symbol {
            font-size: 11px;
            display: inline-block;
            width: 12px;
        }

        .duration-tag {
            font-size: 7px;
            font-weight: bold;
            margin-left: 4px;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            text-align: right;
            color: #94a3b8;
        }

        .time-col {
            font-size: 7.5px;
            text-align: center;
            line-height: 1.2;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Monitoring Agenda</h1>
        <p>Periode: {{ $month }} | PIC: {{ $filterInfo }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Waktu</th>
                <th width="20%">PIC & Agenda</th>
                <th width="45%">Tahapan & Durasi</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $currentDate = null; @endphp
            @foreach ($data as $item)
                @if ($currentDate !== $item->display_date)
                    <tr class="date-row {{ \Carbon\Carbon::parse($item->display_date)->isWeekend() ? 'weekend' : '' }}">
                        <td colspan="4">{{ \Carbon\Carbon::parse($item->display_date)->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>
                    @php $currentDate = $item->display_date; @endphp
                @endif

                @if ($item->type === 'agenda')
                    <tr>
                        <td class="time-col">
                            <div style="color: #64748b;">Mulai:</div>
                            <div style="font-weight: bold; margin-bottom: 5px;">{{ $item->jam_dibuat }}</div>
                            <div style="color: #64748b;">Limit:</div>
                            <div
                                style="font-weight: bold; color: {{ str_contains($item->jam_deadline, ' ') ? '#e11d48' : '#333' }};">
                                {{ $item->jam_deadline }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: bold; color: #4f46e5;">{{ $item->user->name }}</div>
                            <div style="font-size: 9px; margin-top: 2px;">{{ $item->title }}</div>
                        </td>
                        <td>
                            @foreach ($item->display_steps as $step)
                                <div class="step-container {{ $step->is_overdue ? 'overdue-bg' : '' }}">
                                    <span class="symbol">
                                        @if ($step->is_completed)
                                            <span class="text-green">&#10004;</span>
                                        @else
                                            <span class="text-muted">&#9675;</span>
                                        @endif
                                    </span>
                                    <span
                                        style="{{ $step->is_completed ? 'color: #64748b;' : 'font-weight: bold;' }}">{{ $step->step_name }}</span>

                                    @if ($step->duration)
                                        <span class="duration-tag {{ $step->is_overdue ? 'text-red' : 'text-blue' }}">
                                            [{{ $step->duration }}]
                                            @if ($step->is_overdue)
                                                <span>&#9888; <i>(Telat {{ $step->overdue_label }})</i></span>
                                            @endif
                                        </span>
                                    @endif

                                    @if ($step->is_completed)
                                        <span style="float: right; font-size: 7px;"
                                            class="text-muted">{{ $step->completed_time }}</span>
                                    @endif
                                    <div style="clear: both;"></div>
                                </div>
                            @endforeach
                        </td>
                        <td align="center">
                            @if ($item->display_status === 'completed')
                                <span class="text-green" style="font-weight: bold;">SELESAI
                                    ({{ $item->jam_selesai }})</span>
                            @else
                                <span class="text-blue" style="font-weight: bold;">ONGOING</span>
                            @endif
                        </td>
                    </tr>
                @elseif ($item->type === 'empty_day')
                    <tr>
                        <td colspan="4" align="center" style="color: #94a3b8; font-style: italic; padding: 10px;">
                            Tidak ada agenda kerja.</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="footer">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>

</html>
