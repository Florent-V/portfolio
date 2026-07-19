<script setup>
defineProps({
  summary: { type: String, default: 'Voir le détail du calcul' },
  text: { type: String, required: true },
})
</script>

<template>
  <details v-if="text" class="mt-4 text-sm">
    <summary class="cursor-pointer font-semibold text-base-content/60">
      {{ summary }}
    </summary>
    <div
      class="bg-base-200 rounded-lg p-4 mt-2 font-mono text-xs whitespace-pre-wrap"
    >
      {{ text }}
    </div>
  </details>
</template>
