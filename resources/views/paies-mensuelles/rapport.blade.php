<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Paie - {{ $paie->periode }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; padding: 15mm; }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-info h1 { font-size: 20px; color: #1e40af; margin-bottom: 5px; }
        .company-info p { color: #666; font-size: 10px; }
        .report-info { text-align: right; }
        .report-info h2 { font-size: 16px; color: #333; margin-bottom: 5px; }
        .report-info p { font-size: 11px; color: #666; }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .summary-card.highlight { background: #1e40af; color: white; }
        .summary-card.highlight .label { color: rgba(255,255,255,0.8); }
        .summary-card .value { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .summary-card .label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        
        .section { margin-bottom: 25px; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; color: #475569; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-blue { color: #2563eb; }
        
        .totals-row { background: #f8fafc; font-weight: bold; }
        .grand-total { background: #1e40af; color: white; font-size: 12px; }
        
        .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-done { background: #d1fae5; color: #065f46; }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #666;
        }
        
        .signature-area {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .signature-box { text-align: center; }
        .signature-box p { margin-bottom: 50px; font-size: 10px; }
        .signature-line { border-top: 1px solid #333; padding-top: 5px; }
        
        @media print {
            body { padding: 10mm; }
            .no-print { display: none; }
        }
        
        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #1e40af;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover { background: #1e3a8a; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Imprimer le rapport</button>
    
    <div class="header">
        <div class="company-info">
            <h1>ENTREPRISE TEXTILE</h1>
            <p>Adresse de l'entreprise<br>N° Registre Commerce: XXXXXXXXXX<br>N° CNAS Employeur: XXXXXXXXXX</p>
        </div>
        <div class="report-info">
            <h2>RAPPORT DE PAIE MENSUELLE</h2>
            <p>
                Période: <strong>{{ $paie->periode }}</strong><br>
                Référence: {{ $paie->reference }}<br>
                Date d'édition: {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="value">{{ $paie->nombre_employes }}</div>
            <div class="label">Employés</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ number_format($paie->total_brut, 0, ',', ' ') }}</div>
            <div class="label">Total Brut (DZD)</div>
        </div>
        <div class="summary-card">
            <div class="value text-red">{{ number_format($paie->total_deductions, 0, ',', ' ') }}</div>
            <div class="label">Total Retenues (DZD)</div>
        </div>
        <div class="summary-card highlight">
            <div class="value">{{ number_format($paie->total_net, 0, ',', ' ') }}</div>
            <div class="label">Total Net à Payer (DZD)</div>
        </div>
    </div>
    
    <!-- Détail des cotisations -->
    <div class="section">
        <h3 class="section-title">📊 Récapitulatif des Cotisations et Impôts</h3>
        <div class="two-columns">
            <table>
                <tr>
                    <td>Total Salaires de Base</td>
                    <td class="text-right font-bold">{{ number_format($paie->total_salaires_base, 2, ',', ' ') }} DZD</td>
                </tr>
                <tr>
                    <td>Total Primes</td>
                    <td class="text-right font-bold text-green">+ {{ number_format($paie->total_primes, 2, ',', ' ') }} DZD</td>
                </tr>
                <tr class="totals-row">
                    <td>TOTAL BRUT</td>
                    <td class="text-right">{{ number_format($paie->total_brut, 2, ',', ' ') }} DZD</td>
                </tr>
            </table>
            
            <table>
                <tr>
                    <td>Cotisations CNAS (9% salarié)</td>
                    <td class="text-right font-bold text-red">{{ number_format($paie->total_cotisations_cnas, 2, ',', ' ') }} DZD</td>
                </tr>
                <tr>
                    <td>IRG (Impôt sur Revenu Global)</td>
                    <td class="text-right font-bold text-red">{{ number_format($paie->total_irg, 2, ',', ' ') }} DZD</td>
                </tr>
                <tr class="totals-row">
                    <td>TOTAL RETENUES</td>
                    <td class="text-right text-red">{{ number_format($paie->total_deductions, 2, ',', ' ') }} DZD</td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Charges Employeur -->
    <div class="section">
        <h3 class="section-title">💼 Charges Patronales</h3>
        <table>
            <tr>
                <td>Cotisations CNAS Employeur (25% du brut)</td>
                <td class="text-right font-bold">{{ number_format($paie->total_charges_patronales, 2, ',', ' ') }} DZD</td>
            </tr>
            <tr class="grand-total">
                <td>COÛT TOTAL EMPLOYEUR</td>
                <td class="text-right">{{ number_format($paie->cout_total_employeur, 2, ',', ' ') }} DZD</td>
            </tr>
        </table>
    </div>
    
    <!-- Statuts -->
    <div class="section">
        <h3 class="section-title">📋 Statuts des Déclarations</h3>
        <table>
            <tr>
                <td>Déclaration CNAS</td>
                <td class="text-center">
                    <span class="status-badge {{ $paie->statut_cnas === 'paye' ? 'status-done' : 'status-pending' }}">
                        {{ $paie->statut_cnas === 'non_declare' ? 'Non déclaré' : ($paie->statut_cnas === 'declare' ? 'Déclaré' : 'Payé') }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($paie->total_cotisations_cnas + $paie->total_charges_patronales, 2, ',', ' ') }} DZD</td>
            </tr>
            <tr>
                <td>Déclaration IRG</td>
                <td class="text-center">
                    <span class="status-badge {{ $paie->statut_irg === 'paye' ? 'status-done' : 'status-pending' }}">
                        {{ $paie->statut_irg === 'non_declare' ? 'Non déclaré' : ($paie->statut_irg === 'declare' ? 'Déclaré' : 'Payé') }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($paie->total_irg, 2, ',', ' ') }} DZD</td>
            </tr>
        </table>
    </div>
    
    <!-- Liste des employés -->
    <div class="section">
        <h3 class="section-title">👥 Détail par Employé (Calcul basé sur les présences)</h3>
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th class="text-center">Présences</th>
                    <th class="text-right">Base</th>
                    <th class="text-right">Brut</th>
                    <th class="text-right">CNAS</th>
                    <th class="text-right">IRG</th>
                    <th class="text-right">Net</th>
                    <th class="text-center">Remise</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paie->fichesPaie as $fiche)
                @php
                    $joursOuvres = $fiche->getJoursOuvresDuMois();
                    $joursPayes = $fiche->jours_travailles + $fiche->jours_justifies;
                @endphp
                <tr>
                    <td>{{ $fiche->employe->matricule }}</td>
                    <td>{{ $fiche->employe->prenom }} {{ $fiche->employe->nom }}</td>
                    <td class="text-center">
                        <span class="{{ $joursPayes < $joursOuvres ? 'text-red' : 'text-green' }}">
                            {{ $joursPayes }}/{{ $joursOuvres }}j
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($fiche->salaire_base, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($fiche->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-right text-red">{{ number_format($fiche->cotisation_cnss, 0, ',', ' ') }}</td>
                    <td class="text-right text-red">{{ number_format($fiche->ir, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold text-blue">{{ number_format($fiche->salaire_net, 0, ',', ' ') }}</td>
                    <td class="text-center">
                        <span class="status-badge {{ $fiche->statut_reception !== 'en_attente' ? 'status-done' : 'status-pending' }}">
                            {{ $fiche->statut_reception === 'en_attente' ? '⏳' : '✓' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="3"><strong>TOTAUX</strong></td>
                    <td class="text-right">{{ number_format($paie->total_salaires_base, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold">{{ number_format($paie->total_brut, 0, ',', ' ') }}</td>
                    <td class="text-right text-red">{{ number_format($paie->total_cotisations_cnas, 0, ',', ' ') }}</td>
                    <td class="text-right text-red">{{ number_format($paie->total_irg, 0, ',', ' ') }}</td>
                    <td class="text-right font-bold text-blue">{{ number_format($paie->total_net, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <p style="margin-top: 10px; font-size: 9px; color: #666;">
            * Présences: jours travaillés + justifiés / jours ouvrés du mois (Dim-Jeu). Salaire calculé au prorata des présences.
        </p>
    </div>
    
    <!-- Signatures -->
    <div class="signature-area">
        <div class="signature-box">
            <p>Établi par:</p>
            <div class="signature-line">Responsable RH</div>
        </div>
        <div class="signature-box">
            <p>Approuvé par:</p>
            <div class="signature-line">Direction</div>
        </div>
    </div>
    
    <div class="footer">
        <span>Document généré le {{ now()->format('d/m/Y à H:i') }}</span>
        <span>{{ $paie->reference }} - Page 1/1</span>
    </div>
</body>
</html>
