<script setup>
defineProps({
  label: { type: String, required: true },
  hint: { type: String, default: '' },
  step: { type: String, default: '0.0001' },
  placeholder: { type: String, default: '' },
})
const model = defineModel({ type: [Number, String], default: null })
</script>

<template>
  <div>
    <label class="label-text font-semibold block mb-1">
      {{ label }}
      <span
        v-if="hint"
        class="block font-normal text-base-content/50 text-xs"
        >{{ hint }}</span
      >
    </label>
    <input
      v-model.number="model"
      type="number"
      :step="step"
      :placeholder="placeholder"
      class="input input-bordered w-full"
    />
  </div>
</template>
