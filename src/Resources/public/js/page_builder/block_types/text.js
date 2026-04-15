import { getQuillConfig, cleanHTML, setPasting } from '../quill_config.js';

export const textBlockType = {
    type: 'text',

    create(generateId) {
        return {
            id: generateId(),
            type: 'text',
            content: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ullamcorper facilisis nisl nec iaculis. Donec tempor feugiat tortor, volutpat dictum justo fringilla tincidunt. Aenean rutrum mattis nunc nec ultricies. Proin mattis ex et nibh rutrum scelerisque. Integer aliquet arcu eget rutrum bibendum.',
        };
    },

    renderPreview(block, ctx) {
        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview');

        content.innerHTML = block.content || '';

        // Variable pour gérer le debounce de sauvegarde
        let saveTimeout = null;
        let isEditing = false;
        let quill = null;

        const initQuill = () => {
            if (quill) return;

            // Intercepter le paste AVANT Quill (phase de capture)
            content.addEventListener('paste', () => {
                setPasting(true);
                setTimeout(() => { setPasting(false); }, 0);
            }, true); // <-- capture phase

            quill = new Quill(content, getQuillConfig('bubble'));

            // (Optionnel) Afficher le tooltip natif sur le bouton
            const mailtoBtn = content.querySelector('.ql-mailto');
            if (mailtoBtn) mailtoBtn.title = 'Ajouter un lien e-mail (mailto)';

            // Gérer les changements
            quill.on('text-change', () => {
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }

                saveTimeout = setTimeout(() => {
                    // Mise à jour silencieuse du contenu SANS re-render
                    // On récupère le HTML de Quill
                    const html = cleanHTML(quill.root.innerHTML);
                    block.content = html;

                    // Mise à jour via le manager pour s'assurer de la synchro si nécessaire
                    ctx.sectionsManager.updateTextBlockContent(block.id, html);
                }, 1000);
            });

            // Gérer le focus pour l'inspecteur
            const editor = content.querySelector('.ql-editor');
            if (editor) {
                const lastChild = editor.lastElementChild;
                if (lastChild && lastChild.tagName === 'P' && lastChild.innerHTML.replace(/<br\s*\/?>/gi, '').replace(/<\/br>/gi, '').trim() === '') {
                    lastChild.remove();
                }
            }
            editor.addEventListener('focus', () => {
                isEditing = true;
                content.dataset.editing = 'true';

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

            editor.addEventListener('blur', () => {
                isEditing = false;
                delete content.dataset.editing;

                // Sauvegarder immédiatement au blur
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }
                const html = cleanHTML(quill.root.innerHTML);
                block.content = html;

                // Mise à jour via le manager pour s'assurer de la synchro si nécessaire
                ctx.sectionsManager.updateTextBlockContent(block.id, html);
            });

            // Empêcher la propagation des clics pour ne pas sélectionner le bloc par le Page Builder
            // mais laisser Quill gérer ses propres clics
            editor.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        };

        // Initialiser Quill après un court délai pour s'assurer que l'élément est dans le DOM
        setTimeout(initQuill, 0);

        const container = document.createElement('div');
        container.classList.add('pb-text-block-container');
        container.appendChild(content);

        return { container, contentElement: content };
    },

    renderInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = 'Bloc texte';

        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2">
                    <small class="pb-inspector-info text-muted d-block mt-1">
                        ℹ️ La mise en forme se fait directement dans le canvas
                    </small>
                </div>
            `;
        }
    },

    handleInspectorInput(block, event) {
        // Le contenu est géré directement via Quill dans le canvas
    },
};
