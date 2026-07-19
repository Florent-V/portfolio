<script setup>
import { onMounted, reactive, ref } from 'vue'

// Tarifs réglementés EDF (juillet 2026, compteur 6 kVA) pré-remplis par défaut :
// Base 0,1940 €/kWh — HP 0,2065 €/kWh — HC 0,1579 €/kWh — abonnement ~187,80 €/an soit 15,65 €/mois.
const DEFAULTS = {
  aboBase: 15.65,
  aboHPHC: 15.65,
  prixBase: 0.194,
  prixHP: 0.2065,
  prixHC: 0.1579,
}

const tabs = [
  { id: 'seuil', label: '01 · Seuil de rentabilité' },
  { id: 'situation', label: '02 · Ma situation' },
  { id: 'csv', label: '03 · Import CSV' },
]
const mode = ref('seuil')

/* ---------------- helpers ---------------- */
const eur = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      })
    : '—'
const kwh = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' kWh'
    : '—'
const pct = (n) =>
  isFinite(n)
    ? (n * 100).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' %'
    : '—'
const asNum = (v) =>
  v === '' || v === null || v === undefined ? NaN : Number(v)
const badgeClass = (kind) =>
  ({ good: 'badge-success', bad: 'badge-error', neutral: 'badge-ghost' })[kind]

/* ---------------- MODE 1 : seuil ---------------- */
const s = reactive({ ...DEFAULTS, conso: 400 })
const sr = ref(null)

// Les tarifs par défaut sont complets : montrer un premier résultat dès l'arrivée
// pour que l'outil soit immédiatement parlant.
onMounted(computeSeuil)

function computeSeuil() {
  const aboBase = asNum(s.aboBase),
    aboHPHC = asNum(s.aboHPHC),
    C = asNum(s.conso)
  const prixBase = asNum(s.prixBase),
    prixHP = asNum(s.prixHP),
    prixHC = asNum(s.prixHC)

  const neutral = (badgeText, sub) => {
    sr.value = {
      badgeClass: 'neutral',
      badgeText,
      figure: '—',
      sub,
      markerLeft: '0%',
      gaugeBg: '',
      formula: '',
    }
  }

  if (
    [aboBase, aboHPHC, C, prixBase, prixHP, prixHC].some((v) => !isFinite(v))
  ) {
    return neutral(
      'Champs manquants',
      'Renseignez tous les champs pour lancer le calcul.',
    )
  }
  if (C <= 0) {
    return neutral(
      'Valeur invalide',
      'La consommation de référence doit être un nombre positif.',
    )
  }
  if ([aboBase, aboHPHC, prixBase, prixHP, prixHC].some((v) => v < 0)) {
    return neutral(
      'Valeur invalide',
      'Les abonnements et prix du kWh doivent être positifs.',
    )
  }

  const denom = prixHC - prixHP
  if (denom === 0) {
    return neutral(
      'Non déterminable',
      'Le tarif heures pleines et le tarif heures creuses sont identiques : comparez directement les abonnements et le tarif Base.',
    )
  }

  const x = (aboBase - aboHPHC + C * (prixBase - prixHP)) / (C * denom)
  const clamped = Math.max(0, Math.min(100, x * 100))
  const gaugeBg = `linear-gradient(to right, var(--color-error) 0%, var(--color-error) ${clamped}%, var(--color-success) ${clamped}%, var(--color-success) 100%)`
  const formula = `x = (aboBase − aboHPHC + C×(prixBase − prixHP)) / (C×(prixHC − prixHP))
  = (${eur(aboBase)} − ${eur(aboHPHC)} + ${C}×(${prixBase} − ${prixHP})) / (${C}×(${prixHC} − ${prixHP}))
  = ${(x * 100).toFixed(1)} %  (C = ${C} kWh/mois pris comme référence)`

  if (x <= 0) {
    sr.value = {
      badgeClass: 'good',
      badgeText: 'Toujours rentable',
      figure: 'Avantageux dès 0 %',
      sub: "Même sans consommation en heures creuses, l'offre HP/HC reste moins chère que l'offre Base à cette consommation de référence.",
      markerLeft: '0%',
      gaugeBg,
      formula,
    }
  } else if (x >= 1) {
    sr.value = {
      badgeClass: 'bad',
      badgeText: 'Jamais rentable',
      figure: 'Même à 100 %, ça ne suffit pas',
      sub: "Même en mettant toute votre consommation en heures creuses, l'offre HP/HC resterait plus chère que Base à cette consommation de référence.",
      markerLeft: '100%',
      gaugeBg,
      formula,
    }
  } else {
    sr.value = {
      badgeClass: 'good',
      badgeText: 'Seuil calculé',
      figure: pct(x) + ' en heures creuses',
      sub: `Au-delà de ${pct(x)} de votre consommation en heures creuses (pour ${kwh(C)}/mois), l'offre HP/HC devient plus avantageuse que Base.`,
      markerLeft: x * 100 + '%',
      gaugeBg,
      formula,
    }
  }
}

