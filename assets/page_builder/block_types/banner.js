import { t } from '../i18n.js';

export const bannerBlockType = {
    type: 'banner',

    create(generateId) {
        return {
            id: generateId(),
            type: 'banner',
            content: t('page.builder.block.banner.default_label'),
        };
    },

    renderPreview(block, ctx) {
        const content = document.createElement('div');
        content.classList.add('pb-block--banner');
        content.className = 'pb-banner-preview';
        content.classList.add('pb-block-content-preview');
        content.setAttribute('contenteditable', 'true');
        content.textContent = block.content || '';

        let saveTimeout = null;
        let isEditing = false;

        // Ouvrir la nav structure au focus (lors de la saisie)
        content.addEventListener('focus', () => {
            isEditing = true;
            content.dataset.editing = 'true';

            // Réinitialiser la sélection
            ctx.sectionsManager.resetSelection();

            // Sélectionner le bloc actuel
            let blockEl = document.querySelector('.pb-block[data-block-id="' + block.id + '"]');
            if (blockEl) {
                blockEl.classList.add('pb-block--selected');

                // Récupérer les informations de la section/row/column
                const sectionId = blockEl.dataset.sectionId;
                const rowId = blockEl.dataset.rowId;
                const columnId = blockEl.dataset.columnId;

                // Sélectionner dans le gestionnaire
                ctx.sectionsManager.selectBlock(sectionId, rowId, columnId, block.id);

                // Ouvrir l'onglet structure et afficher l'inspecteur de bloc
                ctx.activateTab('nav-structure');
                ctx.showBlockInspector();
            }
        });

        content.addEventListener('blur', () => {
            isEditing = false;
            delete content.dataset.editing;

            // Sauvegarder immédiatement au blur
            if (saveTimeout) {
                clearTimeout(saveTimeout);
            }
            // Mise à jour silencieuse sans re-render
            block.content = content.textContent;
        });

        // Mettre à jour le textarea lors de la saisie
        content.addEventListener('input', (e) => {
            // Annuler le timeout précédent
            if (saveTimeout) {
                clearTimeout(saveTimeout);
            }

            // Sauvegarder après 1 seconde d'inactivité
            saveTimeout = setTimeout(() => {
                // Mise à jour du contenu du bloc
                block.content = e.target.textContent;

                // Mise à jour du textarea de l'inspecteur
                if (ctx.blockContentInputTarget) {
                    ctx.blockContentInputTarget.value = block.content;
                }

                // Mettre à jour le titre de la section dans la structure
                const blockEl = document.querySelector('.pb-block[data-block-id="' + block.id + '"]');
                if (blockEl) {
                    const sectionId = blockEl.dataset.sectionId;
                    const bannerEl = document.querySelector('.pb-section-banner[data-section-id="' + sectionId + '"]');
                    if (bannerEl) {
                        bannerEl.textContent = block.content;
                    }
                }
            }, 1000);
        });

        // Empêcher la propagation des clics pour ne pas sélectionner le bloc
        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        const container = document.createElement('div');
        container.classList.add('pb-block-banner-container');
        container.appendChild(content);

        return {container, contentElement: content};
    },

    renderInspector(block, ctx) {

        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `

                <div class="mb-2">
                    <label class="form-label form-label-sm">Contenu du texte</label>
                    <textarea
                        class="form-control form-control-sm"
                        rows="4"
                        placeholder="Votre texte…"
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>
            `;
        }

        ctx.blockTitleTarget.textContent = t('page.builder.block_title.banner');
        ctx.blockContentInputTarget.value = block.content || '';
    },

    handleInspectorInput(block, event) {

        if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.content = event.target.value;
        }

        // Mettre à jour le contenu éditable dans le canvas
        // On cherche spécifiquement le bloc par son ID pour éviter de mettre à jour d'autres blocs identiques
        const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-banner-preview');
        contentElements.forEach(el => {
            if (el.textContent !== block.content) {
                el.textContent = block.content;
            }
        });
    },
};
