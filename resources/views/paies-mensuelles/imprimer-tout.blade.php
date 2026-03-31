<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiches de Paie - {{ $paie->periode }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 5mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7px; line-height: 1.2; }

        .fiche-paie {
            width: 100%;
            padding: 4mm;
            border: 0.5px solid #999;
            page-break-inside: avoid;
            margin-bottom: 2mm;
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

        .info-row {
            display: flex;
            gap: 3mm;
            margin-bottom: 3px;
        }

        .employee-section {
            flex: 1;
            background: #f8fafc;
            padding: 3px;
            border-radius: 2px;
        }
        .employee-section h3 { font-size: 6.5px; color: #1e40af; margin-bottom: 2px; text-transform: uppercase; }

        .presence-section {
            flex: 0 0 auto;
            width: 30%;
            background: #f0fdf4;
            padding: 3px;
            border-radius: 2px;
        }
        .presence-section h3 { font-size: 6.5px; color: #166534; margin-bottom: 2px; text-transform: uppercase; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0px 6px; font-size: 6px; }
        .info-grid div { display: flex; justify-content: space-between; gap: 3px; }
        .info-grid span:first-child { color: #666; white-space: nowrap; }
        .info-grid span:last-child { font-weight: bold; text-align: right; }

        .salary-row {
            display: flex;
            gap: 3mm;
            margin-top: 3px;
        }

        .salary-table { width: 100%; border-collapse: collapse; font-size: 6px; flex: 1; }
        .salary-table th, .salary-table td { padding: 1px 3px; text-align: left; border-bottom: 0.5px solid #e5e7eb; }
        .salary-table th { background: #f1f5f9; font-size: 6px; text-transform: uppercase; color: #64748b; }
        .salary-table .amount { text-align: right; font-family: monospace; font-size: 6px; }
        .salary-table .gain { color: #16a34a; }
        .salary-table .deduction { color: #dc2626; }
        .salary-table .section-header td { font-weight: bold; font-size: 6px; padding: 1px 3px; }
        .salary-table .subtotal { background: #f8fafc; font-weight: bold; }
        .salary-table .total { background: #1e40af; color: white; font-weight: bold; font-size: 7px; }

        .signature-section {
            flex: 0 0 auto;
            width: 28%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 4mm;
            padding-left: 3mm;
            border-left: 0.5px solid #e5e7eb;
        }
        .signature-box { text-align: center; }
        .signature-box p { font-size: 5.5px; color: #666; margin-bottom: 8px; }
        .signature-line { border-top: 0.5px solid #333; padding-top: 2px; font-size: 5px; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
        }

        @media screen {
            body { padding: 10px; background: #eee; }
            .fiche-paie { background: white; margin-bottom: 6px; }
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

    @foreach($paie->fichesPaie as $fiche)
    <div class="fiche-paie">
        <div class="header">
            <div class="company-info">
                <h1>ENTREPRISE TEXTILE</h1>
                <p>Adresse de l'entreprise | Tél: +213 XX XX XX XX</p>
            </div>
            <div class="fiche-info">
                <h2>BULLETIN DE PAIE</h2>
                <p>{{ $paie->periode }} | Réf: {{ $fiche->id }}</p>
            </div>
        </div>

        <div class="info-row">
            <div class="employee-section">
                <h3>Employé</h3>
                <div class="info-grid">
                    <div><span>Matricule:</span> <span>{{ $fiche->employe->matricule }}</span></div>
                    <div><span>Nom:</span> <span>{{ $fiche->employe->prenom }} {{ $fiche->employe->nom }}</span></div>
                    <div><span>Poste:</span> <span>{{ $fiche->employe->poste ?? '-' }}</span></div>
                    <div><span>Département:</span> <span>{{ $fiche->employe->departement ?? '-' }}</span></div>
                    <div><span>N° CNAS:</span> <span>{{ $fiche->employe->numero_cnas ?? $fiche->employe->cnss ?? '-' }}</span></div>
                    <div><span>Mode:</span> <span>{{ ucfirst($fiche->mode_remuneration_snapshot ?? 'salaire') }}</span></div>
                </div>
            </div>
            <div class="presence-section">
                <h3>Présence</h3>
                <div class="info-grid" style="grid-template-columns: 1fr;">
                    <div><span>Jours ouvrés:</span> <span>{{ $fiche->jours_ouvres ?? '-' }}</span></div>
                    <div><span>Jours travaillés:</span> <span>{{ $fiche->jours_travailles ?? '-' }}</span></div>
                    <div><span>H. Sup:</span> <span>{{ number_format($fiche->heures_supplementaires ?? 0, 1) }}h</span></div>
                    @if(($fiche->mode_remuneration_snapshot ?? 'salaire') === 'piece')
                    <div><span>Pièces:</span> <span>{{ $fiche->pieces_fabriquees ?? 0 }}</span></div>
                    <div><span>Prime/pièce:</span> <span>{{ number_format($fiche->prime_par_piece_snapshot ?? 0, 2, ',', ' ') }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="salary-row">
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th class="amount">Montant (DZD)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="section-header"><td colspan="2" style="background:#e0f2fe;color:#0369a1;">GAINS</td></tr>
                    <tr>
                        <td>Salaire de base</td>
                        <td class="amount">{{ number_format($fiche->salaire_base, 2, ',', ' ') }}</td>
                    </tr>
                    @if(($fiche->montant_heures_supplementaires ?? 0) > 0)
                    <tr>
                        <td>H. Sup ({{ number_format($fiche->heures_supplementaires ?? 0, 1) }}h × 150%)</td>
                        <td class="amount gain">+ {{ number_format($fiche->montant_heures_supplementaires, 2, ',', ' ') }}</td>
                    </tr>
                    @endif
                    @if($fiche->prime_transport > 0)
                    <tr>
                        <td>Prime transport</td>
                        <td class="amount gain">+ {{ number_format($fiche->prime_transport, 2, ',', ' ') }}</td>
                    </tr>
                    @endif
                    @if($fiche->prime_rendement > 0)
                    <tr>
                        <td>Prime rendement @if(($fiche->mode_remuneration_snapshot ?? 'salaire') === 'piece')({{ $fiche->pieces_fabriquees ?? 0 }} pcs)@endif</td>
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

                    <tr class="section-header"><td colspan="2" style="background:#fef2f2;color:#dc2626;">RETENUES</td></tr>
                    <tr>
                        <td>CNAS (9%)</td>
                        <td class="amount deduction">- {{ number_format($fiche->cotisation_cnss, 2, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td>IRG</td>
                        <td class="amount deduction">- {{ number_format($fiche->ir, 2, ',', ' ') }}</td>
                    </tr>
                    @if(($fiche->deduction_retard ?? 0) > 0)
                    <tr>
                        <td>Pénalités retards</td>
                        <td class="amount deduction">- {{ number_format($fiche->deduction_retard, 2, ',', ' ') }}</td>
                    </tr>
                    @endif
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
                    <p>Signature employeur</p>
                    <div class="signature-line">Date: ___/___/______</div>
                </div>
                <div class="signature-box">
                    <p>Signature employé</p>
                    <div class="signature-line">Date: ___/___/______</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
