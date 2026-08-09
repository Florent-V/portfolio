import { asNum } from './format'

// Valeurs de démo : Tesla Model 3 Propulsion vs Peugeot 508 PureTech 130
// (tarifs France 2026, recharge majoritairement à domicile).
export const DEMO = {
  evConso: 14.4, // kWh/100 km, usage mixte
  evPrixDomicile: 0.22, // €/kWh à domicile
  evPrixBorne: 0.45, // €/kWh en borne rapide
  evPartDomicile: 90, // % des recharges faites à domicile
  iceConso: 6.3, // L/100 km, usage mixte
  icePrix: 1.9, // €/L SP95-E10
  kmAn: 15000, // km/an, moyenne française
  evAchat: 39990,
  evAide: 0, // bonus écologique / prime déduite du prix
  evBorne: 1200, // wallbox installée
  evEntretien: 350, // €/an
  evAssurance: 950, // €/an
  iceAchat: 39900,
  iceEntretien: 750, // €/an (révisions, vidanges, distribution…)
  iceAssurance: 800, // €/an
}

export const KM_STEPS = [5000, 10000, 15000, 20000, 25000, 30000, 40000]
export const HORIZON = 15 // années simulées

const eur0 = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
      })
    : '—'

const eurDec = (n, dec) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: dec,
        maximumFractionDigits: dec,
      })
    : '—'

export const km = (n) => (isFinite(n) ? n.toLocaleString('fr-FR') + ' km' : '—')

const num = (form, key) => asNum(form[key]) || 0

// Prix du kWh pondéré domicile / borne publique.
export const blendedElecPrice = (form) => {
  const part = Math.min(Math.max(num(form, 'evPartDomicile'), 0), 100) / 100
  return (
    part * num(form, 'evPrixDomicile') + (1 - part) * num(form, 'evPrixBorne')
  )
}

export const usageResult = (form) => {
  const elecPrice = blendedElecPrice(form)
  const evPar100 = num(form, 'evConso') * elecPrice
  const icePar100 = num(form, 'iceConso') * num(form, 'icePrix')
  const ecart100 = icePar100 - evPar100
  const kmAn = num(form, 'kmAn')
  const ecoAn = (ecart100 * kmAn) / 100

  const rows = KM_STEPS.map((k) => {
    const ev = (evPar100 * k) / 100
    const ice = (icePar100 * k) / 100
    return { km: k, ev, ice, diff: ice - ev }
  })

  return {
    elecPrice,
    evPar100,
    icePar100,
    evParKm: evPar100 / 100,
    iceParKm: icePar100 / 100,
    ecart100,
    ecoAn,
    verdict: {
      badgeClass: 'good',
      badgeText:
        ecoAn >= 0 ? 'Électrique plus économe' : 'Thermique plus économe',
      figure: `${eur0(Math.abs(ecoAn))} / an`,
      sub:
        ecoAn >= 0
          ? `d'écart d'énergie en faveur de l'électrique sur ${km(kmAn)} par an (kWh moyen : ${eurDec(elecPrice, 3)}).`
          : `d'écart d'énergie en faveur du thermique sur ${km(kmAn)} par an (kWh moyen : ${eurDec(elecPrice, 3)}).`,
    },
    rows,
  }
}

export const tcoResult = (form) => {
  const { evPar100, icePar100 } = usageResult(form)
  const kmAn = num(form, 'kmAn')

  const evAnnuel =
    (evPar100 * kmAn) / 100 +
    num(form, 'evEntretien') +
    num(form, 'evAssurance')
  const iceAnnuel =
    (icePar100 * kmAn) / 100 +
    num(form, 'iceEntretien') +
    num(form, 'iceAssurance')
  const evFixe =
    num(form, 'evAchat') - num(form, 'evAide') + num(form, 'evBorne')
  const iceFixe = num(form, 'iceAchat')

  const rows = []
  for (let y = 0; y <= HORIZON; y++) {
    rows.push({
      year: y,
      km: y * kmAn,
      ev: evFixe + evAnnuel * y,
      ice: iceFixe + iceAnnuel * y,
      diff: iceFixe + iceAnnuel * y - (evFixe + evAnnuel * y),
    })
  }

  // Point d'inversion : evFixe + evAnnuel·t = iceFixe + iceAnnuel·t
  const surcout = evFixe - iceFixe
  const gainAnnuel = iceAnnuel - evAnnuel

  let breakeven
  if (surcout <= 0 && gainAnnuel >= 0) {
    breakeven = {
      years: 0,
      value: "Dès l'achat",
      sub: "L'électrique est plus économique dès le premier kilomètre : coûts fixes inférieurs et usage moins cher.",
    }
  } else if (surcout > 0 && gainAnnuel > 0) {
    const years = surcout / gainAnnuel
    breakeven =
      years <= HORIZON * 2
        ? {
            years,
            value: `${years.toLocaleString('fr-FR', { maximumFractionDigits: 1 })} ans · ${km(Math.round((years * kmAn) / 100) * 100)}`,
            sub: `Le surcoût initial de ${eur0(surcout)} est absorbé par une économie de ${eur0(gainAnnuel)} par an (énergie + entretien + assurance). Au-delà, chaque année roulée est un gain net.`,
          }
        : {
            years: null,
            value: 'Hors période simulée',
            sub: `Le surcoût initial de ${eur0(surcout)} est trop important pour être absorbé en ${HORIZON * 2} ans avec une économie de ${eur0(gainAnnuel)} par an.`,
          }
  } else if (surcout <= 0 && gainAnnuel < 0) {
    const years = surcout / gainAnnuel // les deux négatifs → t positif
    breakeven = {
      years: null,
      value: `Avantage jusqu'à ${years.toLocaleString('fr-FR', { maximumFractionDigits: 1 })} ans`,
      sub: `L'électrique est moins chère à l'achat mais plus chère à l'usage : son avance de ${eur0(-surcout)} s'érode de ${eur0(-gainAnnuel)} par an.`,
    }
  } else {
    breakeven = {
      years: null,
      value: 'Jamais atteint',
      sub: "Avec ces valeurs, l'électrique coûte plus cher à l'achat et à l'usage : l'écart ne se referme pas.",
    }
  }

  return {
    evAnnuel,
    iceAnnuel,
    evFixe,
    iceFixe,
    surcout,
    gainAnnuel,
    rows,
    breakeven,
  }
}
