export const titleBlockType = {
    type: 'title',

    create(generateId) {
        return {
            id: generateId(),
            type: 'title',
            content: 'Titre',
            size: 'h2',
            fontSize: '32px',
            class: '',
            horizontalAlignment: 'center'
        };
    },

    parseSize(sizeValue) {
        const parts = sizeValue.split('-');
        const tag = parts[0];
        const classNamesString = parts[1] || '';
        let classNames = classNamesString ? classNamesString.split('_') : [];
        let fontSize = null;

        // Si le dernier élément est un nombre, on le traite comme fontSize
        if (classNames.length > 0) {
            const lastPart = classNames[classNames.length - 1];
            if (!isNaN(lastPart) && lastPart !== '') {
                fontSize = lastPart + 'px';
                classNames.pop(); // Retirer de la liste des classes
            }
        }

        return {
            tag: tag,           // h2, h3, h4, etc.
            classNames: classNames, // ['heading'], etc.
            fontSize: fontSize      // '36px' ou null
        };
    },

    renderPreview(block, ctx) {
        const content = document.createElement('div');
        content.classList.add('pb-block--title');
        content.className = 'pb-title-preview';
        content.classList.add('pb-block-content-preview');
        const horizontalAlignmentClass = 'text-' + block.horizontalAlignment;
        content.classList.add(horizontalAlignmentClass);

        // Parser la taille pour extraire la balise, les classes et la taille de police
        const parsed = this.parseSize(block.size || 'h2');
        if (parsed.classNames && parsed.classNames.length > 0) {
            parsed.classNames.forEach(className => {
                content.classList.add(className);
            });
        }

        if (parsed.fontSize) {
            content.style.fontSize = parsed.fontSize;
        }

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
                    const titleEl = document.querySelector('.pb-section-title[data-section-id="' + sectionId + '"]');
                    if (titleEl) {
                        titleEl.textContent = block.content;
                    }
                }
            }, 1000);
        });

        // Empêcher la propagation des clics pour ne pas sélectionner le bloc
        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        const container = document.createElement('div');
        //const horizontalAlignmentClass = 'text-' + (block.horizontalAlignment || 'left');
        //container.classList.add(horizontalAlignmentClass);
        container.appendChild(content);

        return {container, contentElement: content};
    },

    renderInspector(block, ctx) {

        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2">
                    <label class="form-label form-label-sm d-block mb-1">Alignement horizontal</label>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill ${block.horizontalAlignment === 'left' ? 'active' : ''}"
                                data-alignment="left"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockContent"
                                title="Aligné à gauche">
                            <i class="fas fa-align-left"></i>
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill ${block.horizontalAlignment === 'center' ? 'active' : ''}"
                                data-alignment="center"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockContent"
                                title="Centré">
                            <i class="fas fa-align-center"></i>
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill ${block.horizontalAlignment === 'right' ? 'active' : ''}"
                                data-alignment="right"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockContent"
                                title="Aligné à droite">
                            <i class="fas fa-align-right"></i>
                        </button>
                    </div>
                </div>
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

                <div class="mb-2">
                    <label class="form-label form-label-sm" for="title-style">Style du titre</label>
                    <select
                        class="form-select form-select-sm"
                        id="title-style" name="title-style"
                        data-page-builder-target="blockStyleInput"
                        data-action="change->page-builder#updateBlockContent"
                    >
                        <option value="h2" ${block.size === 'h2' ? 'selected' : ''}>Style</option>
                        <option value="h2-heading_32" ${block.size === 'h2-heading_32' ? 'selected' : ''}>Titre souligné</option>
                        <option value="h2-heading_36" ${block.size === 'h2-heading_36' ? 'selected' : ''}>Titre en-tête souligné</option>
                        <option value="h2-secondary_32" ${block.size === 'h2-secondary_32' ? 'selected' : ''}>Titre jaune</option>
                        <option value="h2-dark_30" ${block.size === 'h2-dark_30' ? 'selected' : ''}>Titre foncé</option>
                    </select>
                </div>
            `;
        }

        ctx.blockTitleTarget.textContent = 'Bloc Titre';
        ctx.blockContentInputTarget.value = block.content || '';
    },

    handleInspectorInput(block, event) {

        if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.content = event.target.value;
        }

        // Mettre à jour le contenu éditable dans le canvas
        // On cherche spécifiquement le bloc par son ID pour éviter de mettre à jour d'autres blocs identiques
        const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-title-preview');
        contentElements.forEach(el => {
            if (el.textContent !== block.content) {
                el.textContent = block.content;
            }
        });

        if (event.target.dataset.pageBuilderTarget === 'blockAlignmentButton' || event.target.closest('[data-page-builder-target="blockAlignmentButton"]')) {
            const button = event.target.dataset.pageBuilderTarget === 'blockAlignmentButton' ? event.target : event.target.closest('[data-page-builder-target="blockAlignmentButton"]');
            const alignment = button.dataset.alignment;
            block.horizontalAlignment = alignment;

            const blockEl = document.querySelector('.pb-block[data-block-id="' + block.id + '"]');
            if (blockEl) {
                const titlePreview = blockEl.querySelector('.pb-title-preview');
                const container = titlePreview ? titlePreview.parentElement : null;
                if (container) {
                    container.className = 'text-' + alignment;
                }
            }

            // Mettre à jour l'état actif des boutons dans l'inspecteur
            const inspector = button.closest('.pb-inspector-block-content');
            if (inspector) {
                inspector.querySelectorAll('[data-page-builder-target="blockAlignmentButton"]').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.alignment === alignment);
                });
            }
        }

        if (event.target.dataset.pageBuilderTarget === 'blockStyleInput') {
            block.size = event.target.value;

            // Parser la nouvelle valeur
            const parsed = this.parseSize(block.size);

            const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-title-preview');
            contentElements.forEach(el => {
                // Supprimer les anciennes classes de style et styles inline
                el.className = 'pb-title-preview pb-block-content-preview';
                el.style.fontSize = '';

                // Ajouter les nouvelles classes si elles existent
                if (parsed.classNames && parsed.classNames.length > 0) {
                    parsed.classNames.forEach(className => {
                        el.classList.add(className);
                    });
                }

                // Appliquer la taille de police si elle existe
                if (parsed.fontSize) {
                    el.style.fontSize = parsed.fontSize;
                }

                // Mettre à jour l'alignement
                const container = el.parentElement;
                if (container) {
                    container.className = 'text-' + (block.horizontalAlignment || 'left');
                }
            });
        }
    },
};
