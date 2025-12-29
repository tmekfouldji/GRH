<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiches de Paie - {{ $paie->periode }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; line-height: 1.3; }
        
        .fiche-paie {
            width: 148mm;
            height: 210mm;
            margin: 0 auto;
            padding: 5mm;
            page-break-after: always;
            overflow: hidden;
        }
        .fiche-paie:last-child { page-break-after: auto; }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #333;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .company-info h1 { font-size: 12px; color: #1e40af; margin-bottom: 2px; }
        .company-info p { color: #666; font-size: 7px; }
        .fiche-info { text-align: right; }
        .fiche-info h2 { font-size: 10px; color: #333; }
        .fiche-info p { font-size: 7px; color: #666; }
        
        .employee-section {
            background: #f8fafc;
            padding: 6px;
            border-radius: 3px;
            margin-bottom: 8px;
        }
        .employee-section h3 { font-size: 8px; color: #1e40af; margin-bottom: 4px; }
        .employee-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2px; font-size: 7px; }
        .employee-grid div { display: flex; justify-content: space-between; }
        .employee-grid span:first-child { color: #666; }
        .employee-grid span:last-child { font-weight: bold; }
        
        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7px; }
        .salary-table th, .salary-table td { padding: 3px 5px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .salary-table th { background: #f1f5f9; font-size: 7px; text-transform: uppercase; color: #64748b; }
        .salary-table .amount { text-align: right; font-family: monospace; }
        .salary-table .gain { color: #16a34a; }
        .salary-table .deduction { color: #dc2626; }
        .salary-table .subtotal { background: #f8fafc; font-weight: bold; }
        .salary-table .total { background: #1e40af; color: white; font-weight: bold; font-size: 9px; }
        
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
        .signature-box { text-align: center; }
        .signature-box p { font-size: 7px; color: #666; margin-bottom: 20px; }
        .signature-line { border-top: 1px solid #333; padding-top: 3px; font-size: 6px; }
        
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .fiche-paie { padding: 5mm; }
            .no-print { display: none; }
        }
        
        @media screen {
            .fiche-paie {
                border: 1px dashed #ccc;
                margin-bottom: 10px;
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
        }
        .print-controls:hover { background: #1e3a8a; }
    </style>
</head>
<body>
    <button class="print-controls no-print" onclick="window.print()">🖨️ Imprimer toutes les fiches</button>
    
    @foreach($paie->fichesPaie as $fiche)
    <div class="fiche-paie">
        <div class="header">
            <div class="company-info">
                <h1>ENTREPRISE TEXTILE</h1>
                <p>Adresse de l'entreprise<br>Tél: +213 XX XX XX XX</p>
            </div>
            <div class="fiche-info">
                <h2>BULLETIN DE PAIE</h2>
                <p>Période: {{ $paie->periode }}<br>Réf: {{ $fiche->id }}</p>
            </div>
        </div>
        
        <div class="employee-section">
            <h3>INFORMATIONS EMPLOYÉ</h3>
            <div class="employee-grid">
                <div><span>Matricule:</span> <span>{{ $fiche->employe->matricule }}</span></div>
                <div><span>Nom complet:</span> <span>{{ $fiche->employe->prenom }} {{ $fiche->employe->nom }}</span></div>
                <div><span>Poste:</span> <span>{{ $fiche->employe->poste ?? '-' }}</span></div>
                <div><span>Département:</span> <span>{{ $fiche->employe->departement ?? '-' }}</span></div>
                <div><span>N° CNAS:</span> <span>{{ $fiche->employe->numero_cnas ?? $fiche->employe->cnss ?? '-' }}</span></div>
                <div><span>Mode paiement:</span> <span>{{ ucfirst($fiche->employe->mode_paiement ?? 'Virement') }}</span></div>
            </div>
        </div>
        
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="amount">Montant (DZD)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2" style="background:#e0f2fe;font-weight:bold;color:#0369a1;">GAINS</td></tr>
                <tr>
                    <td>Salaire de base</td>
                    <td class="amount">{{ number_format($fiche->salaire_base, 2, ',', ' ') }}</td>
                </tr>
                @if($fiche->prime_transport > 0)
                <tr>
                    <td>Prime de transport</td>
                    <td class="amount gain">+ {{ number_format($fiche->prime_transport, 2, ',', ' ') }}</td>
                </tr>
                @endif
                @if($fiche->prime_rendement > 0)
                <tr>
                    <td>Prime de rendement</td>
                    <td class="amount gain">+ {{ number_format($fiche->prime_rendement, 2, ',', ' ') }}</td>
                </tr>
                @endif
                @if($fiche->autres_primes > 0)
                <tr>
                    <td>Autres primes</td>
                    <td class="amount gain">+ {{ number_format($fiche->autres_primes, 2, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="subtotal">
                    <td><strong>SALAIRE BRUT</strong></td>
                    <td class="amount"><strong>{{ number_format($fiche->salaire_brut, 2, ',', ' ') }}</strong></td>
                </tr>
                
                <tr><td colspan="2" style="background:#fef2f2;font-weight:bold;color:#dc2626;">RETENUES</td></tr>
                <tr>
                    <td>Cotisation CNAS (9%)</td>
                    <td class="amount deduction">- {{ number_format($fiche->cotisation_cnss, 2, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>IRG (Impôt sur le Revenu Global)</td>
                    <td class="amount deduction">- {{ number_format($fiche->ir, 2, ',', ' ') }}</td>
                </tr>
                @if($fiche->autres_deductions > 0)
                <tr>
                    <td>Autres déductions</td>
                    <td class="amount deduction">- {{ number_format($fiche->autres_deductions, 2, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="subtotal">
                    <td><strong>TOTAL RETENUES</strong></td>
                    <td class="amount deduction"><strong>- {{ number_format($fiche->total_deductions, 2, ',', ' ') }}</strong></td>
                </tr>
                
                <tr class="total">
                    <td>NET À PAYER</td>
                    <td class="amount">{{ number_format($fiche->salaire_net, 2, ',', ' ') }} DZD</td>
                </tr>
            </tbody>
        </table>
        
        <div class="signature-section">
            <div class="signature-box">
                <p>Signature de l'employeur</p>
                <div class="signature-line">Date: ___/___/______</div>
            </div>
            <div class="signature-box">
                <p>Signature de l'employé<br>(Lu et approuvé)</p>
                <div class="signature-line">Date: ___/___/______</div>
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
