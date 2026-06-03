export function fcfa(amount: number | string | null | undefined): string {
    const n = typeof amount === 'string' ? parseFloat(amount) : (amount ?? 0);
    if (!isFinite(n)) return '0 FCFA';
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(n)) + ' FCFA';
}

export function fdate(d: string | Date | null | undefined): string {
    if (!d) return '';
    const date = typeof d === 'string' ? new Date(d) : d;
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
}

export function fdatetime(d: string | Date | null | undefined): string {
    if (!d) return '';
    const date = typeof d === 'string' ? new Date(d) : d;
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date);
}

export const PAYMENT_METHODS: Array<{ value: string; label: string }> = [
    { value: 'cash', label: 'Espèces' },
    { value: 'om', label: 'Orange Money' },
    { value: 'momo', label: 'MTN MoMo' },
    { value: 'wave', label: 'Wave' },
    { value: 'card', label: 'Carte bancaire' },
    { value: 'bank', label: 'Virement bancaire' },
    { value: 'other', label: 'Autre' },
];

export function paymentLabel(value: string): string {
    return PAYMENT_METHODS.find(p => p.value === value)?.label ?? value;
}
