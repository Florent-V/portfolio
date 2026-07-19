export function fmtHour(h) {
  return h === 24 ? '24h' : String(((h % 24) + 24) % 24).padStart(2, '0') + 'h'
}

export function hcArrayToRanges(arr) {
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

export function rangesToText(ranges) {
  return ranges.map(([s, e]) => fmtHour(s) + '-' + fmtHour(e)).join(', ')
}

// Fragments that don't match the expected "HHh-HHh" pattern are returned in
// invalidFragments instead of being silently dropped.
export function parseHCRanges(text) {
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
  return { ranges, invalidFragments }
}

// A start===end range (e.g. a "06h-06h" typo) covers nothing — it is NOT treated as "all day".
// Only the explicit 00h-24h marker (produced by the dial when every hour is selected) means "all day".
export function minutesInHC(minutes, ranges) {
  for (const [start, end] of ranges) {
    if (start === end) continue // zero-length range: no coverage, not "all day"
    if (start < end) {
      if (minutes >= start && minutes < end) return true
    } else if (minutes >= start || minutes < end) return true
  }
  return false
}
