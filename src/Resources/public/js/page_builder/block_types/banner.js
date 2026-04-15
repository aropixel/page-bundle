export const bannerBlockType = {
    type: 'banner',

    create(generateId) {
        return {
            id: generateId(),
            type: 'banner',
            content: 'Bandeau défilant',
        };
    },

    initScrollingText(el) {
        const content = el.querySelector('.pb-banner-preview');
        if (!content) return;

        const textWidth = content.scrollWidth + 10;
        const speed = 130;
        const duration = (textWidth / speed) * 1000;

        el.style.setProperty('--tw', `${textWidth}px`);
        el.style.setProperty('--ad', `${duration}ms`);
    },

    renderPreview(block, ctx) {
        const content = document.createElement('div');
        content.classList.add('pb-block--banner');
        content.className = 'pb-banner-preview';
        content.classList.add('pb-block-content-preview');
        content.setAttribute('contenteditable', 'true');
        content.innerHTML = block.content || '';

        document.fonts.ready.then(() => {
            // Attendre un peu plus pour que le DOM soit stable
            setTimeout(() => {
                document.querySelectorAll('.pb-block-banner-container')
                    .forEach(el => this.initScrollingText(el));
            }, 100);
        });

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
            block.content = content.innerHTML;
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
                block.content = e.target.innerHTML;

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
                        bannerEl.innerHTML = block.content;
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
                    <label class="form-label form-label-sm d-flex justify-content-between align-items-center">
                        Contenu du texte
                        <button type="button" class="btn btn-xs btn-outline-secondary pb-wrap-span" title="Sélectionnez le contenu pour changer sa couleur">
                            <span>2ème couleur</span>
                        </button>
                    </label>
                    <textarea
                        class="form-control form-control-sm"
                        rows="4"
                        placeholder="Votre texte…"
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>
            `;

            const wrapBtn = contentContainer.querySelector('.pb-wrap-span');
            if (wrapBtn) {
                wrapBtn.addEventListener('click', () => {
                    const textarea = contentContainer.querySelector('textarea');
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;
                    const selectedText = text.substring(start, end);
                    const before = text.substring(0, start);
                    const after = text.substring(end);

                    if (selectedText.length > 0) {
                        textarea.value = before + '<span>' + selectedText + '</span>' + after;
                        textarea.dispatchEvent(new Event('input', { bubbles: true }));
                        textarea.focus();
                        textarea.setSelectionRange(start, end + 13); // 13 is the length of <span></span>
                    }
                });
            }
        }

        ctx.blockTitleTarget.textContent = 'Bloc bandeau défilant';
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
            if (el.innerHTML !== block.content) {
                el.innerHTML = block.content;
            }
        });
    },
};
