<template>
  <div class="relative">
    <!-- Grid of cards -->
    <div class="flex flex-wrap justify-center gap-4 md:gap-6">
      <div
        v-for="(skill, index) in skills"
        :key="skill.id"
        class="card w-72 bg-base-100 shadow-lg cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
        data-aos="fade-up"
        :data-aos-delay="index * 100"
        @click="selectSkill(skill)"
      >
        <div class="card-body items-center text-center justify-center">
          <Icon
            v-if="skill.icon"
            :icon="skill.icon"
            height="32"
            width="32"
            class="mb-2 text-primary"
          />
          <h3 class="card-title text-lg">{{ skill.name }}</h3>
        </div>
      </div>
    </div>

    <!-- Modal Overlay -->
    <Transition name="modal-fade">
      <div
        v-if="selectedSkill"
        class="fixed inset-0 bg-black/70 z-40 backdrop-blur-sm rounded-lg"
        @click="deselectSkill"
      ></div>
    </Transition>

    <!-- Expanded Card Modal -->
    <Transition name="modal-zoom">
      <div
        v-if="selectedSkill"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        @click.self="deselectSkill"
      >
        <div class="flip-card w-[22rem] md:w-[28rem]">
          <div
            class="flip-card-inner"
            :class="{ 'is-flipped': isFlipped }"
            @click="isFlipped = !isFlipped"
          >
            <!-- Front Face -->
            <div
              class="flip-card-face flip-card-front card bg-base-100 shadow-2xl p-4"
            >
              <div class="card-body items-center text-center justify-center">
                <Icon
                  :icon="selectedSkill.icon"
                  height="48"
                  width="48"
                  class="mb-4 text-primary"
                />
                <h3 class="card-title text-2xl">{{ selectedSkill.name }}</h3>
                <p class="text-sm text-base-content/60 mt-4">
                  Cliquez pour voir la description
                </p>
              </div>
            </div>
            <!-- Back Face -->
            <div
              class="flip-card-face flip-card-back card bg-base-100 shadow-2xl p-6"
            >
              <div class="card-body text-left">
                <div class="flex items-center mb-4">
                  <Icon
                    :icon="selectedSkill.icon"
                    height="32"
                    width="32"
                    class="mr-4 text-primary shrink-0"
                  />
                  <h3 class="card-title text-xl font-bold">
                    {{ selectedSkill.name }}
                  </h3>
                </div>
                <p class="text-base-content/90 text-justify">
                  {{ selectedSkill.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <button
          class="btn btn-sm btn-circle btn-ghost absolute top-4 right-4 text-white"
          @click.stop="deselectSkill"
        >
          ✕
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
  skills: {
    type: Array,
    required: true,
  },
})

const selectedSkill = ref(null)
const isFlipped = ref(false)

const selectSkill = (skill) => {
  selectedSkill.value = skill
  document.body.style.overflow = 'hidden' // Prevent background scrolling
  // Flip the card shortly after the zoom animation starts
  setTimeout(() => {
    isFlipped.value = true
  }, 200)
}

const deselectSkill = () => {
  selectedSkill.value = null
  document.body.style.overflow = '' // Restore background scrolling
  // Reset flip state after the modal has closed
  setTimeout(() => {
    isFlipped.value = false
  }, 400)
}

// Handle Escape key to close modal
const handleKeydown = (e) => {
  if (e.key === 'Escape' && selectedSkill.value) {
    deselectSkill()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = '' // Ensure scroll is restored
})
</script>

<style scoped>
/* Flip card base styles */
.flip-card {
  perspective: 1500px;
}
.flip-card-inner {
  position: relative;
  width: 100%;
  transition: transform 0.8s;
  transform-style: preserve-3d;
  display: grid;
}
.flip-card-inner.is-flipped {
  transform: rotateY(180deg);
}
.flip-card-face {
  grid-area: 1 / 1 / 2 / 2;
  width: 100%;
  height: 100%;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--rounded-box, 1rem);
  overflow: hidden; /* Prevents content from spilling during animation */
}
.flip-card-back {
  transform: rotateY(180deg);
}

/* Modal Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.4s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-zoom-enter-active,
.modal-zoom-leave-active {
  transition: all 0.4s ease;
}
.modal-zoom-enter-from,
.modal-zoom-leave-to {
  opacity: 0;
  transform: scale(0.7);
}
</style>
