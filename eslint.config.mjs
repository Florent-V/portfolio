import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import pluginPrettier from 'eslint-plugin-prettier'
import prettier from 'eslint-config-prettier'
import globals from 'globals'

export default [
  {
    name: 'app/files-to-lint',
    files: ['**/*.{js,mjs,jsx,vue}'],
    plugins: {
      prettier: pluginPrettier,
    },
    rules: {
      // Règles JavaScript
      eqeqeq: ['error', 'always'], // Toujours utiliser === et !==
      'no-console': ['warn'], // Avertit en cas d'utilisation de console.log
      'no-unused-vars': ['warn'], // Avertit sur les variables inutilisées
      semi: ['error', 'never'], // Pas de point-virgule
      quotes: ['error', 'single'], // Utilisation des guillemets simples
      // Règles Vue.js
      'vue/multi-word-component-names': 'off', // Désactive l'obligation d'avoir des noms de composants en plusieurs mots
      'vue/valid-v-bind': 'error', // Vérifie l'utilisation correcte de `v-bind`
      'vue/no-multiple-template-root': 'error', // Un seul élément racine dans le template
      // Ajoute tes règles personnalisées ici
      'prettier/prettier': 'error',
    },
  },
  {
    name: 'app/files-to-ignore',
    ignores: [
      '**/dist/**',
      '**/dist-ssr/**',
      '**/coverage/**',
      'vendor/',
      'public/',
      'assets/vendor/',
    ],
  },
  js.configs.recommended, // Règles de base ESLint pour JavaScript
  ...pluginVue.configs['flat/recommended'], // Règles recommandées pour Vue.js 3
  prettier, // Désactive les règles ESLint en conflit avec Prettier
  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        myCustomGlobal: 'readonly',
      },
    },
    // ...other config
  },
]
