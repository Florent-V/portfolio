import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  connect() {
    document.addEventListener('turbo:load', function () {
      const carouselContainers = document.querySelectorAll(
        '.carousel-container',
      )
      carouselContainers.forEach((carouselContainer) => {
        const carouselTrack = carouselContainer.querySelector('.carousel-track')
        const carouselSlides = carouselTrack.children
        const carouselPrevButton =
          carouselContainer.querySelector('.carousel-prev')
        const carouselNextButton =
          carouselContainer.querySelector('.carousel-next')
        const carouselDots = carouselContainer.querySelectorAll('.carousel-dot')

        let currentSlideIndex = 0

        // Set up carousel dots
        carouselDots.forEach((dot, index) => {
          dot.addEventListener('click', () => {
            currentSlideIndex = index
            updateCarousel()
          })
        })

        // Set up navigation buttons
        carouselPrevButton.addEventListener('click', () => {
          currentSlideIndex =
            (currentSlideIndex - 1 + carouselSlides.length) %
            carouselSlides.length
          updateCarousel()
        })

        carouselNextButton.addEventListener('click', () => {
          currentSlideIndex = (currentSlideIndex + 1) % carouselSlides.length
          updateCarousel()
        })

        // Update carousel
        function updateCarousel() {
          const translateX = -currentSlideIndex * carouselSlides[0].offsetWidth
          carouselTrack.style.transform = `translateX(${translateX}px)`

          // Update active dot
          carouselDots.forEach((dot, index) => {
            dot.classList.remove('bg-white')
            if (index === currentSlideIndex) {
              dot.classList.add('bg-white')
            }
          })
        }

        // Initialize carousel
        updateCarousel()
      })
    })
  }
}
