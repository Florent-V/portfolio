import { asNum, eur, kwh, pct } from './format'

// Tarifs réglementés EDF (juillet 2026, compteur 6 kVA) pré-remplis par défaut :
// Base 0,1940 €/kWh — HP 0,2065 €/kWh — HC 0,1579 €/kWh — abonnement ~187,80 €/an soit 15,65 €/mois.
export const DEFAULTS = {
  aboBase: 15.65,
  aboHPHC: 15.65,
  prixBase: 0.194,
  prixHP: 0.2065,
  prixHC: 0.1579,
}

const neutral = (badgeText, sub, extra = {}) => ({
  badgeClass: 'neutral',
  badgeText,
  figure: '—',
  sub,
  formula: '',
  ...extra,
})

// Mode 1 : part minimale de consommation en HC pour que HP/HC batte Base.
export function seuilResult(form) {
  const aboBase = asNum(form.aboBase),
    aboHPHC = asNum(form.aboHPHC),
    C = asNum(form.conso)
  const prixBase = asNum(form.prixBase),
    prixHP = asNum(form.prixHP),
    prixHC = asNum(form.prixHC)
  const gaugeDefaults = { markerLeft: '0%', gaugeBg: '' }

  if (
    [aboBase, aboHPHC, C, prixBase, prixHP, prixHC].some((v) => !isFinite(v))
  ) {
    return neutral(
      'Champs manquants',
      'Renseignez tous les champs pour lancer le calcul.',
      gaugeDefaults,
    )
  }
  if (C <= 0) {
    return neutral(
      'Valeur invalide',
      'La consommation de référence doit être un nombre positif.',
      gaugeDefaults,
    )
  }
  if ([aboBase, aboHPHC, prixBase, prixHP, prixHC].some((v) => v < 0)) {
    return neutral(
      'Valeur invalide',
      'Les abonnements et prix du kWh doivent être positifs.',
      gaugeDefaults,
    )
  }
  const denom = prixHC - prixHP
  if (denom === 0) {
    return neutral(
      'Non déterminable',
      'Le tarif heures pleines et le tarif heures creuses sont identiques : comparez directement les abonnements et le tarif Base.',
      gaugeDefaults,
    )
  }

  const x = (aboBase - aboHPHC + C * (prixBase - prixHP)) / (C * denom)
  const clamped = Math.max(0, Math.min(100, x * 100))
  const common = {
    gaugeBg: `linear-gradient(to right, var(--color-error) 0%, var(--color-error) ${clamped}%, var(--color-success) ${clamped}%, var(--color-success) 100%)`,
    formula: `x = (aboBase − aboHPHC + C×(prixBase − prixHP)) / (C×(prixHC − prixHP))
  = (${eur(aboBase)} − ${eur(aboHPHC)} + ${C}×(${prixBase} − ${prixHP})) / (${C}×(${prixHC} − ${prixHP}))
  = ${(x * 100).toFixed(1)} %  (C = ${C} kWh/mois pris comme référence)`,
  }

  if (x <= 0) {
    return {
      ...common,
      badgeClass: 'good',
      badgeText: 'Toujours rentable',
      figure: 'Avantageux dès 0 %',
      sub: "Même sans consommation en heures creuses, l'offre HP/HC reste moins chère que l'offre Base à cette consommation de référence.",
      markerLeft: '0%',
    }
  }
  if (x >= 1) {
    return {
      ...common,
      badgeClass: 'bad',
      badgeText: 'Jamais rentable',
      figure: 'Même à 100 %, ça ne suffit pas',
      sub: "Même en mettant toute votre consommation en heures creuses, l'offre HP/HC resterait plus chère que Base à cette consommation de référence.",
      markerLeft: '100%',
    }
  }
  return {
    ...common,
    badgeClass: 'good',
    badgeText: 'Seuil calculé',
    figure: pct(x) + ' en heures creuses',
    sub: `Au-delà de ${pct(x)} de votre consommation en heures creuses (pour ${kwh(C)}/mois), l'offre HP/HC devient plus avantageuse que Base.`,
    markerLeft: x * 100 + '%',
  }
}

