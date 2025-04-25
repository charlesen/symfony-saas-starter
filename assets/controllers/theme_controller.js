import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["select", "notification"];
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
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.showNotification(`${theme.charAt(0).toUpperCase() + theme.slice(1)} theme activated`);
                }
            })
            .catch(error => {
                console.error('Error updating theme preference:', error);
                // Revert to previous theme on error
                const previousTheme = this.getStoredTheme() || "system";
                this.applyTheme(previousTheme);
                this.selectTarget.value = previousTheme;
                this.showNotification('Failed to update theme preference', true);
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

    showNotification(message, isError = false) {
        // Créer une notification dynamiquement si elle n'existe pas dans le DOM
        if (!this.hasNotificationTarget) {
            const notification = document.createElement('div');
            notification.classList.add('fixed', 'bottom-4', 'right-4', 'px-4', 'py-2', 'rounded-lg', 'shadow-lg', 'transform', 'transition-all', 'duration-300', 'ease-in-out', 'translate-y-20', 'opacity-0');
            notification.dataset.themeTarget = 'notification';
            document.body.appendChild(notification);
            this.notificationTarget = notification;
        }
        
        // Configuration de la notification
        this.notificationTarget.textContent = message;
        this.notificationTarget.classList.remove('bg-green-500', 'text-white', 'bg-red-500');
        
        if (isError) {
            this.notificationTarget.classList.add('bg-red-500', 'text-white');
        } else {
            this.notificationTarget.classList.add('bg-green-500', 'text-white');
        }
        
        // Affichage de la notification
        this.notificationTarget.classList.remove('translate-y-20', 'opacity-0');
        this.notificationTarget.classList.add('translate-y-0', 'opacity-100');
        
        // Disparition après 3 secondes
        setTimeout(() => {
            this.notificationTarget.classList.remove('translate-y-0', 'opacity-100');
            this.notificationTarget.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }
}
