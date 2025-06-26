import { Controller } from '@hotwired/stimulus'

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
  connect() {
    console.log('Hello from toggleDarkMode_controller.js')

    // Theme toggle script
    const themeToggleCheckbox = document.getElementById('theme-toggle-checkbox')
    let currentTheme = localStorage.getItem('theme') || 'dark' // Default to 'dark'
    document.documentElement.setAttribute('data-theme', currentTheme)
    // This assumes 'light' is the "off" state (sun icon) and 'dark' (or any other) is "on" (moon icon)
    themeToggleCheckbox.checked = currentTheme === 'dark'

    themeToggleCheckbox.addEventListener('change', function () {
      if (this.checked) {
        document.documentElement.setAttribute('data-theme', 'dark')
        localStorage.setItem('theme', 'dark')
      } else {
        document.documentElement.setAttribute('data-theme', 'light')
        localStorage.setItem('theme', 'light')
      }
    })
  }
}
