<script setup>
import { reactive, ref } from 'vue'
import { asNum } from '../../../script/simulator/format'
import {
  hcArrayToRanges,
  minutesInHC,
  parseHCRanges,
  rangesToText,
} from '../../../script/simulator/hcRanges'
import { analyzeConsumption, parseCsvFile } from '../../../script/simulator/csv'
import {
  csvNeutralResult,
  csvResult,
  DEFAULTS,
} from '../../../script/simulator/pricing'
import TarifFields from './TarifFields.vue'
import HourDial from './HourDial.vue'
import ResultHeader from './ResultHeader.vue'
import FormulaDetails from './FormulaDetails.vue'

/* ---- plages heures creuses ---- */
const hcHours = reactive(new Array(24).fill(false))
;[22, 23, 0, 1, 2, 3, 4, 5].forEach((h) => {
  hcHours[h] = true
})
const rangesText = ref(rangesToText(hcArrayToRanges(hcHours)))
const rangesWarning = ref('')

function toggleHour(h) {
  hcHours[h] = !hcHours[h]
  rangesText.value = rangesToText(hcArrayToRanges(hcHours))
}

function parseRanges() {
  const { ranges, invalidFragments } = parseHCRanges(rangesText.value)
  rangesWarning.value = invalidFragments.length
    ? `${invalidFragments.length} plage(s) non reconnue(s) et ignorée(s) : ${invalidFragments.join(', ')}`
    : ''
  return ranges
}

function applyRangesText() {
  const ranges = parseRanges()
  for (let h = 0; h < 24; h++) {
    hcHours[h] = minutesInHC(h * 60 + 30, ranges)
  }
}

/* ---- fichier CSV ---- */
const csvHeader = ref(null)
let csvRows = null
const csvStatus = ref('')
const csvStatusOk = ref(true)
const csvFormat = ref('startend')
const cols = reactive({ start: 0, end: 1, power: 2, date: 0, val: 1 })

function onCsvFile(e) {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => {
    try {
      const parsed = parseCsvFile(ev.target.result)
      csvRows = parsed.rows
      csvFormat.value = parsed.format
      cols.start = parsed.startIdx
      cols.end = parsed.endIdx
      cols.power = parsed.powerIdx
      cols.date = parsed.dateIdx
      cols.val = parsed.valIdx
      csvHeader.value = parsed.header
      csvStatusOk.value = true
      csvStatus.value = `${csvRows.length} lignes lues. Vérifiez le format et les colonnes détectées ci-dessous.`
    } catch (err) {
      csvStatusOk.value = false
      csvStatus.value = 'Impossible de lire ce fichier : ' + err.message
      csvHeader.value = null
      csvRows = null
    }
  }
  reader.readAsText(file)
}

/* ---- tarifs + calcul ---- */
const form = reactive({ ...DEFAULTS })
const result = ref(null)

function compute() {
  if (!csvRows) {
    result.value = csvNeutralResult(
      'Aucun fichier',
      "Importez un fichier CSV avant de lancer l'analyse.",
    )
    return
  }
  const tarifs = {
    aboBase: asNum(form.aboBase),
    aboHPHC: asNum(form.aboHPHC),
    prixBase: asNum(form.prixBase),
    prixHP: asNum(form.prixHP),
    prixHC: asNum(form.prixHC),
  }
  const values = Object.values(tarifs)
  if (values.some((v) => !isFinite(v))) {
    result.value = csvNeutralResult(
      'Tarifs manquants',
      'Renseignez les tarifs des deux offres.',
    )
    return
  }
  if (values.some((v) => v < 0)) {
    result.value = csvNeutralResult(
      'Valeur invalide',
      'Les abonnements et prix du kWh doivent être positifs.',
    )
    return
  }

  const analysis = analyzeConsumption(csvRows, {
    format: csvFormat.value,
    cols,
    ranges: parseRanges(),
  })
  if (analysis.usedRows === 0) {
    result.value = csvNeutralResult(
      'Lecture impossible',
      'Aucune ligne exploitable : vérifiez le format et les colonnes sélectionnées.',
    )
    return
  }

  result.value = csvResult(analysis, tarifs, {
    rangesText: rangesText.value,
    format: csvFormat.value,
  })
}
</script>

