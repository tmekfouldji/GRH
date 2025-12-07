/**
 * Utilitaires de formatage pour l'application
 */

/**
 * Formater une date en français
 * @param {string|Date} date 
 * @param {string} format - 'short', 'medium', 'long', 'full'
 */
export function formatDate(date, format = 'medium') {
    if (!date) return '-';
    
    const d = new Date(date);
    if (isNaN(d.getTime())) return '-';

    const options = {
        short: { day: '2-digit', month: '2-digit', year: 'numeric' },
        medium: { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' },
        long: { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' },
        full: { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' },
        dayMonth: { day: '2-digit', month: 'short' },
    };

    return d.toLocaleDateString('fr-FR', options[format] || options.medium);
}

/**
 * Formater une heure
 * @param {string|Date} datetime 
 */
export function formatTime(datetime) {
    if (!datetime) return '-';
    
    // Si c'est juste une heure (HH:mm:ss)
    if (typeof datetime === 'string' && datetime.match(/^\d{2}:\d{2}/)) {
        return datetime.substring(0, 5);
    }
    
    const d = new Date(datetime);
    if (isNaN(d.getTime())) return '-';
    
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

/**
 * Formater une date et heure
 * @param {string|Date} datetime 
 */
export function formatDateTime(datetime) {
    if (!datetime) return '-';
    
    const d = new Date(datetime);
    if (isNaN(d.getTime())) return '-';
    
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formater un montant en dinars algériens
 * @param {number} amount 
 */
export function formatMoney(amount) {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('fr-DZ', {
        style: 'currency',
        currency: 'DZD',
        minimumFractionDigits: 2
    }).format(amount);
}

/**
 * Formater un nombre
 * @param {number} num 
 * @param {number} decimals 
 */
export function formatNumber(num, decimals = 2) {
    if (num === null || num === undefined) return '-';
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(num);
}

/**
 * Obtenir les initiales d'un nom
 * @param {object} person - {prenom, nom}
 */
export function getInitials(person) {
    if (!person) return '?';
    const p = person.prenom?.[0] || '';
    const n = person.nom?.[0] || '';
    return (p + n).toUpperCase() || '?';
}

/**
 * Formater une durée en heures
 * @param {number} hours 
 */
export function formatHours(hours) {
    if (hours === null || hours === undefined) return '-';
    return `${parseFloat(hours).toFixed(1)}h`;
}

/**
 * Nom du mois en français
 * @param {number} month - 1-12
 */
export function getMonthName(month) {
    const months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    return months[month - 1] || '';
}
