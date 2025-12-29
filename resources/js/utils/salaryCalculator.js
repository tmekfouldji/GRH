/**
 * Unified Salary Calculator for Algerian Payroll System
 * 
 * Rules:
 * - CNAS: 9% of gross salary (employee contribution)
 * - IRG: Progressive tax with 40% abatement
 * - Exemption: SNI <= 30,000 DZD
 * - Degressive zone: 30,001 - 35,000 DZD
 */

/**
 * Calculate IRG (Income Tax) based on SNI (Net Taxable Income)
 */
export function calculateIRG(sni) {
    // Exemption for SNI <= 30,000
    if (sni <= 30000) {
        return 0;
    }
    
    // Calculate gross IRG using progressive brackets
    const irgBrut = calculateIRGBrackets(sni);
    
    // Degressive zone: 30,001 - 35,000
    if (sni > 30000 && sni < 35000) {
        const irgWithAbatement = applyAbatement40(irgBrut);
        // Special formula: IRG = IRG(abattu) × (137/51) − (27925/8)
        const irgFinal = (irgWithAbatement * (137 / 51)) - (27925 / 8);
        return Math.max(0, Math.round(irgFinal));
    }
    
    // Standard 40% abatement for SNI >= 35,000
    return Math.round(applyAbatement40(irgBrut));
}

/**
 * Calculate gross IRG using progressive monthly brackets
 */
function calculateIRGBrackets(sni) {
    let irg = 0;
    
    if (sni <= 20000) return 0;
    
    // 23%: 20,001 - 40,000
    if (sni > 20000) {
        irg += (Math.min(sni, 40000) - 20000) * 0.23;
    }
    
    // 27%: 40,001 - 80,000
    if (sni > 40000) {
        irg += (Math.min(sni, 80000) - 40000) * 0.27;
    }
    
    // 30%: 80,001 - 160,000
    if (sni > 80000) {
        irg += (Math.min(sni, 160000) - 80000) * 0.30;
    }
    
    // 33%: 160,001 - 320,000
    if (sni > 160000) {
        irg += (Math.min(sni, 320000) - 160000) * 0.33;
    }
    
    // 35%: > 320,000
    if (sni > 320000) {
        irg += (sni - 320000) * 0.35;
    }
    
    return irg;
}

/**
 * Apply 40% abatement with min 1000 and max 1500
 */
function applyAbatement40(irgBrut) {
    const abatement = Math.min(Math.max(irgBrut * 0.40, 1000), 1500);
    return Math.max(0, irgBrut - abatement);
}

/**
 * Calculate salary from GROSS (Brut -> Net)
 */
export function calculateFromBrut(salaireBrut, options = {}) {
    const {
        primeTransport = 0,
        primePanier = 0,
        autresPrimes = 0,
        autresDeductions = 0,
    } = options;
    
    // Total gross = base + primes
    const totalBrut = salaireBrut + primeTransport + primePanier + autresPrimes;
    
    // CNAS 9%
    const cotisationCNAS = Math.round(totalBrut * 0.09);
    
    // SNI (Net Taxable Income)
    const sni = totalBrut - cotisationCNAS;
    
    // IRG
    const irg = calculateIRG(sni);
    
    // Total deductions
    const totalDeductions = cotisationCNAS + irg + autresDeductions;
    
    // Net salary
    const salaireNet = totalBrut - totalDeductions;
    
    return {
        salaireBrut: Math.round(salaireBrut),
        primeTransport: Math.round(primeTransport),
        primePanier: Math.round(primePanier),
        autresPrimes: Math.round(autresPrimes),
        totalBrut: Math.round(totalBrut),
        cotisationCNAS: Math.round(cotisationCNAS),
        sni: Math.round(sni),
        irg: Math.round(irg),
        autresDeductions: Math.round(autresDeductions),
        totalDeductions: Math.round(totalDeductions),
        salaireNet: Math.round(salaireNet),
    };
}

/**
 * Calculate salary from NET (Net -> Brut) using binary search
 * Finds the BASE salary that, when combined with primes, gives the target net
 */
export function calculateFromNet(salaireNetCible, options = {}) {
    const {
        primeTransport = 0,
        primePanier = 0,
        autresPrimes = 0,
        autresDeductions = 0,
    } = options;
    
    const totalPrimes = primeTransport + primePanier + autresPrimes;
    
    // Binary search to find BASE salary that gives target net
    // Low bound must account for primes (base salary can be lower than target net)
    let low = Math.max(0, salaireNetCible - totalPrimes - (salaireNetCible * 0.3));
    let high = salaireNetCible * 2;
    
    let bestResult = null;
    let bestDiff = Infinity;
    
    for (let i = 0; i < 100; i++) {
        const mid = Math.round((low + high) / 2);
        
        const result = calculateFromBrut(mid, {
            primeTransport,
            primePanier,
            autresPrimes,
            autresDeductions,
        });
        
        const diff = Math.abs(result.salaireNet - salaireNetCible);
        
        // Track best result
        if (diff < bestDiff) {
            bestDiff = diff;
            bestResult = result;
        }
        
        // Found exact match (within 1 DZD)
        if (diff <= 1) {
            return {
                ...result,
                salaireNetCible,
            };
        }
        
        // Convergence check
        if (high - low <= 1) {
            break;
        }
        
        if (result.salaireNet < salaireNetCible) {
            low = mid;
        } else {
            high = mid;
        }
    }
    
    // Return best result found
    return {
        ...bestResult,
        salaireNetCible,
    };
}

/**
 * Format number as currency
 */
export function formatMoney(value) {
    return new Intl.NumberFormat('fr-DZ').format(Math.round(value || 0));
}

/**
 * Calculate employer total cost (gross + 25% charges)
 */
export function calculateEmployerCost(salaireBrut) {
    const chargesPatronales = Math.round(salaireBrut * 0.25);
    return {
        salaireBrut: Math.round(salaireBrut),
        chargesPatronales,
        coutTotal: Math.round(salaireBrut) + chargesPatronales,
    };
}
