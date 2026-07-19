import { minutesInHC } from './hcRanges'

function detectDelimiter(line) {
  const semi = (line.match(/;/g) || []).length,
    comma = (line.match(/,/g) || []).length
  return semi > comma ? ';' : ','
}

// Parses "DD/MM/YYYY HH:mm:ss", "DD/MM/YYYY HH:mm", "YYYY-MM-DD HH:mm(:ss)" etc. into a real Date.
// French DD/MM/YYYY is assumed for slash-separated dates (never MM/DD) to avoid ambiguity.
export function parseDateTimeFR(v) {
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

// Parses raw CSV text, detects delimiter, scores columns and guesses the file
// format plus the most plausible column mapping. Throws on unusable files.
export function parseCsvFile(text) {
  const lines = text.split(/\r\n|\n|\r/).filter((l) => l.trim().length > 0)
  if (lines.length < 2) throw new Error('Fichier trop court.')
  const delim = detectDelimiter(lines[0])
  const header = lines[0].split(delim).map((h) => h.trim())
  const rows = lines.slice(1).map((l) => l.split(delim))
  const n = Math.min(20, rows.length)

  const numScores = header.map((h, col) => looksLikeNumberColumn(rows, col))
  const dateCols = header
    .map((h, col) => ({ col, score: looksLikeDateColumn(rows, col) }))
    .filter((x) => x.score > n * 0.5)
    .map((x) => x.col)

  const format = dateCols.length >= 2 ? 'startend' : 'single'

  const startIdx = dateCols[0] ?? 0
  const endIdx = dateCols[1] ?? Math.min(1, header.length - 1)
  const bestNumCol = (excluded) => {
    let best = -1,
      bestScore = -1
    for (let col = 0; col < header.length; col++) {
      if (excluded.includes(col)) continue
      if (numScores[col] > bestScore) {
        bestScore = numScores[col]
        best = col
      }
    }
    return best
  }
  let powerIdx = bestNumCol([startIdx, endIdx])
  if (powerIdx < 0) powerIdx = header.length - 1
  const dateIdx = dateCols[0] ?? 0
  let valIdx = bestNumCol([dateIdx])
  if (valIdx < 0) valIdx = header.length > 1 ? 1 : 0

  return { header, rows, format, startIdx, endIdx, powerIdx, dateIdx, valIdx }
}

// Aggregates consumption rows into HP/HC totals for the given HC ranges.
// Unusable rows are counted per reason instead of failing the whole analysis.
export function analyzeConsumption(rows, { format, cols, ranges }) {
  let consoHP = 0,
    consoHC = 0,
    usedRows = 0,
    skippedRows = 0
  let minTime = null,
    maxTime = null
  const days = new Set()
  const skipReasons = {
    cellManquante: 0,
    nombreInvalide: 0,
    dateInvalide: 0,
    dureeInvalide: 0,
  }
  const skipExamples = []
  const noteSkip = (reason, rawRow) => {
    skippedRows++
    skipReasons[reason]++
    if (skipExamples.length < 6)
      skipExamples.push({ reason, raw: rawRow.join(' | ') })
  }
  const useRow = (d, energy) => {
    days.add(dateKey(d))
    const time = d.getTime()
    if (minTime === null || time < minTime) minTime = time
    if (maxTime === null || time > maxTime) maxTime = time
    usedRows++
    const minutes = d.getHours() * 60 + d.getMinutes()
    if (minutesInHC(minutes, ranges)) consoHC += energy
    else consoHP += energy
  }

  for (const row of rows) {
    if (format === 'startend') {
      const startStr = row[cols.start],
        endStr = row[cols.end],
        pStr = row[cols.power]
      if (!startStr?.trim() || !endStr?.trim() || !pStr?.trim()) {
        noteSkip('cellManquante', row)
        continue
      }
      const power = parseFloat(pStr.replace(',', '.').trim())
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
      useRow(start, power * durationHours) // kW × h = kWh
    } else {
      const dstr = row[cols.date],
        vstr = row[cols.val]
      if (!dstr?.trim() || !vstr?.trim()) {
        noteSkip('cellManquante', row)
        continue
      }
      const val = parseFloat(vstr.replace(',', '.').trim())
      const d = parseDateTimeFR(dstr)
      if (!d) {
        noteSkip('dateInvalide', row)
        continue
      }
      if (isNaN(val)) {
        noteSkip('nombreInvalide', row)
        continue
      }
      useRow(d, val)
    }
  }

  return {
    consoHP,
    consoHC,
    usedRows,
    skippedRows,
    skipReasons,
    skipExamples,
    daysWithData: days.size,
    minTime,
    maxTime,
  }
}