// Mode 2 : comparaison du coût réel HP/HC vs Base sur une même période.
export function situationResult(form) {
  const aboBase = asNum(form.aboBase),
    aboHPHC = asNum(form.aboHPHC)
  const prixBase = asNum(form.prixBase),
    prixHP = asNum(form.prixHP),
    prixHC = asNum(form.prixHC)
  const consoHP = asNum(form.consoHP),
    consoHC = asNum(form.consoHC)
  const all = [aboBase, aboHPHC, prixBase, prixHP, prixHC, consoHP, consoHC]

  if (all.some((v) => !isFinite(v))) {
    return neutral(
      'Champs manquants',
      'Renseignez tous les champs pour lancer le calcul.',
      { bars: false },
    )
  }
  if (all.some((v) => v < 0)) {
    return neutral(
      'Valeur invalide',
      'Les abonnements, prix et consommations doivent être positifs.',
      { bars: false },
    )
  }

  const costHPHC = aboHPHC + consoHP * prixHP + consoHC * prixHC
  const costBase = aboBase + (consoHP + consoHC) * prixBase
  const diff = costBase - costHPHC // positive = HPHC cheaper
  const maxCost = Math.max(costHPHC, costBase, 0.01)

  const common = {
    bars: true,
    costBase,
    costHPHC,
    barBaseWidth: (costBase / maxCost) * 100 + '%',
    barHPHCWidth: (costHPHC / maxCost) * 100 + '%',
    formula: `Coût HP/HC = aboHPHC + consoHP×prixHP + consoHC×prixHC
           = ${eur(aboHPHC)} + ${consoHP}×${prixHP} + ${consoHC}×${prixHC} = ${eur(costHPHC)}
Coût Base  = aboBase + (consoHP+consoHC)×prixBase
           = ${eur(aboBase)} + ${consoHP + consoHC}×${prixBase} = ${eur(costBase)}`,
  }

  if (Math.abs(diff) < 0.01) {
    return {
      ...common,
      badgeClass: 'neutral',
      badgeText: 'Équivalent',
      figure: 'Aucune différence notable',
      sub: 'Les deux offres reviennent quasiment au même prix sur cette période.',
    }
  }
  if (diff > 0) {
    return {
      ...common,
      badgeClass: 'good',
      badgeText: 'HP/HC rentable',
      figure: `Vous économisez ${eur(diff)} / mois`,
      sub: `Soit environ ${eur(diff * 12)} sur l'année par rapport à l'offre Base.`,
    }
  }
  return {
    ...common,
    badgeClass: 'bad',
    badgeText: 'Base plus avantageuse',
    figure: `Vous perdez ${eur(-diff)} / mois`,
    sub: `Vous auriez payé ${eur(-diff * 12)} de moins sur l'année en restant en Base.`,
  }
}

const SKIP_REASON_LABELS = {
  cellManquante: 'Cellule vide ou colonne manquante sur la ligne',
  dateInvalide: 'Date/heure illisible (format inattendu)',
  nombreInvalide: 'Valeur de consommation/puissance non numérique',
  dureeInvalide:
    "Durée Fin − Début nulle ou négative (souvent le changement d'heure été/hiver)",
}

function buildSkipDetail({ usedRows, skippedRows, skipReasons, skipExamples }) {
  if (skippedRows === 0) return ''
  const breakdown = Object.entries(skipReasons)
    .filter(([, n]) => n > 0)
    .map(([k, n]) => `  ${SKIP_REASON_LABELS[k]} : ${n} ligne(s)`)
    .join('\n')
  const examples = skipExamples
    .map((ex) => `  [${SKIP_REASON_LABELS[ex.reason]}] ${ex.raw}`)
    .join('\n')
  return `${skippedRows} ligne(s) sur ${usedRows + skippedRows} n'ont pas pu être exploitées :
${breakdown}

Exemples de lignes ignorées (contenu brut) :
${examples || '(aucun exemple disponible)'}`
}

