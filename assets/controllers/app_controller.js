import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["loadMoreButton"];

  intersect(entry) {
    if (entry[0].isIntersecting) {
      this.loadMoreButtonTarget.click();
    }
  }
}