/* ---------------- MODE 2 : situation ---------------- */
const t = reactive({ ...DEFAULTS, consoHP: null, consoHC: null })
const tr = ref(null)

function computeSituation() {
  const aboBase = asNum(t.aboBase),
    aboHPHC = asNum(t.aboHPHC)
  const prixBase = asNum(t.prixBase),
    prixHP = asNum(t.prixHP),
    prixHC = asNum(t.prixHC)
  const consoHP = asNum(t.consoHP),
    consoHC = asNum(t.consoHC)

  const neutral = (badgeText, sub) => {
    tr.value = {
      badgeClass: 'neutral',
      badgeText,
      figure: '—',
      sub,
      bars: false,
      formula: '',
    }
  }
  if (
    [aboBase, aboHPHC, prixBase, prixHP, prixHC, consoHP, consoHC].some(
      (v) => !isFinite(v),
    )
  ) {
    return neutral(
      'Champs manquants',
      'Renseignez tous les champs pour lancer le calcul.',
    )
  }
  if (
    [aboBase, aboHPHC, prixBase, prixHP, prixHC, consoHP, consoHC].some(
      (v) => v < 0,
    )
  ) {
    return neutral(
      'Valeur invalide',
      'Les abonnements, prix et consommations doivent être positifs.',
    )
  }

  const costHPHC = aboHPHC + consoHP * prixHP + consoHC * prixHC
  const costBase = aboBase + (consoHP + consoHC) * prixBase
  const diff = costBase - costHPHC // positive = HPHC cheaper
  const maxCost = Math.max(costHPHC, costBase, 0.01)

  const base = {
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
    tr.value = {
      ...base,
      badgeClass: 'neutral',
      badgeText: 'Équivalent',
      figure: 'Aucune différence notable',
      sub: 'Les deux offres reviennent quasiment au même prix sur cette période.',
    }
  } else if (diff > 0) {
    tr.value = {
      ...base,
      badgeClass: 'good',
      badgeText: 'HP/HC rentable',
      figure: `Vous économisez ${eur(diff)} / mois`,
      sub: `Soit environ ${eur(diff * 12)} sur l'année par rapport à l'offre Base.`,
    }
  } else {
    tr.value = {
      ...base,
      badgeClass: 'bad',
      badgeText: 'Base plus avantageuse',
      figure: `Vous perdez ${eur(-diff)} / mois`,
      sub: `Vous auriez payé ${eur(-diff * 12)} de moins sur l'année en restant en Base.`,
    }
  }
}

/* ---------------- dial ---------------- */
function polar(cx, cy, r, angleDeg) {
  const a = ((angleDeg - 90) * Math.PI) / 180
  return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) }
}
function wedgePath(cx, cy, rOuter, rInner, startDeg, endDeg) {
  const p1 = polar(cx, cy, rOuter, startDeg),
    p2 = polar(cx, cy, rOuter, endDeg)
  const p3 = polar(cx, cy, rInner, endDeg),
    p4 = polar(cx, cy, rInner, startDeg)
  return `M ${p1.x} ${p1.y} A ${rOuter} ${rOuter} 0 0 1 ${p2.x} ${p2.y} L ${p3.x} ${p3.y} A ${rInner} ${rInner} 0 0 0 ${p4.x} ${p4.y} Z`
}
function tickPos(h) {
  return polar(100, 100, 106, h * 15)
}
function dialWedgeLabel(h) {
  return `${String(h).padStart(2, '0')}h - ${String((h + 1) % 24).padStart(2, '0')}h`
}

const csvHC = reactive(new Array(24).fill(false))
;[22, 23, 0, 1, 2, 3, 4, 5].forEach((h) => {
  csvHC[h] = true
})

function fmtHour(h) {
  return h === 24 ? '24h' : String(((h % 24) + 24) % 24).padStart(2, '0') + 'h'
}
function hcArrayToRanges(arr) {
  const n = 24,
    visited = new Array(n).fill(false),
    ranges = []
  if (arr.every(Boolean)) return [[0, 24 * 60]] // unambiguous full-day marker (00h-24h)
  for (let start = 0; start < n; start++) {
    const prev = (start - 1 + n) % n
    if (arr[start] && !arr[prev] && !visited[start]) {
      let idx = start,
        len = 0
      while (arr[idx] && len < n) {
        visited[idx] = true
        idx = (idx + 1) % n
        len++
      }
      ranges.push([start, idx])
    }
  }
  return ranges
}
function rangesToText(ranges) {
  return ranges.map(([s2, e]) => fmtHour(s2) + '-' + fmtHour(e)).join(', ')
}

const hcRangesText = ref(rangesToText(hcArrayToRanges(csvHC)))
const rangesWarning = ref('')

function toggleHour(h) {
  csvHC[h] = !csvHC[h]
  hcRangesText.value = rangesToText(hcArrayToRanges(csvHC))
}

