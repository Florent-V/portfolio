<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import {
  CategoryScale,
  Chart,
  Filler,
  Legend,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Title,
  Tooltip,
} from 'chart.js'
import { eur } from '../../../../script/simulator/format'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Legend,
  Tooltip,
  Filler,
  Title,
)

const props = defineProps({
  title: { type: String, required: true },
  xLabel: { type: String, required: true },
  labels: { type: Array, required: true },
  // [{ label, data, role: 'ev'|'ice' }] — palette validée (dataviz) :
  // aqua = électrique, orange = thermique, thermique en pointillés + carrés
  // pour rester lisible en cas de daltonisme.
  series: { type: Array, required: true },
  tooltipSuffix: { type: String, default: '' },
})

const PALETTE = {
  light: {
    ev: '#1baf7a',
    ice: '#eb6834',
    grid: 'rgba(0,0,0,.08)',
    ink: '#57534e',
  },
  dark: {
    ev: '#199e70',
    ice: '#d95926',
    grid: 'rgba(255,255,255,.1)',
    ink: '#a8a29e',
  },
}

const isDark = () =>
  document.documentElement.getAttribute('data-theme') === 'dark'

const canvas = ref(null)
let chart = null
let observer = null

// Étiquette directe en fin de courbe (encodage secondaire, en plus de la légende).
const endLabels = {
  id: 'endLabels',
  afterDatasetsDraw(c) {
    const { ctx } = c
    ctx.save()
    ctx.font = '600 11px sans-serif'
    ctx.textAlign = 'left'
    c.data.datasets.forEach((ds, i) => {
      const meta = c.getDatasetMeta(i)
      const last = meta.data[meta.data.length - 1]
      if (!last) return
      ctx.fillStyle = ds.borderColor
      ctx.fillText(ds.label, last.x + 6, last.y + (i === 0 ? -4 : 12))
    })
    ctx.restore()
  },
}

const buildDatasets = (colors) =>
  props.series.map((s) => ({
    label: s.label,
    data: s.data,
    borderColor: colors[s.role],
    backgroundColor: colors[s.role] + '1a',
    borderWidth: 2,
    borderDash: s.role === 'ice' ? [6, 4] : [],
    pointStyle: s.role === 'ice' ? 'rectRot' : 'circle',
    pointRadius: 4,
    pointHoverRadius: 6,
    fill: false,
    tension: 0.25,
  }))

const build = () => {
  const colors = PALETTE[isDark() ? 'dark' : 'light']
  chart = new Chart(canvas.value, {
    type: 'line',
    data: { labels: props.labels, datasets: buildDatasets(colors) },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { right: 76 } },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          labels: { usePointStyle: true, color: colors.ink },
        },
        tooltip: {
          callbacks: {
            label: (c) =>
              ` ${c.dataset.label} : ${eur(c.parsed.y)}${props.tooltipSuffix}`,
          },
        },
        title: {
          display: true,
          text: props.title,
          color: colors.ink,
          font: { weight: '700', size: 14 },
        },
      },
      scales: {
        x: {
          grid: { color: colors.grid },
          ticks: { color: colors.ink },
          title: { display: true, text: props.xLabel, color: colors.ink },
        },
        y: {
          grid: { color: colors.grid },
          ticks: { color: colors.ink, callback: (v) => eur(v) },
        },
      },
    },
    plugins: [endLabels],
  })
}

const refresh = () => {
  if (!chart) return
  const colors = PALETTE[isDark() ? 'dark' : 'light']
  chart.data.labels = props.labels
  chart.data.datasets = buildDatasets(colors)
  chart.options.plugins.legend.labels.color = colors.ink
  chart.options.plugins.title.color = colors.ink
  chart.options.scales.x.grid.color = colors.grid
  chart.options.scales.x.ticks.color = colors.ink
  chart.options.scales.x.title.color = colors.ink
  chart.options.scales.y.grid.color = colors.grid
  chart.options.scales.y.ticks.color = colors.ink
  chart.update()
}

watch(() => [props.labels, props.series], refresh, { deep: true })

onMounted(() => {
  build()
  observer = new MutationObserver(refresh)
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['data-theme'],
  })
})

onBeforeUnmount(() => {
  observer?.disconnect()
  chart?.destroy()
})
</script>

<template>
  <div class="relative h-72 md:h-80">
    <canvas ref="canvas"></canvas>
  </div>
</template>
