import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["sidebar"];

  toggle() {
    this.sidebarTarget.classList.toggle("-translate-x-full");
    this.sidebarTarget.classList.toggle("sm:translate-x-0");
  }
}
