<script setup>
import { computed, inject } from 'vue'
import PriceField from '../PriceField.vue'
import CostChart from './CostChart.vue'
import { km, usageResult } from '../../../../script/simulator/carCost'
import { eur } from '../../../../script/simulator/format'

const form = inject('carForm')
const result = computed(() => usageResult(form))

const eur3 = (n) =>
  isFinite(n)
    ? n.toLocaleString('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 3,
        maximumFractionDigits: 3,
      })
    : '—'

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
const chartLabels = computed(() =>
  result.value.rows.map((r) => r.km.toLocaleString('fr-FR')),
)
</script>

<template>
  <div>
    <div class="grid md:grid-cols-2 gap-5 mb-5">
      <div class="card bg-base-100 border border-info/40">
        <div class="card-body">
          <h2 class="card-title text-base">
            <span class="w-3 h-3 rounded-full bg-info inline-block"></span>
            Véhicule électrique
          </h2>
          <p class="text-xs text-base-content/50 mb-2">
            Démo : Tesla Model 3 Propulsion
          </p>
          <div class="grid sm:grid-cols-2 gap-4">
            <PriceField
              v-model="form.evConso"
              label="Consommation"
              hint="kWh / 100 km"
              step="0.1"
            />
            <PriceField
              v-model="form.evPrixDomicile"
              label="Prix du kWh à domicile"
              hint="€ / kWh"
              step="0.01"
            />
            <PriceField
              v-model="form.evPrixBorne"
              label="Prix du kWh en borne"
              hint="€ / kWh (recharge rapide)"
              step="0.01"
            />
            <PriceField
              v-model="form.evPartDomicile"
              label="Part rechargée à domicile"
              hint="% des recharges"
              step="5"
            />
          </div>
        </div>
      </div>

      <div class="card bg-base-100 border border-warning/40">
        <div class="card-body">
          <h2 class="card-title text-base">
            <span class="w-3 h-3 rounded-full bg-warning inline-block"></span>
            Véhicule thermique
          </h2>
          <p class="text-xs text-base-content/50 mb-2">
            Démo : Renault Mégane IV 1.3 TCe 160 RS Line
          </p>
          <div class="grid sm:grid-cols-2 gap-4">
            <PriceField
              v-model="form.iceConso"
              label="Consommation"
              hint="L / 100 km"
              step="0.1"
            />
            <PriceField
              v-model="form.icePrix"
              label="Prix du carburant"
              hint="€ / L"
              step="0.01"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body sm:flex-row sm:items-center gap-4">
        <div class="sm:w-64">
          <PriceField
            v-model="form.kmAn"
            label="Kilométrage annuel"
            hint="km / an"
            step="500"
          />
        </div>
        <p class="text-sm text-base-content/60 flex-1">
          Vous ne connaissez pas votre kilométrage ? Le tableau et le graphique
          ci-dessous montrent l'écart de coût pour toutes les distances, de
          5&nbsp;000 à 40&nbsp;000&nbsp;km par an.
        </p>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4 mb-5">
      <div class="stat bg-info/10 border border-info/30 rounded-lg">
        <div class="stat-title text-xs uppercase">Électrique</div>
        <div class="stat-value text-2xl text-info">
          {{ eur(result.evPar100) }}
        </div>
        <div class="stat-desc">
          aux 100 km · {{ eur3(result.evParKm) }} par km
        </div>
      </div>
      <div class="stat bg-warning/10 border border-warning/30 rounded-lg">
        <div class="stat-title text-xs uppercase">Thermique</div>
        <div class="stat-value text-2xl text-warning">
          {{ eur(result.icePar100) }}
        </div>
        <div class="stat-desc">
          aux 100 km · {{ eur3(result.iceParKm) }} par km
        </div>
      </div>
      <div class="stat bg-base-100 border border-base-300 rounded-lg">
        <div class="stat-title text-xs uppercase">Écart aux 100 km</div>
        <div class="stat-value text-2xl">
          {{ eur(Math.abs(result.ecart100)) }}
        </div>
        <div class="stat-desc">
          en faveur
          {{ result.ecart100 >= 0 ? "de l'électrique" : 'du thermique' }}
        </div>
      </div>
    </div>

    <div class="card bg-primary text-primary-content mb-8">
      <div class="card-body py-5 flex-row flex-wrap items-baseline gap-3">
        <span class="text-3xl font-bold">{{ result.verdict.figure }}</span>
        <span class="opacity-80 text-sm">{{ result.verdict.sub }}</span>
      </div>
    </div>

    <h3 class="text-lg font-semibold text-secondary mb-1">
      Coût selon la distance parcourue
    </h3>
    <p class="text-sm text-base-content/60 mb-4">
      Le coût d'énergie annuel de chaque véhicule, palier par palier. La colonne
      «&nbsp;Écart&nbsp;» montre la différence annuelle entre les deux.
    </p>

    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body">
        <CostChart
          title="Coût d'énergie annuel selon le kilométrage"
          x-label="km par an"
          :labels="chartLabels"
          :series="chartSeries"
          tooltip-suffix=" / an"
        />
      </div>
    </div>

    <div class="overflow-x-auto border border-base-300 rounded-lg">
      <table class="table table-zebra table-sm">
        <thead>
          <tr>
            <th>Distance / an</th>
            <th class="text-right">Électrique</th>
            <th class="text-right">Thermique</th>
            <th class="text-right">Écart / an</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in result.rows"
            :key="row.km"
            :class="{ 'font-semibold': row.km === Number(form.kmAn) }"
          >
            <td>{{ km(row.km) }}</td>
            <td class="text-right text-info">{{ eur(row.ev) }}</td>
            <td class="text-right text-warning">{{ eur(row.ice) }}</td>
            <td class="text-right font-medium">
              {{ row.diff >= 0 ? '−' : '+' }}{{ eur(Math.abs(row.diff)) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
