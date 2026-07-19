<script setup>
defineProps({
  // 24 booleans, true = heure creuse
  hours: { type: Array, required: true },
})
const emit = defineEmits(['toggle'])

function polar(cx, cy, r, angleDeg) {
  const a = ((angleDeg - 90) * Math.PI) / 180
  return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) }
}
function wedgePath(h) {
  const [cx, cy, rOuter, rInner] = [100, 100, 96, 52.8]
  const startDeg = h * 15,
    endDeg = startDeg + 15
  const p1 = polar(cx, cy, rOuter, startDeg),
    p2 = polar(cx, cy, rOuter, endDeg)
  const p3 = polar(cx, cy, rInner, endDeg),
    p4 = polar(cx, cy, rInner, startDeg)
  return `M ${p1.x} ${p1.y} A ${rOuter} ${rOuter} 0 0 1 ${p2.x} ${p2.y} L ${p3.x} ${p3.y} A ${rInner} ${rInner} 0 0 0 ${p4.x} ${p4.y} Z`
}
function tickPos(h) {
  return polar(100, 100, 106, h * 15)
}
function wedgeLabel(h) {
  return `${String(h).padStart(2, '0')}h - ${String((h + 1) % 24).padStart(2, '0')}h`
}
</script>

<template>
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
        :class="hours[h - 1] ? 'fill-info' : 'fill-warning'"
        stroke-width="1.5"
        :d="wedgePath(h - 1)"
        role="button"
        :aria-label="wedgeLabel(h - 1)"
        @click="emit('toggle', h - 1)"
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
      <span class="flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-sm bg-warning inline-block"></span
        >Heures pleines
      </span>
      <span class="flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-sm bg-info inline-block"></span>Heures
        creuses
      </span>
    </div>
  </div>
</template>
