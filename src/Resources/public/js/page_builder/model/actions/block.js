export class BlockActions {
    constructor(sectionsManager) {
        this.manager = sectionsManager;
    }

    #findColumn(context) {
        return this.manager.findColumnById(context.columnId);
    }

    addBlock(sectionId, rowId, columnId, type) {

        const col = this.manager.findColumnById(columnId);
        if (!col) {
            console.error(`Colonne ${columnId} non trouvée`);
            return null;
        }

        const block = this.manager.blockTypes.createBlock(type);

        // si le block est de type cta/icon-box
        if (Array.isArray(block)) {
            block.forEach(childBlock => col.addBlock(childBlock));
        } else {
            col.addBlock(block);
        }

        this.manager.selectedSectionId = sectionId;
        this.manager.selectedRowId = rowId;
        this.manager.selectedColumnId = columnId;
        this.manager.selectedBlockId = block.id;

        return block;
    }

    deleteBlock(context) {
        const col = this.#findColumn(context);
        if (!col) return;

        col.blocks = col.blocks.filter(block => block.id !== context.blockId);
        this.#updateSelection(context, null);
    }

    duplicateBlock(context) {
        const col = this.#findColumn(context);
        if (!col) return null;

        const blockIndex = col.blocks.findIndex(b => b.id === context.blockId);
        if (blockIndex === -1) return null;

        const block = col.blocks[blockIndex];
        const duplicated = JSON.parse(JSON.stringify(block));
        duplicated.id = `block-${Date.now()}-${Math.random().toString(16).slice(2)}`;

        col.blocks.splice(blockIndex + 1, 0, duplicated);

        this.#updateSelection(context, duplicated.id);

        return duplicated;
    }

    #updateSelection(context, blockId) {
        if (context.type === 'nested') {
            context.parentBlock.selectedBlockId = blockId;
        } else {
            this.manager.selectedBlockId = blockId;
        }
    }

    moveBlockUp(context) {
        const col = this.#findColumn(context);
        if (!col) return;

        const index = col.blocks.findIndex(b => b.id === context.blockId);
        if (index <= 0) return;

        this.#swapBlocks(col.blocks, index, index - 1);
    }

    moveBlockDown(context) {
        const col = this.#findColumn(context);
        if (!col) return;

        const index = col.blocks.findIndex(b => b.id === context.blockId);
        if (index === -1 || index >= col.blocks.length - 1) return;

        this.#swapBlocks(col.blocks, index, index + 1);
    }

    #swapBlocks(blocks, i, j) {
        [blocks[i], blocks[j]] = [blocks[j], blocks[i]];
    }

    updateTextBlockContent(blockId, content) {
        const block = this.manager.findBlockById(blockId);
        if (block && block.type === 'text') {
            block.content = content;
        }
    }
    updateBlockHorizontalAlignment(alignment) {
        const block = this.manager.selectedBlock;
        if (!block) return;

        block.horizontalAlignment = alignment;
    }
}
