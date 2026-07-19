<script setup>
import PriceField from './PriceField.vue'

const aboBase = defineModel('aboBase', {
  type: [Number, String],
  default: null,
})
const aboHPHC = defineModel('aboHPHC', {
  type: [Number, String],
  default: null,
})
const prixBase = defineModel('prixBase', {
  type: [Number, String],
  default: null,
})
const prixHP = defineModel('prixHP', { type: [Number, String], default: null })
const prixHC = defineModel('prixHC', { type: [Number, String], default: null })
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <PriceField
      v-model="aboBase"
      label="Abonnement Base"
      hint="€ / mois"
      step="0.01"
      placeholder="ex : 15,65"
    />
    <PriceField
      v-model="aboHPHC"
      label="Abonnement HP/HC"
      hint="€ / mois"
      step="0.01"
      placeholder="ex : 15,65"
    />
    <slot name="third">
      <div class="hidden md:block"></div>
    </slot>
    <PriceField
      v-model="prixBase"
      label="Prix du kWh — Base"
      hint="€ TTC / kWh"
      placeholder="ex : 0,1940"
    />
    <PriceField
      v-model="prixHP"
      label="Prix du kWh — Heures pleines"
      hint="€ TTC / kWh"
      placeholder="ex : 0,2065"
    />
    <PriceField
      v-model="prixHC"
      label="Prix du kWh — Heures creuses"
      hint="€ TTC / kWh"
      placeholder="ex : 0,1579"
    />
    <slot></slot>
  </div>
</template>
