import { t } from '../i18n.js';

export const textBlockType = {
    type: 'text',

    create(generateId) {
        return {
            id: generateId(),
            type: 'text',
            content: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ullamcorper facilisis nisl nec iaculis. Donec tempor feugiat tortor, volutpat dictum justo fringilla tincidunt. Aenean rutrum mattis nunc nec ultricies. Proin mattis ex et nibh rutrum scelerisque. Integer aliquet arcu eget rutrum bibendum.',
            horizontalAlignment: 'left'
        };
    },

    renderPreview(block, ctx) {
        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview');
        content.setAttribute('contenteditable', 'true');
        content.textContent = block.content || '';
        content.innerHTML = block.content || 'Votre texte ici...';
        const horizontalAlignmentClass = 'text-' + block.horizontalAlignment;
        content.classList.add(horizontalAlignmentClass);

        // Variable pour gérer le debounce de sauvegarde
        let saveTimeout = null;
        let isEditing = false;

        // Empêcher le collage avec formatage
        content.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        // Variable pour sauvegarder la sélection
        let savedSelection = null;

        // Marquer qu'on est en train d'éditer
        content.addEventListener('focus', () => {
            isEditing = true;
            content.dataset.editing = 'true';

            // Afficher la toolbar quand on a le focus
            if (content.previousElementSibling && content.previousElementSibling.classList.contains('pb-text-toolbar')) {
                content.previousElementSibling.style.display = 'flex';
            }

            ctx.sectionsManager.resetSelection()

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

            // Masquer la toolbar quand on perd le focus
            if (content.previousElementSibling && content.previousElementSibling.classList.contains('pb-text-toolbar')) {
                content.previousElementSibling.style.display = 'none';
            }

            // Sauvegarder immédiatement au blur
            if (saveTimeout) {
                clearTimeout(saveTimeout);
            }
            // Mise à jour silencieuse sans re-render
            block.content = content.innerHTML;
        });

        // Sauvegarder la sélection quand l'utilisateur sélectionne du texte
        content.addEventListener('mouseup', () => {
            if (isEditing) {
                savedSelection = ctx.sectionsManager.updateTextBlockContent(block.id, content);
            }
        });

        content.addEventListener('keyup', () => {
            if (isEditing) {
                savedSelection = ctx.sectionsManager.updateTextBlockContent(block.id, content);
            }
        });

        // Sauvegarder le contenu avec un debounce - SANS APPELER renderCanvas
        content.addEventListener('input', () => {
            // Annuler le timeout précédent
            if (saveTimeout) {
                clearTimeout(saveTimeout);
            }

            // Sauvegarder après 1 seconde d'inactivité
            saveTimeout = setTimeout(() => {
                // Mise à jour silencieuse du contenu SANS re-render
                block.content = content.innerHTML;
                ctx.blockContentInputTarget.value = block.content;
            }, 1000);
        });

        // Empêcher la propagation des clics pour ne pas sélectionner le bloc
        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Toolbar d'édition
        const toolbar = document.createElement('div');
        toolbar.classList.add('pb-text-toolbar');
        toolbar.contentEditable = false;
        toolbar.style.display = 'none';

        const tools = [
            { cmd: 'bold', icon: 'B', title: t('page.builder.text.toolbar.bold') },
            { cmd: 'italic', icon: 'I', title: t('page.builder.text.toolbar.italic') },
            { cmd: 'underline', icon: 'U', title: t('page.builder.text.toolbar.underline') },
            { cmd: 'createLink', icon: '🔗', title: t('page.builder.text.toolbar.link'), prompt: true },
            { cmd: 'insertUnorderedList', icon: '•', title: t('page.builder.text.toolbar.list_unordered') },
            { cmd: 'insertOrderedList', icon: '1.', title: t('page.builder.text.toolbar.list_ordered') },
            { cmd: 'justifyLeft', icon: '⬅', title: t('page.builder.text.toolbar.align_left') },
            { cmd: 'justifyCenter', icon: '⬌', title: t('page.builder.text.toolbar.align_center') },
            { cmd: 'justifyRight', icon: '➡', title: t('page.builder.text.toolbar.align_right') },
        ];

        tools.forEach(({ cmd, icon, title, value, prompt }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.classList.add('pb-text-toolbar-btn');
            btn.innerHTML = icon;
            btn.title = title;

            btn.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Empêcher la perte de focus
                e.stopPropagation(); // Empêcher la sélection du bloc
            });

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Restaurer la sélection avant d'exécuter la commande
                // if (savedSelection) {
                //     restoreSelection(content, savedSelection);
                // }

                content.focus();

                if (prompt && cmd === 'createLink') {
                    const url = window.prompt(t('page.builder.text.toolbar.link_prompt'), 'https://');
                    if (url && url !== 'https://') {
                        document.execCommand(cmd, false, url);
                    }
                } else if (value) {
                    document.execCommand(cmd, false, value);
                } else {
                    document.execCommand(cmd, false, null);
                }

                // Sauvegarder immédiatement après une action de toolbar - SANS re-render
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }
                block.content = content.innerHTML;

                // Sauvegarder la nouvelle sélection
                savedSelection = ctx.sectionsManager.updateTextBlockContent(block.id, content);
            });

            toolbar.appendChild(btn);
        });

        const container = document.createElement('div');
        container.classList.add('pb-text-block-container');
        container.appendChild(toolbar);
        container.appendChild(content);

        return { container, contentElement: content };
    },

    renderInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = 'Bloc texte';

        // Créer un élément temporaire pour extraire le texte sans HTML
        const temp = document.createElement('div');
        temp.innerHTML = block.content || '';

        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2">
                    <label class="form-label form-label-sm d-block mb-1">Alignement horizontal</label>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="left"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
                                title="Aligné à gauche">
                            <i class="fas fa-align-left"></i>
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="center"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
                                title="Centré">
                            <i class="fas fa-align-center"></i>
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="right"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
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
                        placeholder="${temp.textContent || temp.innerText || ''}"
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>
            `;
        }

        ctx.blockContentInputTarget.value = temp.textContent || temp.innerText || '';

        // Ajouter un message d'info
        const existingInfo = ctx.blockContentInputTarget.parentElement.querySelector('.pb-inspector-info');
        if (existingInfo) {
            existingInfo.remove();
        }

        const info = document.createElement('small');
        info.classList.add('pb-inspector-info', 'text-muted', 'd-block', 'mt-1');
        info.textContent = 'ℹ️ La mise en forme se fait directement dans le canvas';
        ctx.blockContentInputTarget.parentElement.appendChild(info);
    },

    handleInspectorInput(block, event) {
        // Convertir le texte brut en HTML simple
        const text = event.target.value;
        // Remplacer les retours à la ligne par des <br>
        block.content = text.replace(/\n/g, '<br>');

        // Mettre à jour le contenu éditable dans le canvas
        const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-block-content-preview');
        contentElements.forEach(el => {
            if (el.innerHTML !== block.content) {
                el.innerHTML = block.content;
            }
        });
    },
};
