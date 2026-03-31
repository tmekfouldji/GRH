<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiches de Paie - {{ $paie->periode }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 3mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7px; line-height: 1.2; }

        .page {
            width: 291mm;
            height: 204mm;
            display: flex;
            gap: 3mm;
            page-break-after: always;
        }
        .page:last-child { page-break-after: auto; }

        .fiche-paie {
            flex: 1;
            min-width: 0;
            border: 0.5px solid #ccc;
            padding: 3mm;
            overflow: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #333;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .company-info h1 { font-size: 10px; color: #1e40af; margin-bottom: 1px; }
        .company-info p { color: #666; font-size: 6px; }
        .fiche-info { text-align: right; }
        .fiche-info h2 { font-size: 8px; color: #333; }
        .fiche-info p { font-size: 6px; color: #666; }

        .employee-section {
            background: #f8fafc;
            padding: 3px;
            border-radius: 2px;
            margin-bottom: 4px;
        }
        .employee-section h3 { font-size: 6.5px; color: #1e40af; margin-bottom: 2px; text-transform: uppercase; }
        .employee-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px 6px; font-size: 6px; }
        .employee-grid div { display: flex; justify-content: space-between; gap: 3px; }
        .employee-grid span:first-child { color: #666; white-space: nowrap; }
        .employee-grid span:last-child { font-weight: bold; text-align: right; }

        .presence-section {
            background: #f0fdf4;
            padding: 3px;
            border-radius: 2px;
            margin-bottom: 4px;
        }
        .presence-section h3 { font-size: 6.5px; color: #166534; margin-bottom: 2px; text-transform: uppercase; }
        .presence-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px 6px; font-size: 6px; }
        .presence-grid div { display: flex; justify-content: space-between; gap: 3px; }
        .presence-grid span:first-child { color: #666; white-space: nowrap; }
        .presence-grid span:last-child { font-weight: bold; }

        .salary-table { width: 100%; border-collapse: collapse; font-size: 6px; }
        .salary-table th, .salary-table td { padding: 1.5px 3px; text-align: left; border-bottom: 0.5px solid #e5e7eb; }
        .salary-table th { background: #f1f5f9; font-size: 6px; text-transform: uppercase; color: #64748b; }
        .salary-table .amount { text-align: right; font-family: monospace; font-size: 6px; }
        .salary-table .gain { color: #16a34a; }
        .salary-table .deduction { color: #dc2626; }
        .salary-table .section-header td { font-weight: bold; font-size: 6px; padding: 1.5px 3px; }
        .salary-table .subtotal { background: #f8fafc; font-weight: bold; }
        .salary-table .total { background: #1e40af; color: white; font-weight: bold; font-size: 7.5px; }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8mm;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 0.5px solid #e5e7eb;
        }
        .signature-box { text-align: center; }
        .signature-box p { font-size: 5.5px; color: #666; margin-bottom: 10px; }
        .signature-line { border-top: 0.5px solid #333; padding-top: 2px; font-size: 5px; }

        /* Empty slot placeholder */
        .fiche-empty {
            flex: 1;
            min-width: 0;
        }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .fiche-paie { border: 0.5px solid #ddd; }
        }

        @media screen {
            .page {
                border: 1px dashed #aaa;
                margin-bottom: 10px;
                background: white;
            }
        }

        .print-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
            font-size: 14px;
        }
        .print-controls:hover { background: #1e3a8a; }
    </style>
</head>
<body>
    <button class="print-controls no-print" onclick="window.print()">🖨️ Imprimer toutes les fiches</button>

    @php $fiches = $paie->fichesPaie->values(); @endphp

    @for($i = 0; $i < $fiches->count(); $i += 2)
    <div class="page">
        {{-- Left fiche --}}
        @php $fiche = $fiches[$i]; @endphp
        <div class="fiche-paie">
            @include('paies-mensuelles.partials.fiche-print', ['fiche' => $fiche, 'paie' => $paie])
        </div>

        {{-- Right fiche (if exists) --}}
        @if(isset($fiches[$i + 1]))
            @php $fiche = $fiches[$i + 1]; @endphp
            <div class="fiche-paie">
                @include('paies-mensuelles.partials.fiche-print', ['fiche' => $fiche, 'paie' => $paie])
            </div>
        @else
            <div class="fiche-empty"></div>
        @endif
    </div>
    @endfor
</body>
</html>
