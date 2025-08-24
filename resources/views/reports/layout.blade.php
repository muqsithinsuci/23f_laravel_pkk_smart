<!-- resources/views/reports/layout.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        @page {
            margin: 1cm 1.5cm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #1e40af;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin: 0;
            color: #64748b;
        }
        
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 100px;
            color: #475569;
        }
        
        .statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 2px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table th {
            background: #1e40af;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e40af;
        }
        
        table td {
            padding: 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .text-sm {
            font-size: 10px;
        }
        
        .mb-2 {
            margin-bottom: 8px;
        }
        
        .mb-4 {
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <h2>Sistem Informasi Dasa Wisma</h2>
    </div>
    
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Petugas:</span>
            <span>{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span>{{ $date }}</span>
        </div>
        @if(isset($periode))
        <div class="info-row">
            <span class="info-label">Periode:</span>
            <span>{{ $periode }}</span>
        </div>
        @endif
        @if(isset($filters) && count(array_filter($filters)) > 0)
        <div class="info-row">
            <span class="info-label">Filter:</span>
            <span>
                @foreach(array_filter($filters) as $key => $value)
                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_bool($value) ? ($value ? 'Ya' : 'Tidak') : $value }}
                    @if(!$loop->last), @endif
                @endforeach
            </span>
        </div>
        @endif
    </div>
    
    @yield('content')
    
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Informasi Dasa Wisma pada {{ $date }}</p>
    </div>
</body>
</html>