// Fragments that didn't match the expected "HHh-HHh" pattern are reported
// via rangesWarning instead of being silently dropped.
function parseHCRangesToMinutes(text) {
  const parts = text
    .split(/,|puis|;|\n/i)
    .map((p) => p.trim())
    .filter(Boolean)
  const ranges = []
  const invalidFragments = []
  for (const p of parts) {
    const m = p.match(
      /^(\d{1,2})\s*[h:]?\s*(\d{2})?\s*-\s*(\d{1,2})\s*[h:]?\s*(\d{2})?$/,
    )
    if (!m) {
      invalidFragments.push(p)
      continue
    }
    const sh = parseInt(m[1], 10),
      sm = m[2] ? parseInt(m[2], 10) : 0
    const eh = parseInt(m[3], 10),
      em = m[4] ? parseInt(m[4], 10) : 0
    if (sh > 24 || eh > 24 || sm > 59 || (eh === 24 && em > 0)) {
      invalidFragments.push(p)
      continue
    }
    ranges.push([sh * 60 + sm, eh * 60 + em])
  }
  rangesWarning.value = invalidFragments.length
    ? `${invalidFragments.length} plage(s) non reconnue(s) et ignorée(s) : ${invalidFragments.join(', ')}`
    : ''
  return ranges
}
// A start===end range (e.g. a "06h-06h" typo) covers nothing — it is NOT treated as "all day".
// Only the explicit 00h-24h marker (produced by the dial when every hour is selected) means "all day".
function minutesInHC(minutes, ranges) {
  for (const [start, end] of ranges) {
    if (start === end) continue // zero-length range: no coverage, not "all day"
    if (start < end) {
      if (minutes >= start && minutes < end) return true
    } else if (minutes >= start || minutes < end) return true
  }
  return false
}
function applyRangesText() {
  const ranges = parseHCRangesToMinutes(hcRangesText.value)
  for (let h = 0; h < 24; h++) {
    csvHC[h] = minutesInHC(h * 60 + 30, ranges)
  }
}

/* ---------------- MODE 3 : CSV ---------------- */
const c = reactive({ ...DEFAULTS })
const cr = ref(null)

const csvHeader = ref(null)
let csvRows = null
const csvStatus = ref('')
const csvStatusClass = ref('')
const csvFormat = ref('startend')
const colStart = ref(0),
  colEnd = ref(1),
  colPower = ref(2)
const colDate = ref(0),
  colVal = ref(1)

function detectDelimiter(line) {
  const semi = (line.match(/;/g) || []).length,
    comma = (line.match(/,/g) || []).length
  return semi > comma ? ';' : ','
}

// Parses "DD/MM/YYYY HH:mm:ss", "DD/MM/YYYY HH:mm", "YYYY-MM-DD HH:mm(:ss)" etc. into a real Date.
// French DD/MM/YYYY is assumed for slash-separated dates (never MM/DD) to avoid ambiguity.
function parseDateTimeFR(v) {
  if (!v) return null
  v = v.trim()
  let m = v.match(
    /^(\d{1,2})\/(\d{1,2})\/(\d{4})[ T](\d{1,2}):(\d{2})(?::(\d{2}))?/,
  )
  if (m) {
    return new Date(+m[3], +m[2] - 1, +m[1], +m[4], +m[5], m[6] ? +m[6] : 0)
  }
  m = v.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{1,2}):(\d{2})(?::(\d{2}))?/)
  if (m) {
    return new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], m[6] ? +m[6] : 0)
  }
  const d = new Date(v.replace(' ', 'T'))
  if (!isNaN(d.getTime())) return d
  return null
}
function dateKey(d) {
  return (
    d.getFullYear() +
    '-' +
    String(d.getMonth() + 1).padStart(2, '0') +
    '-' +
    String(d.getDate()).padStart(2, '0')
  )
}
function looksLikeDateColumn(rows, col) {
  let hits = 0
  const n = Math.min(20, rows.length)
  for (let i = 0; i < n; i++) {
    if (parseDateTimeFR(rows[i][col] || '') !== null) hits++
  }
  return hits
}
function looksLikeNumberColumn(rows, col) {
  let hits = 0
  const n = Math.min(20, rows.length)
  for (let i = 0; i < n; i++) {
    const v = (rows[i][col] || '').replace(',', '.').trim()
    if (v !== '' && !isNaN(parseFloat(v))) hits++
  }
  return hits
}

