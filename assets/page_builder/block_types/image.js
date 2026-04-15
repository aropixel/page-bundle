import { initImageManager } from '/bundles/aropixeladmin/js/module/image-manager/launcher.js';
import { IM_Library } from '/bundles/aropixeladmin/js/module/image-manager/library.js';

export const imageBlockType = {
    type: 'image',

    create: (generateId) => ({
        id: generateId(),
        type: 'image',
        src: '',
        hoverSrc: '', // Nouvelle image au hover
        alt: '',
        imageId: null, // ID de l'image dans aropixel_image
        hoverImageId: null, // ID de l'image hover
        width: 100, // Largeur par défaut en %
        maxWidth: 200,
        useOriginalSize: true,
    }),

    renderPreview: (block, ctx, rowId) => {
        const label = document.createElement('div');
        label.classList.add('pb-block-label');
        label.textContent = 'Image';

        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview');
        content.dataset.imageBlockId = block.id; // Identifiant pour cibler ce content

        const img = document.createElement('div');
        img.className = 'pb-image-preview';
        img.style.cursor = 'pointer';
        img.dataset.blockId = block.id;

        if (!block.useOriginalSize) {
            const row = ctx.sectionsManager.findRowById(rowId);
            //const maxWidth = row ? row.imgWidth : block.maxWidth;
            content.style.width = `${block.width}%`;
            //img.style.maxWidth = `${maxWidth}px`;
            img.style.maxHeight = `${block.maxHeight}px`;
            img.style.margin = 'auto';
        } else {
            content.style.width = 'auto';
        }

        if (block.src) {
            // Afficher l'image si elle existe
            img.classList.add('active');
            const imgElement = document.createElement('img');
            imgElement.src = block.src;
            imgElement.style.height = 'auto';

            // Appliquer la largeur originale si nécessaire
            if (block.useOriginalSize) {
                imgElement.style.width = 'auto';
                imgElement.style.maxWidth = '100%';
            }

            img.innerHTML = '';
            img.appendChild(imgElement);
        } else {
            img.innerHTML = '<i class="fas fa-images"></i>';
            img.style.padding = '20px';
            img.style.textAlign = 'center';
            img.style.border = '2px dashed #ccc';
        }

        content.appendChild(img);

        const container = document.createElement('div');
        container.appendChild(content);

        return { container };
    },

    renderInspector: (block, ctx) => {
        const inspector = ctx.inspectorPanelBlockTarget;
        const contentContainer = inspector.querySelector('.pb-inspector-block-content');

        if (contentContainer) {

            // Récupérer le contenu de #section-image
            const sectionImage = document.getElementById('section-image');

            if (sectionImage) {
                // Cloner le contenu pour le déplacer dans l'inspector
                const imageContent = sectionImage.cloneNode(true);
                imageContent.classList.remove('d-none');
                imageContent.removeAttribute('id'); // Éviter les doublons d'ID

                contentContainer.innerHTML = `
                    <div class="mb-3">
                        <label class="form-label form-label-sm">Texte alternatif</label>
                        <input type="text"
                               class="form-control form-control-sm"
                               placeholder="Description de l'image"
                               value="${block.alt || ''}"
                               data-page-builder-target="blockAltInput"
                               data-action="input->page-builder#updateBlockContent" />
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch d-flex justify-content-between">
                            <label class="form-label mb-0 form-label-sm" for="use-original-size">Utiliser la taille originale</label>
                            <input class="form-check-input mt-1"
                                   type="checkbox"
                                   id="use-original-size"
                                   ${block.useOriginalSize ? 'checked' : ''}
                                   data-action="change->page-builder#updateBlockContent">
                        </div>
                    </div>
                    <div class="mb-2" id="size-range-container" ${block.useOriginalSize ? 'style="display:none"' : ''}>
                        <label class="form-label form-label-sm" for="image-size">Taille de l'image (%)</label>
                        <div class="d-flex align-items-center gap-2">
                          <input type="range" min="1" max="100" value="${block.width || 100}"
                                 class="form-range" id="image-size" style="width:100%"
                                 data-action="input->page-builder#updateBlockContent">
                          <span id="size-value" class="text-muted small">${block.width || 100}%</span>
                        </div>
                    </div>
                `;

                // Ajouter le widget image
                contentContainer.appendChild(imageContent);

                // Si le bloc a déjà une image, la pré-remplir dans le widget
                if (block.src) {
                    const preview = imageContent.querySelector('.im-manager .preview');
                    if (preview) {
                        // Garder l'input existant et juste mettre à jour l'affichage
                        const existingInput = preview.querySelector('input[type="hidden"]');
                        preview.innerHTML = `<img src="${block.src}" alt="${block.alt || ''}" />`;
                        if (existingInput && block.imageId) {
                            existingInput.value = block.imageId;
                            preview.appendChild(existingInput);
                        } else if (block.imageId) {
                            // Recréer l'input s'il n'existe pas
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.value = block.imageId;
                            preview.appendChild(input);
                        }
                        // Retirer l'attribut data-new pour indiquer qu'une image est présente
                        preview.removeAttribute('data-new');
                    }
                }

                initializeImageManager(block, ctx);
            }
        }

        ctx.blockTitleTarget.textContent = 'Bloc image';
    },

    handleInspectorInput: (block, event) => {
        if (event.target.dataset.pageBuilderTarget === 'blockAltInput') {
            block.alt = event.target.value;
        }
        if (event.target.id === 'image-size') {
            block.width = parseInt(event.target.value, 10) || 100;

            // Mettre à jour l'affichage de la valeur
            const sizeValue = document.getElementById('size-value');
            if (sizeValue) {
                sizeValue.textContent = `${block.width}%`;
            }
        }
        if (event.target.id === 'use-original-size') {
            block.useOriginalSize = event.target.checked;

            // Afficher/masquer le contrôle de taille
            const sizeRangeContainer = document.getElementById('size-range-container');
            if (sizeRangeContainer) {
                sizeRangeContainer.style.display = event.target.checked ? 'none' : 'block';
            }
        }
    },
};

