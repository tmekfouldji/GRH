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

<div class="employee-section">
    <h3>Employé</h3>
    <div class="employee-grid">
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
    <div class="presence-grid">
        <div><span>Jours ouvrés:</span> <span>{{ $fiche->jours_ouvres ?? '-' }}</span></div>
        <div><span>Jours travaillés:</span> <span>{{ $fiche->jours_travailles ?? '-' }}</span></div>
        <div><span>H. Travaillées:</span> <span>{{ number_format($fiche->heures_travaillees ?? 0, 1) }}h</span></div>
        <div><span>H. Sup:</span> <span>{{ number_format($fiche->heures_supplementaires ?? 0, 1) }}h</span></div>
        @if(($fiche->mode_remuneration_snapshot ?? 'salaire') === 'piece')
        <div><span>Pièces:</span> <span>{{ $fiche->pieces_fabriquees ?? 0 }}</span></div>
        <div><span>Prime/pièce:</span> <span>{{ number_format($fiche->prime_par_piece_snapshot ?? 0, 2, ',', ' ') }}</span></div>
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
