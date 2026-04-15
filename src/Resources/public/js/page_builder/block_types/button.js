export const btnBlockType = {
    type: 'button',

    create(generateId) {
        return {
            id: generateId(),
            type: 'button',
            label: 'En savoir plus',
            class: 'primary-xdark',
            url: '#',
            pagePath: null,
            parentSlug: null,
            linkType: 'url',
            variant: 'primary',
            horizontalAlignment: 'center'
        };
    },

    renderPreview(block) {

        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview');

        const btn = document.createElement('a');
        btn.className = 'pb-btn-preview';
        btn.textContent = block.label || 'Bouton';
        btn.href = block.url || '#';
        btn.horizontalAlignment = block.horizontalAlignment;
        const horizontalAlignmentClass = 'text-' + block.horizontalAlignment;
        content.classList.add(horizontalAlignmentClass);
        btn.classList.add(block.class);
        //a.onclick="location.href='" + a.url || '#' + "'";

        // Empêcher la navigation dans l'éditeur
        btn.addEventListener('click', (e) => {
             e.preventDefault();
        });

        content.appendChild(btn);

        const container = document.createElement('div');
        //container.appendChild(label);
        container.appendChild(content);

        return { container };
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
                    <label class="form-label form-label-sm">Texte du bouton</label>
                    <textarea
                        class="form-control form-control-sm"
                        rows="1"
                        placeholder="Votre texte…"
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>

                <div class="mb-2">
                    <label class="form-label form-label-sm d-block mb-1" for="button-link-type">Type de lien</label>
                    <select class="form-select form-select-sm" id="button-link-type"
                            data-page-builder-target="blockLinkTypeSelect"
                            data-action="change->page-builder#updateBlockLinkType">
                        <option value="url">Lien (URL)</option>
                        <option value="page">Page du site (CMS)</option>
                        <option value="fixed">Page du site (fixe)</option>
                    </select>
                </div>
                <div class="mb-2" data-page-builder-target="blockUrlInputContainer">
                    <label class="form-label form-label-sm d-block mb-1" for="button-url">Lien (URL)</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           id="button-url"
                           placeholder="https://..."
                           data-page-builder-target="blockUrlInput"
                           data-action="input->page-builder#updateBlockUrl">
                </div>
                <div class="mb-2 d-none" data-page-builder-target="blockPagePathSelectContainer">
                    <label class="form-label form-label-sm d-block mb-1" for="button-page-path">Lien vers une page du site (CMS)</label>
                    <select class="form-select form-select-sm" id="button-page-path"
                            data-page-builder-target="blockPagePathSelect"
                            data-action="change->page-builder#updateBlockPagePath"
                    >
                        <option value="">-- Choisir --</option>
                    </select>
                </div>
                <div class="mb-2 d-none" data-page-builder-target="blockFixedPageSelectContainer">
                    <label class="form-label form-label-sm d-block mb-1" for="button-fixed-page">Lien vers une page fixe</label>
                    <select class="form-select form-select-sm" id="button-fixed-page"
                            data-page-builder-target="blockFixedPageSelect"
                            data-action="change->page-builder#updateBlockPagePath"
                    >
                        <option value="">-- Choisir --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-sm">Couleur du bouton</label>
                    <select class="form-select form-select-sm" id="btn-color-select"
                        data-page-builder-target="blockColorInput"
                        data-action="change->page-builder#updateBlockContent">
                        <option value="primary-xdark" ${block.class === 'primary-xdark' ? 'selected' : ''}>Violet foncé</option>
                        <option value="secondary" ${block.class === 'secondary' ? 'selected' : ''}>Jaune</option>
                    </select>
                </div>
            `;
        }

        ctx.blockTitleTarget.textContent = 'Bloc bouton';
        ctx.blockContentInputTarget.value = block.label || '';

        // Initialisation du type de lien et chargement des pages
        const linkType = block.linkType || (block.pagePath ? 'page' : 'url');
        block.linkType = linkType;

        if (ctx.hasBlockLinkTypeSelectTarget) {
            ctx.blockLinkTypeSelectTarget.value = linkType;
        }

        if (ctx.hasBlockUrlInputTarget) {
            ctx.blockUrlInputTarget.value = block.url || '';
        }

        if (ctx.hasBlockPagePathSelectTarget || ctx.hasBlockFixedPageSelectTarget) {
            const pageSelect = ctx.blockPagePathSelectTarget;
            const fixedSelect = ctx.blockFixedPageSelectTarget;
            const pagePathJsonListUrl = JSON.parse(document.getElementById('page-json-list-url').textContent);

            if (pageSelect && pageSelect.options.length <= 1) {
                fetch(pagePathJsonListUrl)
                    .then(r => r.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.slug;
                            option.dataset.parentSlug = item.parentSlug || '';
                            option.textContent = item.title;

                            const isFixed = item.id.toString().startsWith('fixed:');

                            if (isFixed) {
                                if (fixedSelect) {
                                    const fixedOption = option.cloneNode(true);
                                    if (block.pagePath === item.slug) {
                                        fixedOption.selected = true;
                                    }
                                    fixedSelect.appendChild(fixedOption);
                                }
                            } else {
                                if (block.pagePath === item.slug) {
                                    option.selected = true;
                                    if (!block.parentSlug) {
                                        block.parentSlug = item.parentSlug;
                                    }
                                }
                                pageSelect.appendChild(option);
                            }
                        });
                    });
            } else {
                if (pageSelect) {
                    Array.from(pageSelect.options).forEach(option => {
                        option.selected = option.value === block.pagePath;
                    });
                }
                if (fixedSelect) {
                    Array.from(fixedSelect.options).forEach(option => {
                        option.selected = option.value === block.pagePath;
                    });
                }
            }
        }

        // Afficher le bon container
        if (ctx.hasBlockUrlInputContainerTarget) {
            ctx.blockUrlInputContainerTarget.classList.toggle('d-none', linkType !== 'url');
        }
        if (ctx.hasBlockPagePathSelectContainerTarget) {
            ctx.blockPagePathSelectContainerTarget.classList.toggle('d-none', linkType !== 'page');
        }
        if (ctx.hasBlockFixedPageSelectContainerTarget) {
            ctx.blockFixedPageSelectContainerTarget.classList.toggle('d-none', linkType !== 'fixed');
        }
    },

    handleInspectorInput(block, event) {
        if (event.target.dataset.pageBuilderTarget === 'blockColorInput') {
            block.class = event.target.value;

            const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-btn-preview');
            contentElements.forEach(el => {
                el.classList.add(event.target.value);
            });
        } else if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.label = event.target.value;
        }
    },
};
