import { t } from '../i18n.js';

export const blogBlockType = {
    type: 'blog',
    label: t('page.builder.block.blog.default_label'),

    create(generateId) {
        return {
            id: generateId(),
            type: 'blog',
        };
    },

    renderPreview(block, controller) {
        const container = document.createElement('div');
        container.classList.add('pb-block-content-preview');
        container.classList.add('pb-block-blog', 'w-100');

        // Gestion du clic pour sélectionner le bloc (standardisation)
        container.addEventListener('click', (e) => {
            e.stopPropagation();

            controller.sectionsManager.resetSelection();

            let blockEl = document.querySelector('.pb-block[data-block-id="' + block.id + '"]');
            if (blockEl) {
                blockEl.classList.add('pb-block--selected');

                const sectionId = blockEl.dataset.sectionId;
                const rowId = blockEl.dataset.rowId;
                const columnId = blockEl.dataset.columnId;

                controller.sectionsManager.selectBlock(sectionId, rowId, columnId, block.id);
                controller.activateTab('nav-structure');
                controller.showBlockInspector();
            }
        });

        const content = document.createElement('div');
        content.classList.add('pb-blog-preview');

        content.innerHTML = `
            <div class="alert alert-info mb-0 text-center">
               ${t('page.builder.block.blog.default_label')}
            </div>`;

        container.appendChild(content);

        return { container, contentElement: null };
    },

    renderInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = t('page.builder.block_title.blog');
    }
};
