// controllers/flash_controller.js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    duration: { type: Number, default: 3000 }, // durée avant disparition (en ms)
  }

  connect() {
    setTimeout(() => {
      this.element.classList.add('fade-out')
      this.element.addEventListener(
        'transitionend',
        () => {
          this.element.remove()
        },
        { once: true },
      )
    }, this.durationValue)
  }
}
