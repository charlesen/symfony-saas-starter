import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'html'];
    static values = {
        theme: String,
        updateUrl: String
    };

    connect() {
        // Initialiser le thème au chargement
        this.updateTheme(this.themeValue || 'system');
        
        // Écouter les changements de préférence système
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.themeValue === 'system') {
                this.applyTheme('system');
            }
        });
    }

    // Quand l'utilisateur change le thème dans le select
    async change(event) {
        const theme = event.target.value;
        await this.updateTheme(theme);
    }

    // Mettre à jour le thème
    async updateTheme(theme) {
        // Sauvegarder en BDD
        const formData = new FormData();
        formData.append('theme', theme);

        try {
            const response = await fetch(this.updateUrlValue, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // Mettre à jour le thème
            this.themeValue = theme;
            this.applyTheme(theme);
            
            // Mettre à jour le select
            if (this.hasSelectTarget) {
                this.selectTarget.value = theme;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Appliquer le thème
    applyTheme(theme) {
        if (theme === 'system') {
            // Utiliser la préférence système
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            this.htmlTarget.classList.remove('dark', 'light');
            this.htmlTarget.classList.add(systemTheme);
        } else {
            // Utiliser le thème choisi
            this.htmlTarget.classList.remove('dark', 'light');
            this.htmlTarget.classList.add(theme);
        }
    }
}
