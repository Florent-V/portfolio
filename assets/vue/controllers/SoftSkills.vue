<script setup>
import { ref, onMounted, onBeforeUnmount, defineProps } from 'vue'
import { Icon } from '@iconify/vue'

// eslint-disable-next-line no-unused-vars
const props = defineProps({
  skills: {
    type: Array,
    default: () => [],
  },
})

const flippedId = ref(null)
const containerRef = ref(null)

function toggleCard(id) {
  flippedId.value = flippedId.value === id ? null : id
}

function handleClickOutside(event) {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    flippedId.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside, true)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside, true)
})
</script>

<template>
  <div ref="containerRef" class="flex flex-wrap justify-center gap-4 md:gap-6">
    <div
      v-for="(skill, index) in skills"
      :key="skill.id"
      class="flip-card w-72"
      data-aos="fade-up"
      :data-aos-delay="index * 100"
    >
      <div
        class="flip-card-inner"
        :class="{ 'is-flipped': flippedId === skill.id }"
        @click.stop="toggleCard(skill.id)"
      >
        <!-- Face avant -->
        <div
          class="flip-card-front card card-compact bg-base-100 shadow-lg justify-center items-center text-center p-4 cursor-pointer"
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

        <!-- Face arrière -->
        <div
          class="flip-card-back card card-compact bg-base-100 shadow-lg justify-center items-center text-center p-4 cursor-pointer"
        >
          <div class="card-body items-center justify-center">
            <p class="text-base-content/80">{{ skill.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.flip-card {
  perspective: 1500px;
}

.flip-card:hover .flip-card-inner:not(.is-flipped) {
  animation: shake 0.5s ease-in-out;
}

.flip-card-inner {
  width: 100%;
  height: 100%;
  transition: transform 0.8s;
  transform-style: preserve-3d;
  display: grid;
}

.flip-card-inner.is-flipped {
  transform: rotateY(180deg);
}

.flip-card-front,
.flip-card-back {
  grid-area: 1 / 1 / 2 / 2;
  width: 100%;
  height: 100%;
  -webkit-backface-visibility: hidden; /* Safari */
  backface-visibility: hidden;
  border-radius: var(
    --rounded-box,
    1rem
  ); /* Assure que le radius de DaisyUI est appliqué */
  display: flex; /* Ajout pour le centrage */
  align-items: center; /* Ajout pour le centrage */
  justify-content: center; /* Ajout pour le centrage */
}

.flip-card-back {
  transform: rotateY(180deg);
}

@keyframes shake {
  10%,
  90% {
    transform: translate3d(-1px, 0, 0);
  }
  20%,
  80% {
    transform: translate3d(2px, 0, 0);
  }
  30%,
  50%,
  70% {
    transform: translate3d(-3px, 0, 0);
  }
  40%,
  60% {
    transform: translate3d(3px, 0, 0);
  }
}
</style>
