import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'typeSelect',
    'formatGroup',
    'contentGroup',
    'languageGroup',
    'imageGroup',
    'altTextGroup',
  ]

  connect() {
    this.update()
  }

  update() {
    const type = this.typeSelectTarget.value
    const isParagraphLike = type === 'paragraph' || type === 'summary'
    const isCode = type === 'code'
    const isImage = type === 'image'

    this.formatGroupTargets.forEach((el) =>
      el.classList.toggle('d-none', !isParagraphLike),
    )
    this.contentGroupTargets.forEach((el) =>
      el.classList.toggle('d-none', isImage),
    )
    this.languageGroupTargets.forEach((el) =>
      el.classList.toggle('d-none', !isCode),
    )
    this.imageGroupTargets.forEach((el) =>
      el.classList.toggle('d-none', !isImage),
    )
    this.altTextGroupTargets.forEach((el) =>
      el.classList.toggle('d-none', !isImage),
    )
  }
}
