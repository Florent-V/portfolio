<script setup>
import { ref } from 'vue'
import SeuilPanel from '../components/simulator/SeuilPanel.vue'
import SituationPanel from '../components/simulator/SituationPanel.vue'
import CsvPanel from '../components/simulator/CsvPanel.vue'

const tabs = [
  { id: 'seuil', label: '01 · Seuil de rentabilité' },
  { id: 'situation', label: '02 · Ma situation' },
  { id: 'csv', label: '03 · Import CSV' },
]
const mode = ref('seuil')
</script>

<template>
  <div>
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

    <!-- v-show (pas v-if) : chaque panneau garde sa saisie quand on change d'onglet -->
    <section v-show="mode === 'seuil'"><SeuilPanel /></section>
    <section v-show="mode === 'situation'"><SituationPanel /></section>
    <section v-show="mode === 'csv'"><CsvPanel /></section>

    <p class="text-xs text-base-content/60 border-t border-base-300 mt-8 pt-4">
      Estimation basée uniquement sur les tarifs et données que vous saisissez :
      ils peuvent évoluer, et ce calcul ne remplace pas une simulation
      officielle de votre fournisseur.
    </p>
  </div>
</template>
