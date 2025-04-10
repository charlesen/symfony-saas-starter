import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  copy(event) {
    navigator.clipboard
      .writeText(event.currentTarget.dataset.copyContent)
      .then(() => {
        event.currentTarget.textContent = "Copié !";
        setTimeout(() => {
          event.currentTarget.textContent = "Copier";
        }, 1500);
      });
  }
}
