import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  connect() {
    // Dans app.js
    document.addEventListener('turbo:load', function () {
      // Sélectionner toutes les images de projet
      const projectImages = document.querySelectorAll('.project-image')

      projectImages.forEach((img) => {
        // Si l'image est déjà chargée
        if (img.complete) {
          adjustImageDisplay(img)
        } else {
          // Sinon, attendre le chargement
          img.addEventListener('load', function () {
            adjustImageDisplay(this)
          })
        }
      })
    })

    function adjustImageDisplay(img) {
      const aspectRatio = img.naturalWidth / img.naturalHeight
      const figure = img.parentElement

      if (aspectRatio > 1) {
        img.classList.remove('object-contain')
        img.classList.add('object-cover')
        figure.classList.remove('bg-base-200')
      } else {
        img.classList.remove('object-cover')
        img.classList.add('object-contain')
        figure.classList.add('bg-base-200')
      }
    }
  }
}
