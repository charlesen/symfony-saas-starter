import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["select"];
    static values = { theme: String };

    connect() {
        const theme = this.themeValue || this.getStoredTheme() || "system";
        this.applyTheme(theme);
        this._boundHandleSystemThemeChange = this.handleSystemThemeChange.bind(this);
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", this._boundHandleSystemThemeChange);
        if (this.hasSelectTarget) {
            this.selectTarget.value = theme;
        }
    }

    disconnect() {
        window.matchMedia("(prefers-color-scheme: dark)").removeEventListener("change", this._boundHandleSystemThemeChange);
    }

    change(event) {
        const theme = event.target.value;
        this.themeValue = theme;
        this.storeTheme(theme);
        this.applyTheme(theme);
    }

    applyTheme(theme) {
        if (theme === "system") {
            const systemIsDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            document.documentElement.classList.toggle("dark", systemIsDark);
        } else if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    }

    handleSystemThemeChange() {
        const theme = this.themeValue || this.getStoredTheme() || "system";
        if (theme === "system") {
            this.applyTheme("system");
        }
    }

    storeTheme(theme) {
        if (theme === "system") {
            localStorage.removeItem("theme");
        } else {
            localStorage.setItem("theme", theme);
        }
    }

    getStoredTheme() {
        return localStorage.getItem("theme");
    }
}
