<template>
  <div>Hello {{ name }}!</div>
</template>

<script setup>
defineProps({
  name: {
    type: String,
    default: 'John Doe', // Valeur par défaut
  },
})
</script>
