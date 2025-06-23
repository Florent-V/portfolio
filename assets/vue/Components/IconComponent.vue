<script setup>
import { defineProps } from 'vue'
import { OhVueIcon, addIcons } from 'oh-vue-icons'
import { MdLightmodeOutlined, MdDarkmodeOutlined } from 'oh-vue-icons/icons' // Ajoutez ici toutes les icônes que vous souhaitez utiliser

// Ajouter les icônes
addIcons(MdLightmodeOutlined, MdDarkmodeOutlined)

// Définir les props
defineProps({
  iconName: {
    type: String,
    required: true,
  },
})
</script>

<template>
  <OhVueIcon :name="iconName" />
</template>
