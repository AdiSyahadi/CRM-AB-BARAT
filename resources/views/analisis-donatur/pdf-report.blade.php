<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Analisis Donatur {{ $tahun }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1F2937;
            line-height: 1.5;
            background: #fff;
        }
        
        .page {
            padding: 30px 40px;
        }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #10B981;
            padding-bottom: 15px;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70px;
        }
        
        .header-left img {
            width: 60px;
            height: auto;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #065F46;
            margin-bottom: 3px;
        }
        
        .header-subtitle {
            font-size: 12px;
            color: #6B7280;
        }
        
        .header-period {
            font-size: 11px;
            color: #10B981;
            font-weight: 600;
            margin-top: 5px;
        }
        
        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #065F46;
            margin: 20px 0 12px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #D1FAE5;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            background: #F0FDF4;
            border: 1px solid #D1FAE5;
        }
        
        .stat-box:first-child {
            border-radius: 8px 0 0 8px;
        }
        
        .stat-box:last-child {
            border-radius: 0 8px 8px 0;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #065F46;
        }
        
        .stat-label {
            font-size: 9px;
            color: #6B7280;
            margin-top: 3px;
        }
        
        /* Chart Container */
        .chart-container {
            text-align: center;
            margin: 15px 0;
            page-break-inside: avoid;
        }
        
        .chart-container img {
            max-width: 100%;
            height: auto;
        }
        
        .chart-title {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        /* Two Column Layout */
        .two-col {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .col-left {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
            vertical-align: top;
        }
        
        .col-right {
            display: table-cell;
            width: 50%;
            padding-left: 10px;
            vertical-align: top;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 10px 0;
        }
        
        .data-table th {
            background: #10B981;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table th:first-child {
            border-radius: 6px 0 0 0;
        }
        
        .data-table th:last-child {
            border-radius: 0 6px 0 0;
        }
        
        .data-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .data-table tr:nth-child(even) td {
            background: #F9FAFB;
        }
        
        .data-table tr:last-child td:first-child {
            border-radius: 0 0 0 6px;
        }
        
        .data-table tr:last-child td:last-child {
            border-radius: 0 0 6px 0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            border: 1px solid #A7F3D0;
            border-radius: 8px;
            padding: 12px;
            margin: 10px 0;
        }
        
        .highlight-title {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 5px;
        }
        
        .highlight-value {
            font-size: 14px;
            font-weight: bold;
            color: #065F46;
        }
        
        .highlight-sub {
            font-size: 9px;
            color: #10B981;
        }
        
        /* Growth Badge */
        .growth-positive {
            color: #059669;
            font-weight: 600;
        }
        
        .growth-negative {
            color: #DC2626;
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            font-size: 9px;
            color: #9CA3AF;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
            display: table;
            width: calc(100% - 80px);
        }
        
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        
        .footer-right {
            display: table-cell;
            text-align: right;
        }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        /* Summary Cards Row */
        .summary-cards {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        
        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 5px;
        }
        
        .summary-card-inner {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        
        .summary-card-inner.primary {
            background: #ECFDF5;
            border-color: #A7F3D0;
        }
        
        .card-icon {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .card-value {
            font-size: 15px;
            font-weight: bold;
            color: #1F2937;
        }
        
        .card-value.primary {
            color: #059669;
        }
        
        .card-label {
            font-size: 9px;
            color: #6B7280;
            margin-top: 3px;
        }
        
        /* Info Row */
        .info-row {
            display: table;
            width: 100%;
            margin: 8px 0;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            color: #6B7280;
            font-size: 10px;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            font-weight: 600;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="{{ $logoUrl }}" alt="Logo">
            </div>
            <div class="header-right">
                <div class="header-title">Laporan Analisis Donatur</div>
                <div class="header-subtitle">Laz Al Bahjah - Dashboard Analytics</div>
                <div class="header-period">
                    Periode: Tahun {{ $tahun }}
                    @if($tim !== 'all') | Tim: {{ $tim }} @endif
                    @if($cs !== 'all') | CS: {{ $cs }} @endif
                </div>
            </div>
        </div>
        
        <!-- Summary Stats -->
        <div class="section-title">📊 Ringkasan Statistik</div>
        
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($stats['total_donatur']) }}</div>
                    <div class="stat-label">Total Donatur</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">Rp {{ number_format($stats['total_perolehan'] / 1000000, 1) }} Jt</div>
                    <div class="stat-label">Total Perolehan</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format($stats['total_transaksi']) }}</div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">
                        <span class="{{ $stats['growth_rate'] >= 0 ? 'growth-positive' : 'growth-negative' }}">
                            {{ $stats['growth_rate'] >= 0 ? '+' : '' }}{{ $stats['growth_rate'] }}%
                        </span>
                    </div>
                    <div class="stat-label">Growth YoY</div>
                </div>
            </div>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-inner primary">
                    <div class="card-value primary">{{ number_format($stats['donatur_baru']) }}</div>
                    <div class="card-label">Donatur Baru {{ $tahun }}</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-card-inner">
                    <div class="card-value">{{ number_format($stats['donatur_hilang']) }}</div>
                    <div class="card-label">Donatur Hilang</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-card-inner primary">
                    <div class="card-value primary">{{ $stats['repeat_donor_rate'] }}%</div>
                    <div class="card-label">Repeat Donor Rate</div>
                </div>
            </div>
        </div>
        
        <div class="info-row">
            <div class="info-label">Rata-rata per Transaksi:</div>
            <div class="info-value">Rp {{ number_format($stats['avg_per_transaksi']) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Rata-rata per Donatur:</div>
            <div class="info-value">Rp {{ number_format($stats['avg_donasi']) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Donatur Aktif Bulan Ini:</div>
            <div class="info-value">{{ number_format($stats['donatur_aktif_bulan_ini']) }} donatur</div>
        </div>
        
        <!-- Trend Chart -->
        <div class="section-title">📈 Trend Perolehan Bulanan (YoY)</div>
        <div class="chart-container">
            <img src="{{ $chartUrls['trend'] }}" alt="Trend Bulanan">
        </div>
        
        <div class="page-break"></div>
        
        <!-- Page 2 -->
        <div class="two-col">
            <div class="col-left">
                <div class="section-title">🏢 Distribusi per Tim</div>
                <div class="chart-container">
                    <img src="{{ $chartUrls['tim'] }}" alt="Distribusi Tim" style="max-height: 200px;">
                </div>
            </div>
            <div class="col-right">
                <div class="section-title">🔄 Repeat vs One-time</div>
                <div class="chart-container">
                    <img src="{{ $chartUrls['repeat'] }}" alt="Repeat vs One-time" style="max-height: 200px;">
                </div>
                <div style="margin-top: 10px; font-size: 10px; color: #6B7280;">
                    <div>• One-time: {{ number_format($charts['repeat_vs_onetime'][0]['count']) }} donatur</div>
                    <div>• Repeat: {{ number_format($charts['repeat_vs_onetime'][1]['count']) }} donatur</div>
                </div>
            </div>
        </div>
        
        <!-- Performa Harian -->
        <div class="section-title">📅 Performa Perolehan Harian</div>
        <div class="chart-container">
            <img src="{{ $chartUrls['harian'] }}" alt="Performa Harian">
        </div>
        
        <div class="highlight-box">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 50%;">
                    <div class="highlight-title">Hari Terbaik {{ $tahun }}</div>
                    <div class="highlight-value">{{ $charts['performa_harian']['summary']['best_day_tahun_ini'] }}</div>
                    <div class="highlight-sub">Rp {{ number_format($charts['performa_harian']['summary']['best_day_avg_tahun_ini']) }}/hari</div>
                </div>
                <div style="display: table-cell; width: 50%;">
                    <div class="highlight-title">Rata-rata Harian {{ $tahun }}</div>
                    <div class="highlight-value">Rp {{ number_format($charts['performa_harian']['summary']['rata_rata_tahun_ini']) }}</div>
                    <div class="highlight-sub">vs {{ $tahun_lalu }}: Rp {{ number_format($charts['performa_harian']['summary']['rata_rata_tahun_lalu']) }}</div>
                </div>
            </div>
        </div>
        
        <div class="page-break"></div>
        
        <!-- Page 3: Top Donatur -->
        <div class="section-title">🏆 Top 10 Donatur</div>
        <div class="chart-container">
            <img src="{{ $chartUrls['topDonatur'] }}" alt="Top Donatur">
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 35%;">Nama Donatur</th>
                    <th style="width: 25%;" class="text-right">Total Donasi</th>
                    <th style="width: 15%;" class="text-center">Transaksi</th>
                    <th style="width: 17%;" class="text-right">Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topDonatur as $i => $donatur)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $donatur->nama ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($donatur->total) }}</td>
                    <td class="text-center">{{ $donatur->transaksi }}x</td>
                    <td class="text-right">Rp {{ number_format($donatur->total / max($donatur->transaksi, 1)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Ranking CS -->
        @if(count($charts['ranking_cs']) > 0)
        <div class="section-title">👥 Ranking Customer Service</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 32%;">Nama CS</th>
                    <th style="width: 25%;" class="text-right">Total Perolehan</th>
                    <th style="width: 18%;" class="text-center">Donatur</th>
                    <th style="width: 17%;" class="text-center">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($charts['ranking_cs']->take(10) as $i => $cs)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $cs->nama_cs }}</td>
                    <td class="text-right">Rp {{ number_format($cs->total) }}</td>
                    <td class="text-center">{{ $cs->donatur }}</td>
                    <td class="text-center">{{ $cs->transaksi }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                Laporan ini dihasilkan secara otomatis oleh sistem Analisis Donatur Laz Al Bahjah
            </div>
            <div class="footer-right">
                Digenerate: {{ $generatedAt }}
            </div>
        </div>
    </div>
</body>
</html>
