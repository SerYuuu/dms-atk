<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Dokumen</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN REKAPITULASI DOKUMEN ARSIP TOKO ATK</h2>
        <p>Periode: {{ $startDate ?? 'Semua' }} s/d {{ $endDate ?? 'Semua' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Judul Dokumen</th>
                <th>No. Dokumen</th>
                <th>Supplier / Instansi</th>
                <th>Tanggal</th>
                <th class="text-right">Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ $doc->document_number ?? '-' }}</td>
                    <td>{{ $doc->entity_name ?? '-' }}</td>
                    <td>{{ $doc->document_date ? $doc->document_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($doc->total_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL AKUMULASI</td>
                <td class="text-right">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>