// Mode 3 : verdict complet à partir d'une analyse CSV (voir csv.js) et des tarifs.
export function csvResult(analysis, tarifs, { rangesText, format }) {
  const { consoHP, consoHC, usedRows, skippedRows, minTime, maxTime } = analysis
  const { aboBase, aboHPHC, prixBase, prixHP, prixHC } = tarifs

  const nbJoursAvecDonnees = analysis.daysWithData || 1
  // L'abonnement court sur toute la période couverte par le relevé (du premier au dernier point),
  // même si certaines journées n'ont pas de données (coupure Linky) : on prorate sur cette étendue
  // calendaire réelle, pas sur le nombre de jours qui ont effectivement des mesures.
  const nbJours =
    minTime !== null
      ? Math.round((maxTime - minTime) / 86400000) + 1
      : nbJoursAvecDonnees
  const moisEquiv = nbJours / 30.437
  const aboBaseProrata = aboBase * moisEquiv
  const aboHPHCProrata = aboHPHC * moisEquiv
  const energieBase = (consoHP + consoHC) * prixBase
  const energieHPHC = consoHP * prixHP + consoHC * prixHC
  const costBase = aboBaseProrata + energieBase
  const costHPHC = aboHPHCProrata + energieHPHC
  const diff = costBase - costHPHC

  const rows = [
    {
      label: 'Lignes exploitées',
      span: true,
      value: `${usedRows}${skippedRows ? ` (${skippedRows} ignorées)` : ''}`,
    },
    {
      label: 'Jours couverts (du premier au dernier relevé)',
      span: true,
      value: `${nbJours} jours (~${moisEquiv.toFixed(2)} mois)`,
    },
    {
      label: 'Dont jours avec au moins une mesure',
      span: true,
      value: `${nbJoursAvecDonnees} jours`,
    },
    { label: 'Conso. heures pleines', span: true, value: kwh(consoHP) },
    { label: 'Conso. heures creuses', span: true, value: kwh(consoHC) },
    {
      label: 'Abonnement (proraté)',
      base: eur(aboBaseProrata),
      hphc: eur(aboHPHCProrata),
    },
    { label: 'Coût énergie', base: eur(energieBase), hphc: eur(energieHPHC) },
    {
      label: 'Total Base',
      base: eur(costBase),
      hphc: '',
      win: costBase < costHPHC,
    },
    {
      label: 'Total HP/HC',
      base: '',
      hphc: eur(costHPHC),
      win: costHPHC < costBase,
    },
  ]

  const formula = `Format : ${format === 'startend' ? 'Début/Fin + puissance (kW), energie = puissance x duree de l intervalle' : 'Date + consommation (kWh)'}
Plages heures creuses : ${rangesText || '(aucune)'}
Abonnement proraté = abonnement mensuel × (jours couverts / 30,437)
Coût Base  = ${eur(aboBaseProrata)} + ${kwh(consoHP + consoHC)}×${prixBase} = ${eur(costBase)}
Coût HP/HC = ${eur(aboHPHCProrata)} + ${kwh(consoHP)}×${prixHP} + ${kwh(consoHC)}×${prixHC} = ${eur(costHPHC)}`

  let verdict
  if (Math.abs(diff) < 0.01) {
    verdict = {
      badgeClass: 'neutral',
      badgeText: 'Équivalent',
      figure: 'Aucune différence notable',
      sub: `Sur les ${nbJours} jours analysés, les deux offres reviennent au même prix.`,
    }
  } else if (diff > 0) {
    verdict = {
      badgeClass: 'good',
      badgeText: 'HP/HC plus intéressante',
      figure: `L'offre HP/HC revient ${eur(diff)} moins cher`,
      sub: `Sur les ${nbJours} jours analysés et avec ces plages horaires, l'offre HP/HC aurait coûté ${eur(costHPHC)} contre ${eur(costBase)} en Base.`,
    }
  } else {
    verdict = {
      badgeClass: 'good',
      badgeText: 'Base plus intéressante',
      figure: `L'offre Base revient ${eur(-diff)} moins cher sur la période de relevé`,
      sub: `Sur les ${nbJours} jours analysés et avec ces plages horaires, l'offre Base aurait coûté ${eur(costBase)} contre ${eur(costHPHC)} en HP/HC.`,
    }
  }

  return { ...verdict, rows, skipDetail: buildSkipDetail(analysis), formula }
}

export const csvNeutralResult = (badgeText, sub) =>
  neutral(badgeText, sub, { rows: [], skipDetail: '' })
