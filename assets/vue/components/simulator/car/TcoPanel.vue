<script setup>
import { computed, inject } from 'vue'
import PriceField from '../PriceField.vue'
import CostChart from './CostChart.vue'
import { km, tcoResult } from '../../../../script/simulator/carCost'
import { eur } from '../../../../script/simulator/format'

const form = inject('carForm')
const result = computed(() => tcoResult(form))

const eur0 = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
      })
    : '—'

const breakevenYear = computed(() => {
  const be = result.value.breakeven
  if (be.years === null) return null
  if (be.years === 0) return 0
  return Math.ceil(be.years)
})

const chartSeries = computed(() => [
  {
    label: 'Électrique',
    role: 'ev',
    data: result.value.rows.map((r) => r.ev),
  },
  {
    label: 'Thermique',
    role: 'ice',
    data: result.value.rows.map((r) => r.ice),
  },
])
const chartLabels = computed(() => result.value.rows.map((r) => r.year))
</script>

<template>
  <div>
    <div class="grid md:grid-cols-2 gap-5 mb-5">
      <div class="card bg-base-100 border border-info/40">
        <div class="card-body">
          <h2 class="card-title text-base">
            <span class="w-3 h-3 rounded-full bg-info inline-block"></span>
            Coûts fixes — électrique
          </h2>
          <p class="text-xs text-base-content/50 mb-2">
            Démo : Tesla Model 3 Propulsion
          </p>
          <div class="grid sm:grid-cols-2 gap-4">
            <PriceField
              v-model="form.evAchat"
              label="Prix d'achat"
              hint="€"
              step="500"
            />
            <PriceField
              v-model="form.evAide"
              label="Aides à l'achat"
              hint="bonus / prime déduits, €"
              step="100"
            />
            <PriceField
              v-model="form.evBorne"
              label="Borne de recharge"
              hint="installation, €"
              step="100"
            />
            <PriceField
              v-model="form.evEntretien"
              label="Entretien annuel"
              hint="€ / an"
              step="50"
            />
            <PriceField
              v-model="form.evAssurance"
              label="Assurance annuelle"
              hint="€ / an"
              step="50"
            />
          </div>
        </div>
      </div>

      <div class="card bg-base-100 border border-warning/40">
        <div class="card-body">
          <h2 class="card-title text-base">
            <span class="w-3 h-3 rounded-full bg-warning inline-block"></span>
            Coûts fixes — thermique
          </h2>
          <p class="text-xs text-base-content/50 mb-2">
            Démo : Renault Mégane IV 1.3 TCe 160 RS Line
          </p>
          <div class="grid sm:grid-cols-2 gap-4">
            <PriceField
              v-model="form.iceAchat"
              label="Prix d'achat"
              hint="€"
              step="500"
            />
            <PriceField
              v-model="form.iceEntretien"
              label="Entretien annuel"
              hint="€ / an"
              step="50"
            />
            <PriceField
              v-model="form.iceAssurance"
              label="Assurance annuelle"
              hint="€ / an"
              step="50"
            />
          </div>
        </div>
      </div>
    </div>

    <p class="text-sm text-base-content/60 mb-5">
      Le calcul reprend les consommations, prix de l'énergie et le kilométrage
      annuel (<strong>{{ km(Number(form.kmAn)) }}/an</strong>) renseignés dans
      l'onglet «&nbsp;Simulation d'usage&nbsp;».
    </p>

    <div class="card bg-primary text-primary-content mb-8">
      <div class="card-body py-5">
        <div class="text-xs uppercase tracking-widest opacity-70">
          Point d'inversion de rentabilité
        </div>
        <div class="text-3xl font-bold">{{ result.breakeven.value }}</div>
        <div class="text-sm opacity-80">{{ result.breakeven.sub }}</div>
      </div>
    </div>

    <h3 class="text-lg font-semibold text-secondary mb-1">Coût total cumulé</h3>
    <p class="text-sm text-base-content/60 mb-4">
      Achat (aides déduites) + borne + énergie + entretien + assurance, année
      après année. Le moment où la courbe électrique passe sous la courbe
      thermique marque le point où l'électrique devient le choix le plus
      économique.
    </p>

    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body">
        <CostChart
          title="Coût de possession cumulé"
          x-label="années"
          :labels="chartLabels"
          :series="chartSeries"
        />
      </div>
    </div>

    <div class="overflow-x-auto border border-base-300 rounded-lg">
      <table class="table table-zebra table-sm">
        <thead>
          <tr>
            <th>Année</th>
            <th class="text-right">Km cumulés</th>
            <th class="text-right">Électrique</th>
            <th class="text-right">Thermique</th>
            <th class="text-right">Écart</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in result.rows"
            :key="row.year"
            :class="{ 'bg-info/10 font-semibold': row.year === breakevenYear }"
          >
            <td>
              {{ row.year === 0 ? 'Achat' : 'Année ' + row.year }}
              <span
                v-if="row.year === breakevenYear"
                class="badge badge-info badge-sm ml-2"
                >point d'inversion</span
              >
            </td>
            <td class="text-right">{{ km(row.km) }}</td>
            <td class="text-right text-info">{{ eur(row.ev) }}</td>
            <td class="text-right text-warning">{{ eur(row.ice) }}</td>
            <td class="text-right font-medium">
              {{ row.diff >= 0 ? '−' : '+' }}{{ eur(Math.abs(row.diff)) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="text-xs text-base-content/50 mt-3">
      Écart annuel entre les deux véhicules : {{ eur0(result.gainAnnuel) }}
      (énergie + entretien + assurance), pour un écart de coûts fixes de
      {{ eur0(result.surcout) }} à l'achat.
    </p>
  </div>
</template>
