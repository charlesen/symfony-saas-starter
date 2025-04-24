import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        this.timeout = setTimeout(() => this.close(), 5000);
    }

    close() {
        this.element.remove();
    }

    disconnect() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    }
}
