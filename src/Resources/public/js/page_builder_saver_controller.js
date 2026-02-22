import { Controller } from '@hotwired/stimulus';
import { showNotification } from '../utils/notification.js';

/*
 * Ce contrôleur gère l'enregistrement de la page ET relaie les actions globales
 * (comme le changement de langue) vers le contrôleur principal page-builder.
 */
export default class extends Controller {
    static targets = [
        "paramTitle",
        "paramSubtitle",
        "paramSlug",
        "paramStatus",
        "paramMetaTitle",
        "paramDescription",
        "paramOgImage"
    ];

    static values = {
        saveUrl: String,
        pageId: String
    }

    /**
     * Action appelée par les boutons de langue dans le header.
     * Relaie l'action vers le contrôleur page-builder situé sur le même élément (ou enfant).
     */
    switchLocale(event) {
        event.preventDefault();
        const pageBuilderController = this.application.getControllerForElementAndIdentifier(this.element, 'page-builder');

        // On récupère la locale depuis le bouton cliqué
        const locale = event.currentTarget.dataset.locale;

        if (pageBuilderController && locale) {
            // On appelle la méthode du contrôleur principal en passant directement la string
            pageBuilderController.switchLocale(locale);
        }
    }
    async save(event) {
        event.preventDefault();

        // 1. Récupération du contrôleur principal "page-builder"
        const pageBuilderController = this.application.getControllerForElementAndIdentifier(this.element, 'page-builder');

        if (!pageBuilderController) {
            console.error("Erreur : Impossible de trouver le contrôleur 'page-builder'.");
            showNotification("Erreur technique : Contrôleur introuvable.", "error");
            return;
        }

        // 2. Récupération des données du builder (Sections)
        const sectionsManager = pageBuilderController.sectionsManager;

        if (!sectionsManager) {
            console.error("Erreur : Le contrôleur 'page-builder' n'a pas de propriété 'sectionsManager'.");
            return;
        }

        const sections = sectionsManager.sections;

        // 3. Récupération des métadonnées du formulaire
        const title = this.hasParamTitleTarget ? this.paramTitleTarget.value : "";
        if (!title.trim()) {
            showNotification("Le titre de la page est obligatoire (Onglet Paramètres).", "warning");
            return;
        }

        const slug = this.hasParamSlugTarget ? this.paramSlugTarget.value : "";
        const status = this.hasParamStatusTarget ? (this.paramStatusTarget.checked ? 'online' : 'offline') : "";
        const description = this.hasParamDescriptionTarget ? this.paramDescriptionTarget.value : "";
        const metaTitle = this.hasParamMetaTitleTarget ? this.paramMetaTitleTarget.value : "";
        const subtitle = this.hasParamSubtitleTarget ? this.paramSubtitleTarget.value : "";
        const ogImage = this.hasParamOgImageTarget ? this.paramOgImageTarget.value : "";

        // On récupère la locale depuis le contrôleur principal
        const locale = pageBuilderController.currentLocale || 'fr';

        // 4. Construction du payload
        const payload = {
            id: this.pageIdValue || null,
            locale: locale,
            title: title,
            subtitle: subtitle,
            slug: slug,
            status: status,
            description: description,
            metaTitle: metaTitle,
            ogImage: ogImage,
            content: {
                sections: sections
            }
        };

        // 5. Envoi Ajax
        try {
            const btn = event.currentTarget;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            btn.disabled = true;

            const response = await fetch(this.saveUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            btn.innerHTML = originalContent;
            btn.disabled = false;

            if (response.ok && data.success) {
                // Mise à jour ID si création
                if (!this.pageIdValue && data.id) {
                    this.pageIdValue = data.id;
                    const newUrl = window.location.pathname.replace(/\/page-builder\/?$/, `/page-builder/${data.id}`);
                    window.history.replaceState({}, '', newUrl);
                }
                // Mise à jour slug
                if (this.hasParamSlugTarget && data.slug) {
                    this.paramSlugTarget.value = data.slug;
                }
                if (this.hasParamStatusTarget && data.status) {
                    this.paramStatusTarget.checked = (data.status === 'online');
                }

                showNotification(data.message || "Page enregistrée avec succès !", "success");
            } else {
                showNotification("Erreur lors de l'enregistrement : " + (data.error || "Inconnue"), "error");
            }

        } catch (error) {
            console.error(error);
            showNotification("Erreur de communication avec le serveur.", "error");
            if(event.currentTarget) {
                event.currentTarget.disabled = false;
                event.currentTarget.innerHTML = 'Enregistrer';
            }
        }
    }
}
