import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["select"];
    static values = { theme: String, themeUpdateUrl: String };

    connect() {
        const theme = this.themeValue || this.getStoredTheme() || "system";
        this.applyTheme(theme);
        this._boundHandleSystemThemeChange = this.handleSystemThemeChange.bind(this);
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", this._boundHandleSystemThemeChange);
        if (this.hasSelectTarget) {
            this.selectTarget.value = theme;
        }
        // If system, set cookie on load
        if (theme === "system") {
            const systemIsDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            document.cookie = `system-theme=${systemIsDark ? 'dark' : 'light'}; path=/; SameSite=Lax`;
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
        // Save user preference to backend if possible
        if (this.hasValue("themeUpdateUrl")) {
            fetch(this.themeUpdateUrlValue, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ theme }),
            });
        }
    }

    applyTheme(theme) {
        // Always remove both 'dark' and 'light' classes first
        document.documentElement.classList.remove("dark", "light");
        if (theme === "system") {
            const systemIsDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
            document.documentElement.classList.add(systemIsDark ? "dark" : "light");
            // Store system theme in cookie for backend
            document.cookie = `system-theme=${systemIsDark ? 'dark' : 'light'}; path=/; SameSite=Lax`;
        } else if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.add("light");
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
