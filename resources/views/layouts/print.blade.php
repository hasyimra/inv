<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Cetak')</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #000; margin: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 16px; }
        .company { font-size: 18px; font-weight: bold; }
        .doc-title { font-size: 16px; font-weight: bold; text-align: right; }
        .doc-no { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.info td { padding: 2px 8px 2px 0; vertical-align: top; }
        table.lines th, table.lines td { border: 1px solid #999; padding: 6px 8px; }
        table.lines th { background: #f0f0f0; text-align: left; }
        table.lines td.text-end, table.lines th.text-end { text-align: right; }
        .section-title { font-weight: bold; margin-bottom: 4px; }
        .footer-notes { margin-top: 16px; font-size: 12px; }
        .print-toolbar { margin-bottom: 16px; }
        @media print {
            .print-toolbar { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button onclick="window.print()">Cetak / Print</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="header">
        <div class="company">PT. Dharma Karyatama Mulia</div>
        <div>
            <div class="doc-title">@yield('doc-title')</div>
            <div class="doc-no">@yield('doc-no')</div>
        </div>
    </div>

    @yield('content')
</body>
</html>
