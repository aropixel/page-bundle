import { Row } from '../model/row.js';
import { Column } from '../model/column.js';

export const nestedRowBlockType = {
    type: 'nested-row',

    create(generateId) {
        const row = new Row();
        const col1 = new Column('1-2');
        const col2 = new Column('1-2');
        row.addColumn(col1);
        row.addColumn(col2);

        return {
            id: generateId(),
            type: 'nested-row',
            row: row,
            selectedColumnId: null,
            selectedBlockId: null,
            horizontalAlignment: 'center'
        };
    },

    renderPreview(block, ctx) {
        const container = document.createElement('div');
        container.classList.add('pb-block-nested-row');

        // Utiliser la méthode du canvas renderer pour générer le contenu
        const rowContent = ctx.canvasRenderer.renderRow(block.row, {
            sectionId: ctx.sectionsManager.selectedSectionId,
            rowId: block.row.id,
            isSelected: false,
            isBlockSelected: false,
            currentDevice: 'desktop',
            isNested: true,
            parentBlock: block
        });

        container.appendChild(rowContent);

        return { container };
    },

    renderInspector(block, ctx) {
        // Si un bloc imbriqué est sélectionné, afficher son inspecteur
        if (block.selectedBlockId && block.selectedColumnId) {
            const col = block.row.findColumn(block.selectedColumnId);
            if (col) {
                const nestedBlock = col.blocks.find(b => b.id === block.selectedBlockId);
                if (nestedBlock) {
                    ctx.blockTitleTarget.textContent = `Bloc imbriqué`;
                    ctx.sectionsManager.blockTypes.renderInspector(nestedBlock, ctx);
                    return;
                }
            }
        }

        // Si une colonne est sélectionnée, afficher l'inspecteur de colonne (via le contrôleur)
        if (block.selectedColumnId) {
            ctx.showColumnInspector();
            return;
        }

        // Sinon, on affiche l'inspecteur de la row standard (via le contrôleur)
        // Cela remplira l'accordéon #collapseRow avec les paramètres de block.row
        ctx.showRowInspector();
    },

    handleInspectorInput(block, event) {
        // Si un bloc imbriqué est sélectionné, déléguer
        if (block.selectedBlockId && block.selectedColumnId) {
            const col = block.row.findColumn(block.selectedColumnId);
            if (col) {
                const nestedBlock = col.blocks.find(b => b.id === block.selectedBlockId);
                if (nestedBlock) {
                    const blockType = nestedBlock.type;
                    const def = event.currentTarget.sectionsManager?.blockTypes?.types?.[blockType];
                    if (def && def.handleInspectorInput) {
                        def.handleInspectorInput(nestedBlock, event);
                    }
                }
            }
        }
    }
};
