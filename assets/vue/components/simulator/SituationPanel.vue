<script setup>
import { reactive, ref } from 'vue'
import { DEFAULTS, situationResult } from '../../../script/simulator/pricing'
import { eur } from '../../../script/simulator/format'
import TarifFields from './TarifFields.vue'
import PriceField from './PriceField.vue'
import ResultHeader from './ResultHeader.vue'
import FormulaDetails from './FormulaDetails.vue'

const form = reactive({ ...DEFAULTS, consoHP: null, consoHC: null })
const result = ref(null)

const compute = () => {
  result.value = situationResult(form)
}
</script>

<template>
  <div class="card bg-base-100 border border-base-300 mb-5">
    <div class="card-body">
      <h2 class="card-title text-base">Mon offre HP/HC est-elle rentable ?</h2>
      <p class="text-sm text-base-content/60 mb-4">
        Entrez vos tarifs et votre consommation réelle (sur une même période,
        par ex. un mois) pour comparer votre coût réel avec ce que vous auriez
        payé en Base.
      </p>
      <TarifFields
        v-model:abo-base="form.aboBase"
        v-model:abo-h-p-h-c="form.aboHPHC"
        v-model:prix-base="form.prixBase"
        v-model:prix-h-p="form.prixHP"
        v-model:prix-h-c="form.prixHC"
      >
        <PriceField
          v-model="form.consoHP"
          label="Ma consommation en heures pleines"
          hint="kWh sur la période"
          step="1"
          placeholder="ex : 210"
        />
        <PriceField
          v-model="form.consoHC"
          label="Ma consommation en heures creuses"
          hint="kWh sur la période"
          step="1"
          placeholder="ex : 190"
        />
      </TarifFields>
      <div>
        <button class="btn btn-primary mt-4" @click="compute">Comparer</button>
      </div>

      <div v-if="result" class="mt-5">
        <ResultHeader :result="result" />
        <div v-if="result.bars" class="space-y-4">
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span>Offre Base</span
              ><span class="font-mono">{{ eur(result.costBase) }}</span>
            </div>
            <div
              class="h-5 bg-base-200 border border-base-300 rounded overflow-hidden"
            >
              <div
                class="h-full transition-all duration-500"
                :class="
                  result.costBase < result.costHPHC
                    ? 'bg-success'
                    : 'bg-neutral'
                "
                :style="{ width: result.barBaseWidth }"
              ></div>
            </div>
          </div>
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span>Offre HP/HC</span
              ><span class="font-mono">{{ eur(result.costHPHC) }}</span>
            </div>
            <div
              class="h-5 bg-base-200 border border-base-300 rounded overflow-hidden"
            >
              <div
                class="h-full transition-all duration-500"
                :class="
                  result.costHPHC < result.costBase
                    ? 'bg-success'
                    : 'bg-neutral'
                "
                :style="{ width: result.barHPHCWidth }"
              ></div>
            </div>
          </div>
        </div>
        <FormulaDetails :text="result.formula" />
      </div>
    </div>
  </div>
</template>
