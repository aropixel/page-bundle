import { t } from '../i18n.js';

export const dividerBlockType = {
    type: 'divider',

    create(generateId) {
        return {
            id: generateId(),
            type: 'divider',
            marginTop: 20,
            marginBottom: 20,
        };
    },

    renderPreview(block) {
        const content = document.createElement('div');
        content.classList.add('pb-block-divider');
        content.style.marginTop = `${block.marginTop || 20}px`;
        content.style.marginBottom = `${block.marginBottom || 20}px`;
        content.style.border = '2px dashed #d1d5db';
        content.style.width = '80%';
        content.style.marginLeft = 'auto';
        content.style.marginRight = 'auto';

        const container = document.createElement('div');
        container.appendChild(content);

        return { container, contentElement: null };
    },

    renderInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = t('page.builder.block_title.divider');

        // Créer les champs pour marginTop et marginBottom
        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2 form-group" data-page-builder-target="blockContentInput">
                    <label class="form-label form-label-sm">${t('page.builder.block.divider.margin_top')}</label>
                    <input type="number" class="form-control form-control-sm" id="divider-margin-top"
                           value="${block.marginTop || 20}" min="0" max="200"
                           data-action="input->page-builder#updateBlockContent">
                </div>
                <div class="mb-3">
                    <label class="form-label">${t('page.builder.block.divider.margin_bottom')}</label>
                    <input type="number" class="form-control" id="divider-margin-bottom"
                           value="${block.marginBottom || 20}" min="0" max="200"
                           data-action="input->page-builder#updateBlockContent">
                </div>
            `;
        }
    },

    handleInspectorInput(block, event) {
        const target = event.target;

        if (target.id === 'divider-margin-top') {
            block.marginTop = parseInt(target.value, 10) || 0;
        } else if (target.id === 'divider-margin-bottom') {
            block.marginBottom = parseInt(target.value, 10) || 0;
        }
    },
};
