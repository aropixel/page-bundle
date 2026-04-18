import { initImageManager } from '/bundles/aropixeladmin/js/module/image-manager/launcher.js';
import { IM_Library } from '/bundles/aropixeladmin/js/module/image-manager/library.js';
import { t } from '../i18n.js';

export const sliderBlockType = {
    type: 'slider',

    create: (generateId) => ({
        id: generateId(),
        type: 'slider',
        items: [], // Liste d'objets { id, src, imageId, alt }
        selectedIndex: null
    }),

    renderPreview: (block, ctx) => {
        const container = document.createElement('div');
        container.classList.add('pb-block-slider-preview');

        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview', 'd-flex', 'flex-wrap', 'gap-2', 'p-2');

        if (block.items && block.items.length > 0) {
            block.items.forEach((item, index) => {
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'pb-slider-item-preview position-relative';
                imgWrapper.style.width = '100px';
                imgWrapper.style.height = '100px';
                if (block.selectedIndex === index) {
                    imgWrapper.style.border = '2px solid #0CABA8';
                    imgWrapper.style.boxShadow = '0 0 5px rgba(0,123,255,0.5)';
                } else {
                    imgWrapper.style.border = '1px solid #ddd';
                }
                //imgWrapper.style.cursor = 'pointer';
                imgWrapper.style.cursor = 'move';
                imgWrapper.setAttribute('draggable', true);
                imgWrapper.dataset.index = index;

                // Drag and Drop Events
                imgWrapper.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', index);
                    imgWrapper.style.opacity = '0.4';
                });

                imgWrapper.addEventListener('dragend', (e) => {
                    imgWrapper.style.opacity = '1';
                });

                imgWrapper.addEventListener('dragover', (e) => {
                    e.preventDefault();
                });

                imgWrapper.addEventListener('drop', (e) => {
                    e.preventDefault();
                    const fromIndex = parseInt(e.dataTransfer.getData('text/plain'), 10);
                    const toIndex = index;

                    if (fromIndex !== toIndex) {
                        const movedItem = block.items.splice(fromIndex, 1)[0];
                        block.items.splice(toIndex, 0, movedItem);

                        // Ajuster l'index sélectionné si nécessaire
                        if (block.selectedIndex === fromIndex) {
                            block.selectedIndex = toIndex;
                        } else if (fromIndex < block.selectedIndex && toIndex >= block.selectedIndex) {
                            block.selectedIndex--;
                        } else if (fromIndex > block.selectedIndex && toIndex <= block.selectedIndex) {
                            block.selectedIndex++;
                        }

                        ctx.renderCanvas();
                    }
                });

                if (item.src) {
                    const img = document.createElement('img');
                    img.src = item.src;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    imgWrapper.appendChild(img);
                } else {
                    imgWrapper.innerHTML = `<div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="fas fa-image" style="font-size: 30px;"></i></div>`;
                }

                // Bouton de suppression sur l'image dans le canvas
                const delBtn = document.createElement('button');
                delBtn.innerHTML = '×';
                delBtn.className = 'btn btn-danger btn-xs position-absolute';
                delBtn.style.top = '2px';
                delBtn.style.right = '2px';
                delBtn.style.zIndex = '10';
                delBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (confirm(t('page.builder.block.slider.delete_confirm'))) {
                        block.items.splice(index, 1);
                        block.selectedIndex = null;
                        ctx.renderCanvas();
                    }
                });
                imgWrapper.appendChild(delBtn);

                // Clic pour sélectionner dans l'inspecteur
                imgWrapper.addEventListener('click', () => {
                    block.selectedIndex = index;
                    ctx.onBlockClick(ctx.sectionsManager.selectedSectionId, ctx.sectionsManager.selectedRowId, ctx.sectionsManager.selectedColumnId, block.id);
                });

                content.appendChild(imgWrapper);
            });
        } else {
            content.innerHTML = `<div class="p-3 text-muted small w-100 text-center">${t('page.builder.block.slider.empty')}</div>`;
        }

        const addBtn = document.createElement('button');
        addBtn.style.width = '100px';
        addBtn.style.height = '100px';
        addBtn.style.border = '1px solid #ddd';
        addBtn.style.cursor = 'pointer';
        addBtn.innerHTML = '<i class="fas fa-plus me-2"></i>';
        addBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            block.items.push({ id: Date.now(), src: '', imageId: null, alt: '' });
            block.selectedIndex = block.items.length - 1;

            const blockEl = addBtn.closest('[data-block-id]');
            if (blockEl) {
                ctx.sectionsManager.selectBlock(
                    blockEl.dataset.sectionId,
                    blockEl.dataset.rowId,
                    blockEl.dataset.columnId,
                    blockEl.dataset.blockId
                );
                ctx.tabs.activate('inspector');
                ctx.showBlockInspector();
            }
            ctx.renderCanvas();
        });
        content.appendChild(addBtn);

        container.appendChild(content);
        return { container };
    },

    renderInspector: (block, ctx) => {
        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');
        if (!contentContainer) return;

        contentContainer.innerHTML = '';

        // Formulaire d'édition de l'élément sélectionné
        if (block.selectedIndex !== null && block.items[block.selectedIndex]) {
            const item = block.items[block.selectedIndex];
            const editCard = document.createElement('div');
            editCard.className = 'section-slider-item';
            editCard.innerHTML = `
                <div class="mb-3">
                    <label class="form-label form-label-sm">Texte alternatif</label>
                    <input type="text" class="form-control form-control-sm" value="${item.alt || ''}"
                           data-op="update-alt" data-index="${block.selectedIndex}" data-action="input->page-builder#updateBlockContent">
                </div>
                <div id="slider-item-image-manager"></div>
            `;
            contentContainer.appendChild(editCard);

            const sectionImage = document.getElementById('section-image');
            if (sectionImage) {
                const imageContent = sectionImage.cloneNode(true);
                imageContent.classList.remove('d-none');
                imageContent.removeAttribute('id');
                editCard.querySelector('#slider-item-image-manager').appendChild(imageContent);

                if (item.src) {
                    const preview = imageContent.querySelector('.im-manager .preview');
                    preview.innerHTML = `<img src="${item.src}" />`;
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.value = item.imageId || '';
                    preview.appendChild(input);
                    preview.removeAttribute('data-new');
                }
                initializeItemManager(block, ctx, editCard, block.selectedIndex);
            }
        }

        ctx.blockTitleTarget.textContent = 'Slider';
    },

    handleInspectorInput: (block, event) => {
        const op = event.target.dataset.op;
        const index = parseInt(event.target.dataset.index, 10);

        if (op === 'add-item') {
            block.items.push({ id: Date.now(), src: '', imageId: null, alt: '' });
            block.selectedIndex = block.items.length - 1;
        } else if (op === 'select-item') {
            block.selectedIndex = index;
        } else if (op === 'update-alt') {
            block.items[index].alt = event.target.value;
        }
    },
};

function initializeItemManager(block, ctx, container, index) {
    const managerEl = container.querySelector('.im-manager');
    if (!managerEl) return;

    initImageManager(managerEl);
    if (!window.imLibrary) {
        window.imLibrary = new IM_Library();
    } else {
        window.imLibrary.modal.init();
    }

    const targetNode = managerEl.querySelector('.preview');
    const observer = new MutationObserver(() => {
        const img = targetNode.querySelector('img');
        if (img) {
            const src = img.getAttribute('src').replace('/media/cache/admin_thumbnail/', '/media/cache/resolve/admin_preview/');
            block.items[block.selectedIndex].src = src;
            const input = managerEl.querySelector('input[type="hidden"]');
            if (input) block.items[block.selectedIndex].imageId = input.value;
            ctx.renderCanvas();
        }
    });
    observer.observe(targetNode, { childList: true });
}
