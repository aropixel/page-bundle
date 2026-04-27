export const iframeBlockType = {
    type: 'iframe',

    create(generateId) {
        return {
            id: generateId(),
            type: 'iframe',
            content: '',
            renderingMode: 'normal',
        };
    },

    renderPreview(block, ctx) {
        const container = document.createElement('div');
        container.classList.add('pb-block-iframe-preview');

        if (block.content) {
            let content = block.content;

            if (block.renderingMode === 'ratio169') {
                container.innerHTML = `<div class="embed-responsive-item position-relative">${content}</div>`;
            } else {
                // Mode normal : on utilise une classe Bootstrap (mw-100) pour la preview
                container.innerHTML = `<div class="mw-100">${content}</div>`;
                const iframe = container.querySelector('iframe');
                if (iframe) {
                    iframe.style.maxWidth = '100%';
                }
            }

            // Empêcher les clics sur l'iframe dans l'éditeur pour ne pas déclencher la navigation ou autre
            // tout en permettant la sélection du bloc par le PageBuilder
            const overlay = document.createElement('div');
            overlay.style.position = 'absolute';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.zIndex = '10';
            overlay.style.cursor = 'pointer';

            container.style.position = 'relative';
            container.appendChild(overlay);

        } else {
            container.innerHTML = `
                <div class="p-3 border rounded bg-light text-center text-muted">
                    <i class="fas fa-video mb-2"></i>
                    <div>Aucun contenu d'iframe</div>
                </div>
            `;
        }

        return { container };
    },

    renderInspector(block, ctx) {

        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {
            contentContainer.innerHTML = `
                <div class="mb-3">
                    <label class="form-label form-label-sm">Mode de rendu</label>
                    <select class="form-select form-select-sm" data-page-builder-target="blockRenderingModeInput" data-action="input->page-builder#updateBlockContent">
                        <option value="normal" ${block.renderingMode === 'normal' ? 'selected' : ''}>Normal (max-width: 100%)</option>
                        <option value="ratio169" ${block.renderingMode === 'ratio169' ? 'selected' : ''}>Ratio 16:9 (video-responsive)</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm">Contenu (Iframe, HTML...)</label>
                    <textarea
                        class="form-control form-control-sm"
                        rows="8"
                        placeholder="Insérez votre code iframe ici..."
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>
            `;
        }

        ctx.blockTitleTarget.textContent = 'Bloc iframe';
        ctx.blockContentInputTarget.value = block.content || '';
    },

    handleInspectorInput(block, event) {

        if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.content = event.target.value;
        }
        if (event.target.dataset.pageBuilderTarget === 'blockRenderingModeInput') {
            block.renderingMode = event.target.value;
        }
    },
};
