import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  connect() {
    console.log('Hello from deleteAlert_controller.js')
    function alertCounter() {
      const alerts = document.getElementsByClassName('alert')
      console.log('alerts', alerts)
      for (let alert of alerts) {
        // alert.textContent += " || suppression dans 5 secondes";
        setTimeout(function () {
          alert.remove()
        }, 8000)
      }
    }

    alertCounter()
  }
}
