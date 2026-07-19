<script setup>
defineProps({
  // { badgeClass: 'good'|'bad'|'neutral', badgeText, figure, sub }
  result: { type: Object, required: true },
})

const BADGES = {
  good: 'badge-success',
  bad: 'badge-error',
  neutral: 'badge-ghost',
}
</script>

<template>
  <div>
    <span class="badge" :class="BADGES[result.badgeClass]">{{
      result.badgeText
    }}</span>
    <div class="text-3xl font-bold mt-2 mb-1">{{ result.figure }}</div>
    <div class="text-sm text-base-content/60 mb-4">{{ result.sub }}</div>
  </div>
</template>
