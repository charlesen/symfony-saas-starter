import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["modal"];

  open(event) {
    event.preventDefault();
    console.log("Open:", event.currentTarget);
    const itemElement = event.currentTarget;
    const modalElement = document.getElementById("app_modal");
    if (modalElement) {
      modalElement.querySelector("#app_modal_content").innerHTML =
        itemElement.dataset.content;
      modalElement.classList.remove("hidden");
    }
    // this.modalTarget.classList.remove("hidden");
  }

  close(event) {
    event.preventDefault();
    const modalElement = document.getElementById("app_modal");
    if (modalElement) {
      modalElement.classList.add("hidden");
    }
    // this.modalTarget.classList.add("hidden");
  }
}
