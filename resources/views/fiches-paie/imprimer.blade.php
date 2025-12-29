<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Paie - {{ $fichesPaie->employe->prenom }} {{ $fichesPaie->employe->nom }} - {{ $fichesPaie->periode }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info h1 {
            font-size: 24px;
            color: #1a56db;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 11px;
        }
        .payslip-title {
            text-align: right;
        }
        .payslip-title h2 {
            font-size: 20px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .payslip-title .period {
            font-size: 14px;
            color: #1a56db;
            font-weight: bold;
            margin-top: 5px;
        }
        .info-section {
            display: flex;
            gap: 40px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .info-block {
            flex: 1;
        }
        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .info-block p {
            margin-bottom: 5px;
        }
        .info-block .name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th,
        .salary-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .salary-table th {
            background: #f8f9fa;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
        }
        .salary-table .section-header {
            background: #e9ecef;
            font-weight: bold;
            color: #333;
        }
        .salary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 500;
        }
        .salary-table .subtotal {
            font-weight: bold;
            border-top: 2px solid #333;
            background: #f8f9fa;
        }
        .salary-table .deduction {
            color: #dc3545;
        }
        .net-pay {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .net-pay .label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .net-pay .value {
            font-size: 28px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            text-align: center;
            width: 200px;
        }
        .signature-block p {
            margin-bottom: 40px;
            font-size: 11px;
            color: #666;
        }
        .signature-block .line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-brouillon { background: #e9ecef; color: #495057; }
        .status-valide { background: #fff3cd; color: #856404; }
        .status-paye { background: #d4edda; color: #155724; }
        
        @media print {
            body { padding: 0; }
            .container { border: none; }
            .no-print { display: none; }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1a56db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Imprimer</button>
    
    <div class="container">
        <div class="header">
            <div class="company-info">
                <h1>TEXTILE GRH</h1>
                <p>Gestion des Ressources Humaines</p>
                <p>Système de Paie</p>
            </div>
            <div class="payslip-title">
                <h2>Bulletin de Paie</h2>
                <div class="period">{{ $fichesPaie->periode }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $fichesPaie->statut }}">
                        {{ ucfirst($fichesPaie->statut) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-block">
                <h3>Employé</h3>
                <p class="name">{{ $fichesPaie->employe->prenom }} {{ $fichesPaie->employe->nom }}</p>
                <p><strong>Matricule:</strong> {{ $fichesPaie->employe->matricule }}</p>
                <p><strong>Poste:</strong> {{ $fichesPaie->employe->poste ?? '-' }}</p>
                <p><strong>Département:</strong> {{ $fichesPaie->employe->departement ?? '-' }}</p>
            </div>
            <div class="info-block">
                <h3>Période de travail</h3>
                <p class="name">{{ $fichesPaie->periode }}</p>
                <p><strong>Heures normales:</strong> {{ number_format($fichesPaie->heures_normales, 1) }}h</p>
                <p><strong>Heures supplémentaires:</strong> {{ number_format($fichesPaie->heures_supplementaires, 1) }}h</p>
                @if($fichesPaie->date_paiement)
                <p><strong>Date de paiement:</strong> {{ $fichesPaie->date_paiement->format('d/m/Y') }}</p>
                @endif
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
                <tr class="section-header">
                    <td colspan="2">GAINS</td>
                </tr>
                <tr>
                    <td>Salaire de base</td>
                    <td class="amount">{{ number_format($fichesPaie->salaire_base, 2, ',', ' ') }}</td>
                </tr>
                @if($fichesPaie->prime_rendement > 0)
                <tr>
                    <td>Prime de rendement</td>
                    <td class="amount">{{ number_format($fichesPaie->prime_rendement, 2, ',', ' ') }}</td>
                </tr>
                @endif
                @if($fichesPaie->prime_transport > 0)
                <tr>
                    <td>Prime de transport</td>
                    <td class="amount">{{ number_format($fichesPaie->prime_transport, 2, ',', ' ') }}</td>
                </tr>
                @endif
                @if($fichesPaie->autres_primes > 0)
                <tr>
                    <td>Autres primes</td>
                    <td class="amount">{{ number_format($fichesPaie->autres_primes, 2, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="subtotal">
                    <td><strong>SALAIRE BRUT</strong></td>
                    <td class="amount"><strong>{{ number_format($fichesPaie->salaire_brut, 2, ',', ' ') }}</strong></td>
                </tr>
                
                <tr class="section-header">
                    <td colspan="2">RETENUES</td>
                </tr>
                <tr>
                    <td>Cotisation CNAS (9%)</td>
                    <td class="amount deduction">- {{ number_format($fichesPaie->cotisation_cnss, 2, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>IRG (Impôt sur le Revenu Global)</td>
                    <td class="amount deduction">- {{ number_format($fichesPaie->ir, 2, ',', ' ') }}</td>
                </tr>
                @if($fichesPaie->autres_deductions > 0)
                <tr>
                    <td>Autres déductions</td>
                    <td class="amount deduction">- {{ number_format($fichesPaie->autres_deductions, 2, ',', ' ') }}</td>
                </tr>
                @endif
                <tr class="subtotal">
                    <td><strong>TOTAL RETENUES</strong></td>
                    <td class="amount deduction"><strong>- {{ number_format($fichesPaie->total_deductions, 2, ',', ' ') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="net-pay">
            <div class="label">Net à Payer</div>
            <div class="value">{{ number_format($fichesPaie->salaire_net, 2, ',', ' ') }} DZD</div>
        </div>
        
        <div class="footer">
            <div class="signature-block">
                <p>L'Employeur</p>
                <div class="line">Signature et cachet</div>
            </div>
            <div class="signature-block">
                <p>L'Employé</p>
                <div class="line">Lu et approuvé</div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print on page load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