function onCsvFile(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => {
    try {
      const text = ev.target.result
      const lines = text.split(/\r\n|\n|\r/).filter((l) => l.trim().length > 0)
      if (lines.length < 2) throw new Error('Fichier trop court.')
      const delim = detectDelimiter(lines[0])
      const header = lines[0].split(delim).map((h) => h.trim())
      csvRows = lines.slice(1).map((l) => l.split(delim))
      const n = Math.min(20, csvRows.length)

      // score each column as date-like or number-like
      const dateScores = header.map((h, col) =>
        looksLikeDateColumn(csvRows, col),
      )
      const numScores = header.map((h, col) =>
        looksLikeNumberColumn(csvRows, col),
      )
      const dateCols = dateScores
        .map((score, col) => ({ col, score }))
        .filter((x) => x.score > n * 0.5)
        .map((x) => x.col)

      csvFormat.value = dateCols.length >= 2 ? 'startend' : 'single'

      const startIdx = dateCols[0] ?? 0
      const endIdx = dateCols[1] ?? Math.min(1, header.length - 1)
      let powerIdx = -1,
        bestScore = -1
      for (let col = 0; col < header.length; col++) {
        if (col === startIdx || col === endIdx) continue
        if (numScores[col] > bestScore) {
          bestScore = numScores[col]
          powerIdx = col
        }
      }
      if (powerIdx < 0) powerIdx = header.length - 1

      const dateIdx = dateCols[0] ?? 0
      let valIdx = -1,
        bestScore2 = -1
      for (let col = 0; col < header.length; col++) {
        if (col === dateIdx) continue
        if (numScores[col] > bestScore2) {
          bestScore2 = numScores[col]
          valIdx = col
        }
      }
      if (valIdx < 0) valIdx = header.length > 1 ? 1 : 0

      colStart.value = startIdx
      colEnd.value = endIdx
      colPower.value = powerIdx
      colDate.value = dateIdx
      colVal.value = valIdx
      csvHeader.value = header

      csvStatusClass.value = 'ok'
      csvStatus.value = `${csvRows.length} lignes lues. Vérifiez le format et les colonnes détectées ci-dessous.`
    } catch (err) {
      csvStatusClass.value = 'err'
      csvStatus.value = 'Impossible de lire ce fichier : ' + err.message
      csvHeader.value = null
      csvRows = null
    }
  }
  reader.readAsText(file)
}

