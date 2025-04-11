import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["feedback"];

  copy(event) {
    navigator.clipboard.writeText(this.element.dataset.copyContent).then(() => {
      if (this.hasFeedbackTarget) {
        this.feedbackTarget.classList.remove("hidden");
        setTimeout(() => {
          this.feedbackTarget.classList.add("hidden");
        }, 1500);
      }
    });
  }
}
