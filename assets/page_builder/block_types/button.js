import { t } from '../i18n.js';
import { getStyleOptions, renderStyleSelect } from '../style_options.js';
import { icon } from '../../utils/icons.js';

export const btnBlockType = {
    type: 'button',

    create(generateId) {
        return {
            id: generateId(),
            type: 'button',
            label: t('page.builder.block.button.default_label'),
            class: '',
            url: '#',
            pagePath: null,
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
                    <label class="form-label pb-label d-block mb-1">${t('page.builder.inspector.column.horizontal_alignment')}</label>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="left"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
                                title="Aligné à gauche">
                            ${icon('align-left')}
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="center"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
                                title="Centré">
                            ${icon('align-center')}
                        </button>
                        <button type="button"
                                class="pb-button pb-button--ghost flex-fill"
                                data-alignment="right"
                                data-page-builder-target="blockAlignmentButton"
                                data-action="click->page-builder#updateBlockHorizontalAlignment"
                                title="Aligné à droite">
                            ${icon('align-right')}
                        </button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label pb-label">${t('page.builder.block.button.label_field')}</label>
                    <textarea
                        class="form-control form-control-sm"
                        rows="1"
                        placeholder="${t('page.builder.inspector.block.content_placeholder')}"
                        data-page-builder-target="blockContentInput"
                        data-action="input->page-builder#updateBlockContent"
                    ></textarea>
                </div>

                <div class="mb-2">
                    <label class="form-label pb-label d-block mb-1" for="button-link-type">${t('page.builder.inspector.column.link_type')}</label>
                    <select class="form-select form-select-sm" id="button-link-type"
                            data-page-builder-target="blockLinkTypeSelect"
                            data-action="change->page-builder#updateBlockLinkType">
                        <option value="url">${t('page.builder.inspector.column.link_url')}</option>
                        <option value="page">${t('page.builder.block.button.link_page')}</option>
                    </select>
                </div>
                <div class="mb-2" data-page-builder-target="blockUrlInputContainer">
                    <label class="form-label pb-label d-block mb-1" for="button-url">${t('page.builder.inspector.column.link_url')}</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           id="button-url"
                           placeholder="https://..."
                           data-page-builder-target="blockUrlInput"
                           data-action="input->page-builder#updateBlockUrl">
                </div>
                <div class="mb-2 d-none" data-page-builder-target="blockPagePathSelectContainer">
                    <label class="form-label pb-label d-block mb-1" for="button-page-path">${t('page.builder.block.button.link_page_label')}</label>
                    <select class="form-select form-select-sm" id="button-page-path"
                            data-page-builder-target="blockPagePathSelect"
                            data-action="change->page-builder#updateBlockPagePath"
                    >
                        <option value="">${t('form.choose')}</option>
                    </select>
                </div>
${renderStyleSelect({
                    options: getStyleOptions(ctx, 'button_colors'),
                    selected: block.class || '',
                    id: 'btn-color-select',
                    label: t('page.builder.block.button.color'),
                    target: 'blockColorInput',
                })}
            `;
        }

        ctx.blockTitleTarget.textContent = t('page.builder.block_title.button');
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

        if (ctx.hasBlockPagePathSelectTarget) {
            const pageSelect = ctx.blockPagePathSelectTarget;
            const pagePathJsonListUrl = JSON.parse(document.getElementById('page-json-list-url').textContent);

            if (pageSelect.options.length <= 1) {
                fetch(pagePathJsonListUrl)
                    .then(r => r.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.slug;
                            option.textContent = item.title;
                            if (block.pagePath === item.slug) {
                                option.selected = true;
                            }
                            pageSelect.appendChild(option);
                        });
                    });
            } else {
                Array.from(pageSelect.options).forEach(option => {
                    option.selected = option.value === (block.pagePath || '');
                });
            }
        }

        // Afficher le bon container
        if (ctx.hasBlockUrlInputContainerTarget) {
            ctx.blockUrlInputContainerTarget.classList.toggle('d-none', linkType !== 'url');
        }
        if (ctx.hasBlockPagePathSelectContainerTarget) {
            ctx.blockPagePathSelectContainerTarget.classList.toggle('d-none', linkType !== 'page');
        }
    },

    handleInspectorInput(block, event) {
        if (event.target.dataset.pageBuilderTarget === 'blockColorInput') {
            const previousClass = block.class;
            block.class = event.target.value;

            const contentElements = document.querySelectorAll('.pb-block[data-block-id="' + block.id + '"] .pb-btn-preview');
            contentElements.forEach(el => {
                // Remplacer, pas empiler : sans cela le bouton cumule toutes les couleurs essayées.
                if (previousClass) {
                    el.classList.remove(previousClass);
                }
                if (block.class) {
                    el.classList.add(block.class);
                }
            });
        } else if (event.target.dataset.pageBuilderTarget === 'blockContentInput') {
            block.label = event.target.value;
        }
    },
};
