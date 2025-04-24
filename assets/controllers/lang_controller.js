import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["select"];

    connect() {
        // Stocke la valeur initiale du select
        this.initialLocale = this.hasSelectTarget ? this.selectTarget.value : null;
        // Écoute la réussite d'une action LiveComponent
        window.addEventListener("live:action:success", this.afterSave.bind(this));
        // Écoute l'event custom dispatché par LiveComponent côté PHP
        window.addEventListener("profile:lang-changed", this.onLangChanged.bind(this));
    }

    disconnect() {
        window.removeEventListener("live:action:success", this.afterSave.bind(this));
        window.removeEventListener("profile:lang-changed", this.onLangChanged.bind(this));
    }

    afterSave(event) {
        // On garde le comportement flash dynamique si la langue ne change pas
    }

    onLangChanged(event) {
        const newLocale = event.detail.locale;
        if (newLocale && newLocale !== this.initialLocale) {
            // Reconstruit l'URL proprement (en gardant query/hash)
            const url = window.location.pathname.replace(/^\/[a-z]{2}(?=\/|$)/, '/' + newLocale);
            window.location.href = url + window.location.search + window.location.hash;
        }
    }
}