function computeCsv() {
  const neutral = (badgeText, sub) => {
    cr.value = {
      badgeClass: 'neutral',
      badgeText,
      figure: '—',
      sub,
      rows: [],
      skipDetail: '',
      formula: '',
    }
  }

  if (!csvRows) {
    return neutral(
      'Aucun fichier',
      "Importez un fichier CSV avant de lancer l'analyse.",
    )
  }
  const aboBase = asNum(c.aboBase),
    aboHPHC = asNum(c.aboHPHC)
  const prixBase = asNum(c.prixBase),
    prixHP = asNum(c.prixHP),
    prixHC = asNum(c.prixHC)
  if ([aboBase, aboHPHC, prixBase, prixHP, prixHC].some((v) => !isFinite(v))) {
    return neutral('Tarifs manquants', 'Renseignez les tarifs des deux offres.')
  }
  if ([aboBase, aboHPHC, prixBase, prixHP, prixHC].some((v) => v < 0)) {
    return neutral(
      'Valeur invalide',
      'Les abonnements et prix du kWh doivent être positifs.',
    )
  }

  const ranges = parseHCRangesToMinutes(hcRangesText.value)

  let consoHP = 0,
    consoHC = 0
  const days = new Set()
  let usedRows = 0,
    skippedRows = 0
  let minTime = null,
    maxTime = null
  const skipReasons = {
    cellManquante: 0,
    nombreInvalide: 0,
    dateInvalide: 0,
    dureeInvalide: 0,
  }
  const skipExamples = []
  function noteSkip(reason, rawRow) {
    skippedRows++
    skipReasons[reason]++
    if (skipExamples.length < 6)
      skipExamples.push({ reason, raw: rawRow.join(' | ') })
  }
  function trackSpan(d) {
    const time = d.getTime()
    if (minTime === null || time < minTime) minTime = time
    if (maxTime === null || time > maxTime) maxTime = time
  }

  if (csvFormat.value === 'startend') {
    for (const row of csvRows) {
      const startStr = row[colStart.value],
        endStr = row[colEnd.value],
        pStr = row[colPower.value]
      if (
        startStr === undefined ||
        endStr === undefined ||
        pStr === undefined ||
        startStr.trim() === '' ||
        endStr.trim() === '' ||
        pStr.trim() === ''
      ) {
        noteSkip('cellManquante', row)
        continue
      }
      const power = parseFloat((pStr || '').replace(',', '.').trim())
      const start = parseDateTimeFR(startStr),
        end = parseDateTimeFR(endStr)
      if (!start || !end) {
        noteSkip('dateInvalide', row)
        continue
      }
      if (isNaN(power)) {
        noteSkip('nombreInvalide', row)
        continue
      }
      const durationHours = (end.getTime() - start.getTime()) / 3600000
      if (!(durationHours > 0)) {
        noteSkip('dureeInvalide', row)
        continue
      }
      const energie = power * durationHours // kW × h = kWh
      const minutes = start.getHours() * 60 + start.getMinutes()
      days.add(dateKey(start))
      trackSpan(start)
      usedRows++
      if (minutesInHC(minutes, ranges)) consoHC += energie
      else consoHP += energie
    }
  } else {
    for (const row of csvRows) {
      const dstr = row[colDate.value],
        vstr = row[colVal.value]
      if (
        dstr === undefined ||
        vstr === undefined ||
        dstr.trim() === '' ||
        vstr.trim() === ''
      ) {
        noteSkip('cellManquante', row)
        continue
      }
      const val = parseFloat((vstr || '').replace(',', '.').trim())
      const d = parseDateTimeFR(dstr)
      if (!d) {
        noteSkip('dateInvalide', row)
        continue
      }
      if (isNaN(val)) {
        noteSkip('nombreInvalide', row)
        continue
      }
      const minutes = d.getHours() * 60 + d.getMinutes()
      days.add(dateKey(d))
      trackSpan(d)
      usedRows++
      if (minutesInHC(minutes, ranges)) consoHC += val
      else consoHP += val
    }
  }

  if (usedRows === 0) {
    return neutral(
      'Lecture impossible',
      'Aucune ligne exploitable : vérifiez le format et les colonnes sélectionnées.',
    )
  }

  const nbJoursAvecDonnees = days.size || 1
  const nbJoursCalendaires =
    minTime !== null
      ? Math.round((maxTime - minTime) / 86400000) + 1
      : nbJoursAvecDonnees
  // L'abonnement court sur toute la période couverte par le relevé (du premier au dernier point),
  // même si certaines journées n'ont pas de données (coupure Linky) : on prorate sur cette étendue
  // calendaire réelle, pas sur le nombre de jours qui ont effectivement des mesures.
  const nbJours = nbJoursCalendaires
  const moisEquiv = nbJours / 30.437
  const aboBaseProrata = aboBase * moisEquiv
  const aboHPHCProrata = aboHPHC * moisEquiv
  const energieBase = (consoHP + consoHC) * prixBase
  const energieHPHC = consoHP * prixHP + consoHC * prixHC
  const costBase = aboBaseProrata + energieBase
  const costHPHC = aboHPHCProrata + energieHPHC
  const diff = costBase - costHPHC

  let skipDetail = ''
  if (skippedRows > 0) {
    const reasonLabels = {
      cellManquante: 'Cellule vide ou colonne manquante sur la ligne',
      dateInvalide: 'Date/heure illisible (format inattendu)',
      nombreInvalide: 'Valeur de consommation/puissance non numérique',
      dureeInvalide:
        "Durée Fin − Début nulle ou négative (souvent le changement d'heure été/hiver)",
    }
    const breakdown = Object.entries(skipReasons)
      .filter(([, n]) => n > 0)
      .map(([k, n]) => `  ${reasonLabels[k]} : ${n} ligne(s)`)
      .join('\n')
    const examples = skipExamples
      .map((ex) => `  [${reasonLabels[ex.reason]}] ${ex.raw}`)
      .join('\n')
    skipDetail = `${skippedRows} ligne(s) sur ${usedRows + skippedRows} n'ont pas pu être exploitées :
${breakdown}

Exemples de lignes ignorées (contenu brut) :
${examples || '(aucun exemple disponible)'}`
  }

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

  const formula = `Format : ${csvFormat.value === 'startend' ? 'Début/Fin + puissance (kW), energie = puissance x duree de l intervalle' : 'Date + consommation (kWh)'}
Plages heures creuses : ${hcRangesText.value || '(aucune)'}
Abonnement proraté = abonnement mensuel × (jours couverts / 30,437)
Coût Base  = ${eur(aboBaseProrata)} + ${kwh(consoHP + consoHC)}×${prixBase} = ${eur(costBase)}
Coût HP/HC = ${eur(aboHPHCProrata)} + ${kwh(consoHP)}×${prixHP} + ${kwh(consoHC)}×${prixHC} = ${eur(costHPHC)}`

  let badgeKind, badgeText, figure, sub
  if (Math.abs(diff) < 0.01) {
    badgeKind = 'neutral'
    badgeText = 'Équivalent'
    figure = 'Aucune différence notable'
    sub = `Sur les ${nbJours} jours analysés, les deux offres reviennent au même prix.`
  } else if (diff > 0) {
    badgeKind = 'good'
    badgeText = 'HP/HC gagnant'
    figure = `${eur(diff)} économisés sur la période`
    sub = `Sur les ${nbJours} jours analysés, l'offre HP/HC aurait coûté moins cher que Base avec ces plages horaires.`
  } else {
    badgeKind = 'bad'
    badgeText = 'Base gagnante'
    figure = `${eur(-diff)} perdus sur la période`
    sub = `Sur les ${nbJours} jours analysés, l'offre Base aurait été moins chère que HP/HC avec ces plages horaires.`
  }

  cr.value = {
    badgeClass: badgeKind,
    badgeText,
    figure,
    sub,
    rows,
    skipDetail,
    formula,
  }
}
</script>

