<script setup>
import { provide, reactive, ref } from 'vue'
import UsagePanel from '../components/simulator/car/UsagePanel.vue'
import TcoPanel from '../components/simulator/car/TcoPanel.vue'
import { DEMO } from '../../script/simulator/carCost'

const tabs = [
  { id: 'usage', label: "01 · Simulation d'usage" },
  { id: 'tco', label: '02 · Coût global & rentabilité' },
]
const mode = ref('usage')

// Formulaire partagé entre les deux onglets : l'onglet coût global réutilise
// consommations, prix de l'énergie et kilométrage saisis dans l'onglet usage.
const form = reactive({ ...DEMO })
provide('carForm', form)

const resetDemo = () => Object.assign(form, DEMO)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center gap-2 mb-6">
      <div role="tablist" class="tabs tabs-boxed bg-base-200 flex-wrap">
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
      <button class="btn btn-ghost btn-sm ml-auto" @click="resetDemo">
        ↺ Valeurs de démo
      </button>
    </div>

    <!-- v-show (pas v-if) : chaque panneau garde son état quand on change d'onglet -->
    <section v-show="mode === 'usage'"><UsagePanel /></section>
    <section v-show="mode === 'tco'"><TcoPanel /></section>

    <p class="text-xs text-base-content/60 border-t border-base-300 mt-8 pt-4">
      Les valeurs de démo sont indicatives (tarifs France 2026, recharge
      majoritairement à domicile) et librement modifiables. Ce simulateur ne
      tient pas compte de la décote à la revente ni de l'évolution des prix de
      l'énergie : il ne remplace pas un devis ou une étude personnalisée.
    </p>
  </div>
</template>
