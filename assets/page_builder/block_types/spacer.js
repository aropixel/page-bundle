export const spacerBlockType = {
    type: 'spacer',

    create(generateId) {
        return {
            id: generateId(),
            type: 'spacer',
            height: 20,
        };
    },

    renderPreview(block) {
        const content = document.createElement('div');
        content.classList.add('pb-block-spacer');
        content.style.height = `${block.height || 20}px`;

        const container = document.createElement('div');
        container.appendChild(content);

        return { container, contentElement: null };
    },

    renderInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = 'Bloc espacement';

        // Créer le champ height
        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-2 form-group" data-page-builder-target="blockContentInput">
                    <label class="form-label form-label-sm">Hauteur (px)</label>
                    <input type="number" class="form-control form-control-sm" id="spacer-height"
                           value="${block.height || 20}" min="0" max="200"
                           data-action="input->page-builder#updateBlockContent">
                </div>
            `;
        }
    },

    handleInspectorInput(block, event) {
        const target = event.target;

        if (target.id === 'spacer-height') {
            block.height = parseInt(target.value, 10) || 0;
        }
    },
};