let imLibrary = null;
function initializeImageManager(block, ctx, imageType = 'main', containerElement = null) {
    const targetContainer = containerElement || document;

    targetContainer.querySelectorAll('.im-manager').forEach((el) => {
        if (el.dataset.imManagerLoaded !== true) {
            initImageManager(el);
            el.dataset.imManagerLoaded = true;
        }
    });

    if (!window.imLibrary) {
        imLibrary = new IM_Library();
        window.imLibrary = imLibrary;
    } else {
        imLibrary = window.imLibrary;
        imLibrary.modal.init();
    }

    const targetNode = containerElement
        ? containerElement.querySelector('.im-manager .preview')
        : document.querySelector('.im-manager .preview');

    if (!targetNode) return;

    const config = { attributes: true, childList: true, subtree: true };

    const callback = (mutationList, observer) => {
        for (const mutation of mutationList) {
            if (mutation.type === "childList") {
                const imgElement = mutation.target.querySelector('img');
                if (imgElement) {
                    const imgSrc = imgElement.getAttribute('src');
                    const previewSrc = imgSrc.replace('/media/cache/admin_thumbnail/', '/media/cache/resolve/admin_preview/');

                    // Récupérer l'input depuis le bon conteneur
                    const inputId = containerElement
                        ? containerElement.querySelector('.im-manager input[type="hidden"]')
                        : document.querySelector('.im-manager input[type="hidden"]');

                    block = ctx.sectionsManager.selectedBlock;
                    // récupérer imageType + containerElement

                    if (imageType === 'hover') {
                        block.hoverSrc = previewSrc;
                        if (inputId) {
                            block.hoverImageId = inputId.value;
                        }
                    } else {
                        block.src = previewSrc;
                        if (inputId) {
                            block.imageId = inputId.value;
                        }

                        // Mettre à jour uniquement la preview principale dans le canvas
                        const previewInStructure = document.querySelector(`.pb-image-preview[data-block-id="${block.id}"]`);
                        if (previewInStructure) {
                            previewInStructure.classList.add('active');
                            previewInStructure.innerHTML = `<img src="${previewSrc}" style="height: auto;" alt="${block.alt || ''}" />`;
                        }
                    }

                    if (ctx && ctx.render) {
                        ctx.render();
                    }
                }
            }
        }
    };

    // Create an observer instance linked to the callback function
    const observer = new MutationObserver(callback);

    // Start observing the target node for configured mutations
    observer.observe(targetNode, config);
}