<template>
  <div>
    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body">
        <h2 class="card-title text-base">Plages horaires heures creuses</h2>
        <p class="text-sm text-base-content/60 mb-4">
          Cliquez sur le cadran pour marquer vos heures creuses, ou saisissez
          directement les plages (ex :
          <span class="font-mono">13h-15h, 00h-06h</span>). Plusieurs plages
          possibles.
        </p>
        <div class="flex flex-wrap gap-6 items-start">
          <HourDial :hours="hcHours" @toggle="toggleHour" />
          <div class="flex-1 min-w-60">
            <label class="label-text font-semibold block mb-1">
              Plages heures creuses
              <span class="block font-normal text-base-content/50 text-xs"
                >séparées par une virgule</span
              >
            </label>
            <input
              v-model="rangesText"
              type="text"
              placeholder="ex : 13h-15h, 00h-06h"
              class="input input-bordered w-full font-mono"
            />
            <p v-if="rangesWarning" class="text-error text-xs mt-2">
              {{ rangesWarning }}
            </p>
            <button
              class="btn btn-outline btn-sm mt-3"
              @click="applyRangesText"
            >
              Appliquer le texte au cadran
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body">
        <h2 class="card-title text-base">Fichier de consommation</h2>
        <p class="text-sm text-base-content/60 mb-4">
          Deux formats sont acceptés : une colonne date + une consommation en
          kWh, ou deux colonnes Début/Fin + une puissance en kW (format export
          Enedis/Linky). L'appli convertit automatiquement la puissance en
          énergie à partir de la durée de chaque intervalle. Tout le traitement
          reste dans votre navigateur : le fichier n'est jamais envoyé sur un
          serveur.
        </p>
        <div
          class="border-2 border-dashed border-base-300 rounded-xl p-6 text-center text-sm text-base-content/60"
        >
          Choisissez votre fichier CSV
          <br />
          <input
            type="file"
            accept=".csv,text/csv"
            class="file-input file-input-bordered file-input-sm mt-3"
            @change="onCsvFile"
          />
        </div>
        <div
          v-if="csvStatus"
          class="text-sm mt-2"
          :class="csvStatusOk ? 'text-success' : 'text-error'"
        >
          {{ csvStatus }}
        </div>

        <div v-if="csvHeader" class="mt-4">
          <div class="max-w-sm">
            <label class="label-text font-semibold block mb-1"
              >Format détecté du fichier</label
            >
            <select v-model="csvFormat" class="select select-bordered w-full">
              <option value="startend">
                2 colonnes Début / Fin + puissance (kW)
              </option>
              <option value="single">
                1 colonne date/heure + consommation (kWh)
              </option>
            </select>
          </div>

          <div
            v-if="csvFormat === 'startend'"
            class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3"
          >
            <div>
              <label class="label-text font-semibold block mb-1"
                >Colonne « Début »</label
              >
              <select
                v-model.number="cols.start"
                class="select select-bordered w-full"
              >
                <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                  {{ h || `Colonne ${i + 1}` }}
                </option>
              </select>
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Colonne « Fin »</label
              >
              <select
                v-model.number="cols.end"
                class="select select-bordered w-full"
              >
                <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                  {{ h || `Colonne ${i + 1}` }}
                </option>
              </select>
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Colonne puissance (kW)</label
              >
              <select
                v-model.number="cols.power"
                class="select select-bordered w-full"
              >
                <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                  {{ h || `Colonne ${i + 1}` }}
                </option>
              </select>
            </div>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
              <label class="label-text font-semibold block mb-1"
                >Colonne date / heure</label
              >
              <select
                v-model.number="cols.date"
                class="select select-bordered w-full"
              >
                <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                  {{ h || `Colonne ${i + 1}` }}
                </option>
              </select>
            </div>
            <div>
              <label class="label-text font-semibold block mb-1"
                >Colonne consommation (kWh)</label
              >
              <select
                v-model.number="cols.val"
                class="select select-bordered w-full"
              >
                <option v-for="(h, i) in csvHeader" :key="i" :value="i">
                  {{ h || `Colonne ${i + 1}` }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 border border-base-300 mb-5">
      <div class="card-body">
        <h2 class="card-title text-base">Tarifs des deux offres</h2>
        <TarifFields
          v-model:abo-base="form.aboBase"
          v-model:abo-h-p-h-c="form.aboHPHC"
          v-model:prix-base="form.prixBase"
          v-model:prix-h-p="form.prixHP"
          v-model:prix-h-c="form.prixHC"
        />
        <div>
          <button class="btn btn-primary mt-4" @click="compute">
            Analyser
          </button>
        </div>

        <div v-if="result" class="mt-5">
          <ResultHeader :result="result" />
          <div v-if="result.rows.length" class="overflow-x-auto">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Détail</th>
                  <th class="text-right">Base</th>
                  <th class="text-right">HP/HC</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, i) in result.rows"
                  :key="i"
                  :class="{ 'text-success font-semibold': row.win }"
                >
                  <td class="text-base-content/60">{{ row.label }}</td>
                  <td v-if="row.span" colspan="2" class="text-right font-mono">
                    {{ row.value }}
                  </td>
                  <template v-else>
                    <td class="text-right font-mono">{{ row.base }}</td>
                    <td class="text-right font-mono">{{ row.hphc }}</td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
          <FormulaDetails
            summary="Pourquoi des lignes ont-elles été ignorées ?"
            :text="result.skipDetail"
          />
          <FormulaDetails
            summary="Hypothèses du calcul"
            :text="result.formula"
          />
        </div>
      </div>
    </div>
  </div>
</template>
