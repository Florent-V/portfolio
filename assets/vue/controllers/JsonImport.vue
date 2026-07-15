<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  inputId: { type: String, required: true },
  submitId: { type: String, required: true },
  initialErrors: { type: Array, default: () => [] },
})

const isDragOver = ref(false)
const fileName = ref('')
const fileSize = ref(0)
const preview = ref('')
const hasFile = ref(false)
const errors = ref(props.initialErrors)

const fileLabel = computed(
  () => `${fileName.value} (${(fileSize.value / 1024).toFixed(1)} Ko)`,
)

const fileInput = () => document.getElementById(props.inputId)

const browse = () => fileInput().click()

const handleFile = (file) => {
  if (!file) return

  errors.value = []
  fileName.value = file.name
  fileSize.value = file.size
  hasFile.value = true

  const reader = new FileReader()
  reader.onload = (event) => {
    try {
      preview.value = JSON.stringify(JSON.parse(event.target.result), null, 2)
    } catch {
      preview.value = event.target.result
    }
  }
  reader.readAsText(file)
}

const onFileSelected = () => handleFile(fileInput().files[0])

onMounted(() => fileInput().addEventListener('change', onFileSelected))
onUnmounted(() => fileInput().removeEventListener('change', onFileSelected))

const onDragOver = (event) => {
  event.preventDefault()
  isDragOver.value = true
}

const onDragLeave = () => {
  isDragOver.value = false
}

const onDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false

  const file = event.dataTransfer.files[0]
  if (!file) return

  const dataTransfer = new DataTransfer()
  dataTransfer.items.add(file)
  fileInput().files = dataTransfer.files
  handleFile(file)
}

const submit = () => document.getElementById(props.submitId).click()
</script>

<template>
  <div>
    <div
      class="border-2 border-dashed rounded-xl p-10 text-center cursor-pointer transition-all duration-200 hover:border-primary hover:bg-primary/5"
      :class="isDragOver ? 'border-primary bg-primary/5' : 'border-base-300'"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
      @click="browse"
    >
      <div class="text-5xl text-base-content/30 mb-4">
        <i class="fa fa-file-code"></i>
      </div>
      <p class="font-semibold text-base-content mb-1">
        Glissez-déposez votre fichier JSON ici
      </p>
      <p class="text-sm text-base-content/50 mb-4">
        ou cliquez pour sélectionner
      </p>

      <button
        type="button"
        class="btn btn-outline btn-primary btn-sm gap-2"
        @click.stop="browse"
      >
        <i class="fa fa-folder-open"></i>Parcourir…
      </button>

      <p v-if="hasFile" class="text-success font-semibold mt-3">
        {{ fileLabel }}
      </p>

      <p v-for="error in errors" :key="error" class="text-error text-sm mt-2">
        {{ error }}
      </p>
    </div>

    <div v-if="preview" class="mt-6">
      <p class="font-medium mb-2 text-base-content">Aperçu du fichier</p>
      <pre
        class="bg-neutral text-neutral-content font-mono text-xs rounded-xl p-4 max-h-64 overflow-y-auto whitespace-pre-wrap break-all"
        >{{ preview }}</pre
      >
    </div>

    <div class="divider"></div>

    <div class="flex justify-end">
      <button
        type="button"
        class="btn btn-primary gap-2"
        :disabled="!hasFile"
        @click="submit"
      >
        <i class="fa fa-upload"></i>Importer l'article
      </button>
    </div>
  </div>
</template>
