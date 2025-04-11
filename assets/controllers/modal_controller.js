import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  open(event) {
    event.preventDefault();

    const itemElement = event.currentTarget;
    const modalElement = document.getElementById("app_modal");

    if (!modalElement) return;

    // Remplir contenu dynamique
    modalElement.querySelector("#app_modal_content").innerHTML =
      itemElement.dataset.content || "";
    modalElement.querySelector("#app_modal_title").innerHTML =
      itemElement.dataset.title || "Details";

    // Afficher modal
    modalElement.classList.remove("hidden");

    // Forcer le repaint pour animation propre
    requestAnimationFrame(() => {
      modalElement.classList.add("opacity-100");
    });

    modalElement.scrollTo(0, 0);

    // Gestion clic extérieur
    this.outsideClick = (e) => {
      if (!modalElement.querySelector("div.relative").contains(e.target)) {
        this.close(e);
      }
    };

    document.addEventListener("mousedown", this.outsideClick);
  }

  close(event) {
    if (event) {
      event.preventDefault();
    }

    const modalElement = document.getElementById("app_modal");

    if (!modalElement) return;

    modalElement.classList.remove("opacity-100");

    setTimeout(() => {
      modalElement.classList.add("hidden");
    }, 300); // doit matcher avec la durée de ta transition css

    document.removeEventListener("mousedown", this.outsideClick);
  }
}