<template>
  <div>
    <!-- ============ TABS ============ -->
    <div role="tablist" class="tabs tabs-boxed bg-base-200 mb-6 flex-wrap">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        role="tab"
        class="tab"
        :class="{ 'tab-active': mode === tab.id }"
        :aria-selected="mode === tab.id"
        @click="mode = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- ============ MODE 1 : SEUIL ============ -->
    <section v-show="mode === 'seuil'">
      <div class="card bg-base-100 border border-base-300 mb-5">
        <div class="card-body">
          <h2 class="card-title text-base">
            Quel % en heures creuses pour rentabiliser l'offre HP/HC ?
          </h2>
          <p class="text-sm text-base-content/60 mb-4">
            Entrez vos tarifs. On calcule la part de consommation à basculer en
            heures creuses pour que l'offre HP/HC devienne plus intéressante que
            l'offre Base.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="s.aboBase"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement HP/HC<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="s.aboHPHC"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Consommation de référence<span
                  class="block font-normal text-base-content/50 text-xs"
                  >kWh / mois</span
                ></label
              >
              <input
                type="number"
                step="1"
                v-model.number="s.conso"
                placeholder="ex : 400"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="s.prixBase"
                placeholder="ex : 0,1940"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures pleines<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="s.prixHP"
                placeholder="ex : 0,2065"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures creuses<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="s.prixHC"
                placeholder="ex : 0,1579"
                class="input input-bordered w-full"
              />
            </div>
          </div>
          <div>
            <button class="btn btn-primary mt-4" @click="computeSeuil">
              Calculer le seuil
            </button>
          </div>

          <div v-if="sr" class="mt-5">
            <span class="badge" :class="badgeClass(sr.badgeClass)">{{
              sr.badgeText
            }}</span>
            <div class="text-3xl font-bold mt-2 mb-1">{{ sr.figure }}</div>
            <div class="text-sm text-base-content/60 mb-4">{{ sr.sub }}</div>
            <div
              class="h-3.5 rounded-full relative bg-base-300 mt-4 mb-2"
              :style="{ background: sr.gaugeBg }"
            >
              <div
                class="absolute -top-1.5 w-1 h-6 bg-base-content rounded"
                :style="{ left: sr.markerLeft }"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-base-content/50">
              <span>0 % en heures creuses</span
              ><span>100 % en heures creuses</span>
            </div>
            <details v-if="sr.formula" class="mt-4 text-sm">
              <summary
                class="cursor-pointer font-semibold text-base-content/60"
              >
                Voir le détail du calcul
              </summary>
              <div
                class="bg-base-200 rounded-lg p-4 mt-2 font-mono text-xs whitespace-pre-wrap"
              >
                {{ sr.formula }}
              </div>
            </details>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ MODE 2 : SITUATION ============ -->
    <section v-show="mode === 'situation'">
      <div class="card bg-base-100 border border-base-300 mb-5">
        <div class="card-body">
          <h2 class="card-title text-base">
            Mon offre HP/HC est-elle rentable ?
          </h2>
          <p class="text-sm text-base-content/60 mb-4">
            Entrez vos tarifs et votre consommation réelle (sur une même
            période, par ex. un mois) pour comparer votre coût réel avec ce que
            vous auriez payé en Base.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="t.aboBase"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement HP/HC<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="t.aboHPHC"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div class="hidden md:block"></div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="t.prixBase"
                placeholder="ex : 0,1940"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures pleines<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="t.prixHP"
                placeholder="ex : 0,2065"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures creuses<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="t.prixHC"
                placeholder="ex : 0,1579"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Ma consommation en heures pleines<span
                  class="block font-normal text-base-content/50 text-xs"
                  >kWh sur la période</span
                ></label
              >
              <input
                type="number"
                step="1"
                v-model.number="t.consoHP"
                placeholder="ex : 210"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Ma consommation en heures creuses<span
                  class="block font-normal text-base-content/50 text-xs"
                  >kWh sur la période</span
                ></label
              >
              <input
                type="number"
                step="1"
                v-model.number="t.consoHC"
                placeholder="ex : 190"
                class="input input-bordered w-full"
              />
            </div>
          </div>
          <div>
            <button class="btn btn-primary mt-4" @click="computeSituation">
              Comparer
            </button>
          </div>

          <div v-if="tr" class="mt-5">
            <span class="badge" :class="badgeClass(tr.badgeClass)">{{
              tr.badgeText
            }}</span>
            <div class="text-3xl font-bold mt-2 mb-1">{{ tr.figure }}</div>
            <div class="text-sm text-base-content/60 mb-4">{{ tr.sub }}</div>
            <div v-if="tr.bars" class="space-y-4">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span>Offre Base</span
                  ><span class="font-mono">{{ eur(tr.costBase) }}</span>
                </div>
                <div
                  class="h-5 bg-base-200 border border-base-300 rounded overflow-hidden"
                >
                  <div
                    class="h-full transition-all duration-500"
                    :class="
                      tr.costBase < tr.costHPHC ? 'bg-success' : 'bg-neutral'
                    "
                    :style="{ width: tr.barBaseWidth }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span>Offre HP/HC</span
                  ><span class="font-mono">{{ eur(tr.costHPHC) }}</span>
                </div>
                <div
                  class="h-5 bg-base-200 border border-base-300 rounded overflow-hidden"
                >
                  <div
                    class="h-full transition-all duration-500"
                    :class="
                      tr.costHPHC < tr.costBase ? 'bg-success' : 'bg-neutral'
                    "
                    :style="{ width: tr.barHPHCWidth }"
                  ></div>
                </div>
              </div>
            </div>
            <details v-if="tr.formula" class="mt-4 text-sm">
              <summary
                class="cursor-pointer font-semibold text-base-content/60"
              >
                Voir le détail du calcul
              </summary>
              <div
                class="bg-base-200 rounded-lg p-4 mt-2 font-mono text-xs whitespace-pre-wrap"
              >
                {{ tr.formula }}
              </div>
            </details>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ MODE 3 : CSV ============ -->
    <section v-show="mode === 'csv'">
      <div class="card bg-base-100 border border-base-300 mb-5">
        <div class="card-body">
          <h2 class="card-title text-base">Plages horaires heures creuses</h2>
          <p class="text-sm text-base-content/60 mb-4">
            Cliquez sur le cadran pour marquer vos heures creuses, ou saisissez
            directement les plages (ex :
            <span class="font-mono">13h-15h, 00h-06h</span>). Plusieurs plages
            possibles.
          </p>
          <div class="flex flex-wrap gap-6 items-start">
            <div>
              <svg
                width="200"
                height="200"
                viewBox="0 0 200 200"
                aria-label="Cadran des 24 heures, cliquer pour marquer les heures creuses"
              >
                <path
                  v-for="h in 24"
                  :key="h - 1"
                  class="cursor-pointer hover:opacity-80 stroke-base-100"
                  :class="csvHC[h - 1] ? 'fill-info' : 'fill-warning'"
                  stroke-width="1.5"
                  :d="wedgePath(100, 100, 96, 52.8, (h - 1) * 15, h * 15)"
                  role="button"
                  :aria-label="dialWedgeLabel(h - 1)"
                  @click="toggleHour(h - 1)"
                />
                <text
                  v-for="h in [0, 6, 12, 18]"
                  :key="'tick' + h"
                  :x="tickPos(h).x"
                  :y="tickPos(h).y"
                  text-anchor="middle"
                  dominant-baseline="middle"
                  font-size="9"
                  fill="currentColor"
                  opacity=".55"
                >
                  {{ String(h).padStart(2, '0') }}h
                </text>
              </svg>
              <div class="flex gap-4 mt-2 text-xs text-base-content/60">
                <span class="flex items-center gap-1.5"
                  ><span
                    class="w-2.5 h-2.5 rounded-sm bg-warning inline-block"
                  ></span
                  >Heures pleines</span
                >
                <span class="flex items-center gap-1.5"
                  ><span
                    class="w-2.5 h-2.5 rounded-sm bg-info inline-block"
                  ></span
                  >Heures creuses</span
                >
              </div>
            </div>
            <div class="flex-1 min-w-60">
              <label class="label-text font-semibold block mb-1"
                >Plages heures creuses<span
                  class="block font-normal text-base-content/50 text-xs"
                  >séparées par une virgule</span
                ></label
              >
              <input
                type="text"
                v-model="hcRangesText"
                placeholder="ex : 13h-15h, 00h-06h"
                class="input input-bordered w-full font-mono"
              />
              <p v-if="rangesWarning" class="text-error text-xs mt-2">
                {{ rangesWarning }}
              </p>
              <button
                class="btn btn-outline btn-sm mt-3"
                @click="applyRangesText"
              >
                Appliquer le texte au cadran
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card bg-base-100 border border-base-300 mb-5">
        <div class="card-body">
          <h2 class="card-title text-base">Fichier de consommation</h2>
          <p class="text-sm text-base-content/60 mb-4">
            Deux formats sont acceptés : une colonne date + une consommation en
            kWh, ou deux colonnes Début/Fin + une puissance en kW (format export
            Enedis/Linky). L'appli convertit automatiquement la puissance en
            énergie à partir de la durée de chaque intervalle. Tout le
            traitement reste dans votre navigateur : le fichier n'est jamais
            envoyé sur un serveur.
          </p>
          <div
            class="border-2 border-dashed border-base-300 rounded-xl p-6 text-center text-sm text-base-content/60"
          >
            Choisissez votre fichier CSV
            <br />
            <input
              type="file"
              accept=".csv,text/csv"
              class="file-input file-input-bordered file-input-sm mt-3"
              @change="onCsvFile"
            />
          </div>
          <div
            v-if="csvStatus"
            class="text-sm mt-2"
            :class="csvStatusClass === 'ok' ? 'text-success' : 'text-error'"
          >
            {{ csvStatus }}
          </div>

          <div v-if="csvHeader" class="mt-4">
            <div class="max-w-sm">
              <label class="label-text font-semibold block mb-1"
                >Format détecté du fichier</label
              >
              <select v-model="csvFormat" class="select select-bordered w-full">
                <option value="startend">
                  2 colonnes Début / Fin + puissance (kW)
                </option>
                <option value="single">
                  1 colonne date/heure + consommation (kWh)
                </option>
              </select>
            </div>

            <div
              v-if="csvFormat === 'startend'"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3"
            >
              <div>
                <label class="label-text font-semibold block mb-1"
                  >Colonne « Début »</label
                >
                <select
                  v-model.number="colStart"
                  class="select select-bordered w-full"
                >
                  <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                    {{ h || `Colonne ${i + 1}` }}
                  </option>
                </select>
              </div>
              <div>
                <label class="label-text font-semibold block mb-1"
                  >Colonne « Fin »</label
                >
                <select
                  v-model.number="colEnd"
                  class="select select-bordered w-full"
                >
                  <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                    {{ h || `Colonne ${i + 1}` }}
                  </option>
                </select>
              </div>
              <div>
                <label class="label-text font-semibold block mb-1"
                  >Colonne puissance (kW)</label
                >
                <select
                  v-model.number="colPower"
                  class="select select-bordered w-full"
                >
                  <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                    {{ h || `Colonne ${i + 1}` }}
                  </option>
                </select>
              </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
              <div>
                <label class="label-text font-semibold block mb-1"
                  >Colonne date / heure</label
                >
                <select
                  v-model.number="colDate"
                  class="select select-bordered w-full"
                >
                  <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                    {{ h || `Colonne ${i + 1}` }}
                  </option>
                </select>
              </div>
              <div>
                <label class="label-text font-semibold block mb-1"
                  >Colonne consommation (kWh)</label
                >
                <select
                  v-model.number="colVal"
                  class="select select-bordered w-full"
                >
                  <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                    {{ h || `Colonne ${i + 1}` }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card bg-base-100 border border-base-300 mb-5">
        <div class="card-body">
          <h2 class="card-title text-base">Tarifs des deux offres</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="c.aboBase"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Abonnement HP/HC<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ / mois</span
                ></label
              >
              <input
                type="number"
                step="0.01"
                v-model.number="c.aboHPHC"
                placeholder="ex : 15,65"
                class="input input-bordered w-full"
              />
            </div>
            <div class="hidden md:block"></div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Base<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="c.prixBase"
                placeholder="ex : 0,1940"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures pleines<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="c.prixHP"
                placeholder="ex : 0,2065"
                class="input input-bordered w-full"
              />
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Prix du kWh — Heures creuses<span
                  class="block font-normal text-base-content/50 text-xs"
                  >€ TTC / kWh</span
                ></label
              >
              <input
                type="number"
                step="0.0001"
                v-model.number="c.prixHC"
                placeholder="ex : 0,1579"
                class="input input-bordered w-full"
              />
            </div>
          </div>
          <div>
            <button class="btn btn-primary mt-4" @click="computeCsv">
              Analyser
            </button>
          </div>

          <div v-if="cr" class="mt-5">
            <span class="badge" :class="badgeClass(cr.badgeClass)">{{
              cr.badgeText
            }}</span>
            <div class="text-3xl font-bold mt-2 mb-1">{{ cr.figure }}</div>
            <div class="text-sm text-base-content/60 mb-4">{{ cr.sub }}</div>
            <div v-if="cr.rows.length" class="overflow-x-auto">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Détail</th>
                    <th class="text-right">Base</th>
                    <th class="text-right">HP/HC</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, i) in cr.rows"
                    :key="i"
                    :class="{ 'text-success font-semibold': row.win }"
                  >
                    <td class="text-base-content/60">{{ row.label }}</td>
                    <td
                      v-if="row.span"
                      colspan="2"
                      class="text-right font-mono"
                    >
                      {{ row.value }}
                    </td>
                    <template v-else>
                      <td class="text-right font-mono">{{ row.base }}</td>
                      <td class="text-right font-mono">{{ row.hphc }}</td>
                    </template>
                  </tr>
                </tbody>
              </table>
            </div>
            <details v-if="cr.skipDetail" class="mt-4 text-sm">
              <summary
                class="cursor-pointer font-semibold text-base-content/60"
              >
                Pourquoi des lignes ont-elles été ignorées ?
              </summary>
              <div
                class="bg-base-200 rounded-lg p-4 mt-2 font-mono text-xs whitespace-pre-wrap"
              >
                {{ cr.skipDetail }}
              </div>
            </details>
            <details v-if="cr.formula" class="mt-4 text-sm">
              <summary
                class="cursor-pointer font-semibold text-base-content/60"
              >
                Hypothèses du calcul
              </summary>
              <div
                class="bg-base-200 rounded-lg p-4 mt-2 font-mono text-xs whitespace-pre-wrap"
              >
                {{ cr.formula }}
              </div>
            </details>
          </div>
        </div>
      </div>
    </section>

    <p class="text-xs text-base-content/60 border-t border-base-300 mt-8 pt-4">
      Estimation basée uniquement sur les tarifs et données que vous saisissez :
      ils peuvent évoluer, et ce calcul ne remplace pas une simulation
      officielle de votre fournisseur.
    </p>
  </div>
</template>
