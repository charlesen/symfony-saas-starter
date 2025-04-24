import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["sidebar"];
  static values = {
    backdropClasses: { type: String, default: "bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-30" }
  }

  connect() {
    // Ajouter un gestionnaire d'événements pour fermer le drawer quand on clique sur le backdrop
    document.addEventListener('click', this.handleBackdropClick.bind(this));
  }

  disconnect() {
    // Nettoyer les gestionnaires d'événements lors de la déconnexion du contrôleur
    document.removeEventListener('click', this.handleBackdropClick.bind(this));
    this.removeBackdrop();
  }

  toggle() {
    if (this.sidebarTarget.classList.contains('-translate-x-full')) {
      this.open();
    } else {
      this.close();
    }
  }

  open() {
    // Ouvrir le drawer
    this.sidebarTarget.classList.remove('-translate-x-full');
    this.addBackdrop();
  }

  close() {
    // Fermer le drawer
    this.sidebarTarget.classList.add('-translate-x-full');
    this.removeBackdrop();
  }

  addBackdrop() {
    // Ajouter le backdrop seulement s'il n'existe pas déjà
    if (!document.querySelector('[drawer-backdrop]')) {
      document.body.insertAdjacentHTML('beforeend', `<div drawer-backdrop="" class="${this.backdropClassesValue}"></div>`);
    }
  }

  removeBackdrop() {
    // Supprimer le backdrop s'il existe
    const backdrop = document.querySelector('[drawer-backdrop]');
    if (backdrop) {
      backdrop.remove();
    }
  }

  handleBackdropClick(event) {
    // Fermer le drawer quand on clique sur le backdrop
    if (event.target.hasAttribute('drawer-backdrop')) {
      this.close();
    }
  }
}
