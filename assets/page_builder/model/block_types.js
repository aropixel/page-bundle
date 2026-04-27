import { textBlockType } from '../block_types/text.js';
import { titleBlockType } from '../block_types/title.js';
import { btnBlockType } from '../block_types/button.js';
import { spacerBlockType } from '../block_types/spacer.js';
import { dividerBlockType } from '../block_types/divider.js';
import { imageBlockType } from '../block_types/image.js';
import { sliderBlockType } from '../block_types/slider.js';
import { blogBlockType } from '../block_types/blog.js';
import { nestedRowBlockType } from '../block_types/nested-row.js';
import { ctaBlockType } from '../block_types/cta.js';
import { bannerBlockType } from '../block_types/banner.js';
import { iframeBlockType } from '../block_types/iframe.js';

export class BlockTypesRegistry {
    constructor() {
        this.types = {};

        // on enregistre chaque type intégré
        [titleBlockType, textBlockType, btnBlockType, spacerBlockType, dividerBlockType, imageBlockType, sliderBlockType, blogBlockType, nestedRowBlockType, ctaBlockType, bannerBlockType, iframeBlockType].forEach((def) => {
            this.types[def.type] = def;
        });

        // Enregistrement des blocs personnalisés fournis par l'application
        const customBlocks = window.__pageBuilderCustomBlocks || [];
        customBlocks.forEach((def) => {
            if (def && def.type) {
                this.types[def.type] = def;
            }
        });
    }

    generateId() {
        return `block-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }

    createBlock(type) {
        const def = this.types[type];
        if (!def || !def.create) {
            throw new Error(`Unknown block type: ${type}`);
        }
        return def.create(this.generateId.bind(this));
    }

    renderPreview(block, ctx, rowId) {
        const def = this.types[block.type];
        if (!def || !def.renderPreview) {
            return this.#renderUnknownPreview(block);
        }
        return def.renderPreview(block, ctx, rowId);
    }

    renderInspector(block, ctx) {
        const def = this.types[block.type];
        if (!def || !def.renderInspector) {
            this.#renderDefaultInspector(block, ctx);
            return;
        }
        def.renderInspector(block, ctx);

        const currentAlignment = block.horizontalAlignment;
        // Mettre à jour les boutons d'alignement
        const alignmentButtons = document.querySelectorAll('[data-page-builder-target="blockAlignmentButton"]');
        alignmentButtons.forEach(button => {
            if (button.dataset.alignment === currentAlignment) {
                button.classList.add('active'); // ou 'pb-button--active' selon votre CSS
            } else {
                button.classList.remove('active');
            }
        });
    }

    handleInspectorInput(block, event) {
        const def = this.types[block.type];
        if (!def || !def.handleInspectorInput) {
            this.#defaultInspectorInput(block, event);
            return;
        }
        def.handleInspectorInput(block, event);
    }

    #renderUnknownPreview(block) {
        const label = document.createElement('div');
        label.classList.add('pb-block-label');
        label.textContent = `Bloc (${block.type || 'inconnu'})`;

        const content = document.createElement('div');
        content.classList.add('pb-block-content-preview');
        content.textContent = '[Type non géré]';

        const container = document.createElement('div');
        container.appendChild(content);

        return { container };
    }

    #renderDefaultInspector(block, ctx) {
        ctx.blockTitleTarget.textContent = `Bloc ${block.type || 'inconnu'}`;
        ctx.blockContentInputTarget.value = '';
    }

    #defaultInspectorInput(block, event) {
        // no-op par défaut
    }
}
