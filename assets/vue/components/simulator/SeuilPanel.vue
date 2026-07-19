<script setup>
import { onMounted, reactive, ref } from 'vue'
import { DEFAULTS, seuilResult } from '../../../script/simulator/pricing'
import TarifFields from './TarifFields.vue'
import PriceField from './PriceField.vue'
import ResultHeader from './ResultHeader.vue'
import FormulaDetails from './FormulaDetails.vue'

const form = reactive({ ...DEFAULTS, conso: 400 })
const result = ref(null)

const compute = () => {
  result.value = seuilResult(form)
}

// Les tarifs par défaut sont complets : montrer un premier résultat dès l'arrivée
// pour que l'outil soit immédiatement parlant.
onMounted(compute)
</script>

<template>
  <div class="card bg-base-100 border border-base-300 mb-5">
    <div class="card-body">
      <h2 class="card-title text-base">
        Quel % en heures creuses pour rentabiliser l'offre HP/HC ?
      </h2>
      <p class="text-sm text-base-content/60 mb-4">
        Entrez vos tarifs. On calcule la part de consommation à basculer en
        heures creuses pour que l'offre HP/HC devienne plus intéressante que
        l'offre Base.
      </p>
      <TarifFields
        v-model:abo-base="form.aboBase"
        v-model:abo-h-p-h-c="form.aboHPHC"
        v-model:prix-base="form.prixBase"
        v-model:prix-h-p="form.prixHP"
        v-model:prix-h-c="form.prixHC"
      >
        <template #third>
          <PriceField
            v-model="form.conso"
            label="Consommation de référence"
            hint="kWh / mois"
            step="1"
            placeholder="ex : 400"
          />
        </template>
      </TarifFields>
      <div>
        <button class="btn btn-primary mt-4" @click="compute">
          Calculer le seuil
        </button>
      </div>

      <div v-if="result" class="mt-5">
        <ResultHeader :result="result" />
        <div
          class="h-3.5 rounded-full relative bg-base-300 mt-4 mb-2"
          :style="{ background: result.gaugeBg }"
        >
          <div
            class="absolute -top-1.5 w-1 h-6 bg-base-content rounded"
            :style="{ left: result.markerLeft }"
          ></div>
        </div>
        <div class="flex justify-between text-xs text-base-content/50">
          <span>0 % en heures creuses</span><span>100 % en heures creuses</span>
        </div>
        <FormulaDetails :text="result.formula" />
      </div>
    </div>
  </div>
</template>
