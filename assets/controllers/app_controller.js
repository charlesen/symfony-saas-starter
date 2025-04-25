import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["loadMoreButton"];

  connect() {
    // Réinitialise Flowbite après un chargement Turbo
    document.addEventListener('turbo:load', () => window.Flowbite?.init());
}

  intersect(entry) {
    if (entry[0].isIntersecting) {
      this.loadMoreButtonTarget.click();
    }
  }
}
