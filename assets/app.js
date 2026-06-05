import './stimulus_bootstrap.js'
import { Workbox } from 'workbox-window'
import { registerVueControllerComponents } from '@symfony/ux-vue'
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css'

registerVueControllerComponents(
  require.context('./vue/controllers', true, /\.vue$/),
)

if ('serviceWorker' in navigator && process.env.NODE_ENV === 'production') {
  window.addEventListener('load', () => {
    const wb = new Workbox('/service-worker.js') // Adjusted path

    wb.addEventListener('installed', (event) => {
      if (event.isUpdate) {
        // User experience for updates:
        // If there's an update, you might want to show a notification
        // or prompt the user to refresh the page.
        console.log('New content is available; please refresh.')
        // Example: if (confirm('New content is available. Refresh?')) { window.location.reload(); }
      }
    })

    wb.register()
      .then((registration) => {
        console.log('Service Worker registered with scope:', registration.scope)
      })
      .catch((error) => {
        console.error('Service Worker registration failed:', error)
      })
  })
}
