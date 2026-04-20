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

        let saveTimeout = null;
        let isEditing = false;
        let quill = null;

        const initQuill = () => {
            if (quill) return;

            content.addEventListener('paste', () => {
                setPasting(true);
                setTimeout(() => { setPasting(false); }, 0);
            }, true);

            quill = new Quill(content, getQuillConfig('bubble'));

            const mailtoBtn = content.querySelector('.ql-mailto');
            if (mailtoBtn) mailtoBtn.title = 'Ajouter un lien e-mail (mailto)';

            quill.on('text-change', () => {
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }

                saveTimeout = setTimeout(() => {
                    const html = cleanHTML(quill.root.innerHTML);
                    block.content = html;
                    ctx.sectionsManager.updateTextBlockContent(block.id, html);
                }, 1000);
            });

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

                ctx.sectionsManager.resetSelection();

                const blockEl = document.querySelector('.pb-block[data-block-id="' + block.id + '"]');
                if (blockEl) {
                    blockEl.classList.add('pb-block--selected');

                    const sectionId = blockEl.dataset.sectionId;
                    const rowId = blockEl.dataset.rowId;
                    const columnId = blockEl.dataset.columnId;

                    ctx.sectionsManager.selectBlock(sectionId, rowId, columnId, block.id);
                    ctx.activateTab('nav-structure');
                    ctx.showBlockInspector();
                }
            });

            editor.addEventListener('blur', () => {
                isEditing = false;
                delete content.dataset.editing;

                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }
                const html = cleanHTML(quill.root.innerHTML);
                block.content = html;
                ctx.sectionsManager.updateTextBlockContent(block.id, html);
            });

            editor.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        };

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
