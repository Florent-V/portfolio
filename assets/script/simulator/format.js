export const eur = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    : '—'

export const kwh = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' kWh'
    : '—'

export const pct = (n) =>
  isFinite(n)
    ? (n * 100).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' %'
    : '—'

export const asNum = (v) =>
  v === '' || v === null || v === undefined ? NaN : Number(v)
