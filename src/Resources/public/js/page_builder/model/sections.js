import { Column } from './column.js';
import { Row } from './row.js';
import { BlockTypesRegistry } from './block_types.js';
import { SectionActions } from './actions/section.js';
import { RowActions } from './actions/row.js';
import { ColumnActions } from './actions/column.js';
import { BlockActions } from './actions/block.js';

export class SectionsManager {
    constructor(columnPresets) {
        this.columnPresets = columnPresets;
        this.sections = [];

        this.selectedSectionId = null;
        this.selectedRowId = null;
        this.selectedColumnId = null;
        this.selectedBlockId = null;

        this.blockTypes = new BlockTypesRegistry();

        // Instancier les actions
        this.sectionActions = new SectionActions(this);
        this.rowActions = new RowActions(this);
        this.columnActions = new ColumnActions(this);
        this.blockActions = new BlockActions(this);

        this.background = {
            type: null, // 'color', 'image', 'class'
            value: null
        };
    }

    // --- Helpers ---

    createColumnsFromPreset(preset = '1-1') {
        return [new Column(preset)];
    }

    applyColumnsPreset(row, width, breakpoint = null) {
        if (!row) return;

        const currentColumns = row.columns;
        const newColumns = [];

        for (let i = 0; i < currentColumns.length; i++) {
            const col = currentColumns[i];
            if (breakpoint) {
                col.width[breakpoint] = width;
            } else {
                col.width.l = width;
                col.width.xl = width;
                col.width.m = width;
                col.width.s = width;
            }
            newColumns.push(col);
        }

        row.columns = newColumns;
    }

    // --- Sélection ---

    get selectedSection() {
        return this.sections.find((s) => s.id === this.selectedSectionId) || null;
    }

    /**
     * Trouve une row par son ID (même nested)
     */
    findRowById(rowId, withinSection = null) {
        const sectionsToSearch = withinSection ? [withinSection] : this.sections;

        for (const section of sectionsToSearch) {
            for (const row of section.rows) {
                if (row.id === rowId) return row;

                // Chercher dans les nested-rows
                for (const col of row.columns) {
                    for (const block of col.blocks) {
                        if (block.type === 'nested-row' && block.row && block.row.id === rowId) {
                            return block.row;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Trouve une colonne par son ID (même nested)
     */
    findColumnById(columnId, withinSection = null) {
        const sectionsToSearch = withinSection ? [withinSection] : this.sections;

        for (const section of sectionsToSearch) {
            for (const row of section.rows) {
                // Chercher dans les colonnes normales
                const col = row.columns.find(c => c.id === columnId);
                if (col) return col;

                // Chercher dans les colonnes nested
                for (const column of row.columns) {
                    for (const block of column.blocks) {
                        if (block.type === 'nested-row' && block.row) {
                            const nestedCol = block.row.columns.find(c => c.id === columnId);
                            if (nestedCol) return nestedCol;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Trouve un bloc par son ID (même nested)
     */
    findBlockById(blockId, withinSection = null) {
        const sectionsToSearch = withinSection ? [withinSection] : this.sections;

        for (const section of sectionsToSearch) {
            for (const row of section.rows) {
                for (const col of row.columns) {
                    // Chercher dans les blocs normaux
                    const block = col.blocks.find(b => b.id === blockId);
                    if (block) return block;

                    // Chercher dans les blocs nested
                    for (const parentBlock of col.blocks) {
                        if (parentBlock.type === 'nested-row' && parentBlock.row) {
                            for (const nestedCol of parentBlock.row.columns) {
                                const nestedBlock = nestedCol.blocks.find(b => b.id === blockId);
                                if (nestedBlock) return nestedBlock;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    get selectedRow() {
        const section = this.selectedSection;
        if (!section || this.selectedRowId == null) return null;

        // Chercher d'abord dans les rows principales
        const directRow = section.rows.find(row => row.id === this.selectedRowId);
        if (directRow) return directRow;

        // Si pas trouvée, chercher dans les nested-rows
        for (const row of section.rows) {
            for (const col of row.columns) {
                for (const block of col.blocks) {
                    if ((block.type === 'nested-row') && block.row && block.row.id === this.selectedRowId) {
                        return block.row;
                    }
                }
            }
        }

        return null;
    }

    get selectedColumn() {
        const row = this.selectedRow;
        if (!row || this.selectedColumnId == null) return null;

        return row.columns.find(col => col.id === this.selectedColumnId);
    }

    get selectedBlock() {
        const column = this.selectedColumn;
        if (!column) return null;

        return column.findBlock(this.selectedBlockId);
    }

    selectSection(id) {
        this.selectedSectionId = id;
        this.selectedRowId = null;
        this.selectedColumnId = null;
        this.selectedBlockId = null;
    }

    selectRow(sectionId, rowId) {
        this.selectedSectionId = sectionId;
        this.selectedRowId = rowId;
        this.selectedColumnId = null;
        this.selectedBlockId = null;
    }

    selectColumn(sectionId, rowId, columnId) {
        this.selectedSectionId = sectionId;
        this.selectedRowId = rowId;
        this.selectedColumnId = columnId;
        this.selectedBlockId = null;
    }

    selectBlock(sectionId, rowId, columnId, blockId) {
        this.selectedSectionId = sectionId;
        this.selectedRowId = rowId;
        this.selectedColumnId = columnId;
        this.selectedBlockId = blockId;
    }

    resetSelection() {
        this.selectedSectionId = null;
        this.selectedRowId = null;
        this.selectedColumnId = null;
        this.selectedBlockId = null;

        const selectors = [
            '.pb-block--selected',
            '.pb-page-column--selected',
            '.pb-page-row--selected'
        ];

        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(el => {
                el.classList.remove(selector.substring(1));
            });
        });
    }

    // --- Délégation aux actions ---

    // Sections
    addSection() { return this.sectionActions.addSection(); }
    addSectionBefore(sectionId) { return this.sectionActions.addSectionBefore(sectionId); }
    addSectionAfter(sectionId) { return this.sectionActions.addSectionAfter(sectionId); }
    addSectionFromTemplate(templateType) { return this.sectionActions.addSectionFromTemplate(templateType); }
    deleteSection(sectionId) { this.sectionActions.deleteSection(sectionId); }
    duplicateSection(sectionId) { return this.sectionActions.duplicateSection(sectionId); }
    moveSectionUp(sectionId) { this.sectionActions.moveSectionUp(sectionId); }
    moveSectionDown(sectionId) { this.sectionActions.moveSectionDown(sectionId); }
    updateSectionName(name) { this.sectionActions.updateSectionName(name); }
    updateSectionLayout(layout) { this.sectionActions.updateSectionLayout(layout); }
    updateSectionVisibility(options) { this.sectionActions.updateSectionVisibility(options); }
    updateSectionActive(active) { this.sectionActions.updateSectionActive(active); }
    updateSectionBackground(type, value) { this.sectionActions.updateSectionBackground(type, value); }


    // Rows
    addRow(sectionId) { return this.rowActions.addRow(sectionId); }
    deleteRow(sectionId, rowId) { this.rowActions.deleteRow(sectionId, rowId); }
    duplicateRow(sectionId, rowId) { return this.rowActions.duplicateRow(sectionId, rowId); }
    moveRowUp(sectionId, rowId) { this.rowActions.moveRowUp(sectionId, rowId); }
    moveRowDown(sectionId, rowId) { this.rowActions.moveRowDown(sectionId, rowId); }
    updateRowAlignment(alignment) { this.rowActions.updateRowAlignment(alignment); }
    updateRowJustify(justify) { this.rowActions.updateRowJustify(justify); }
    updateRowResponsive(options) { this.rowActions.updateRowResponsive(options); }
    updateRowMode(mode) { return this.rowActions.updateRowMode(mode); }
    updateRowType(type) { this.rowActions.updateRowType(type); }
    updateRowImgWidth(width) { this.rowActions.updateRowImgWidth(width); }
    updateRowSlider(value) { this.rowActions.updateRowSlider(value); }

    // Columns
    setRowColumnsPresetFromString(widthsString, breakpoint = null) { this.columnActions.setRowColumnsPresetFromString(widthsString, breakpoint); }
    deleteColumn(context) { this.columnActions.deleteColumn(context); }
    duplicateColumn(context) { return this.columnActions.duplicateColumn(context); }
    moveColumnLeft(context) { this.columnActions.moveColumnLeft(context); }
    moveColumnRight(context) { this.columnActions.moveColumnRight(context); }
    updateColumnHorizontalAlignment(alignment) { this.columnActions.updateColumnHorizontalAlignment(alignment); }
    updateColumnBackground(type, value) { this.columnActions.updateColumnBackground(type, value); }
    updateColumnBackgroundOverlay(opacity) { this.columnActions.updateColumnBackgroundOverlay(opacity); }
    updateColumnUrl(url) { this.columnActions.updateColumnUrl(url); }
    updateColumnPagePath(slug, parentSlug) { this.columnActions.updateColumnPagePath(slug, parentSlug); }
    updateColumnHeight(height) { this.columnActions.updateColumnHeight(height); }
    updateColumnBorderRadius(borderRadius) { this.columnActions.updateColumnBorderRadius(borderRadius); }

    // Blocks
    addBlock(sectionId, rowId, columnId, type) { return this.blockActions.addBlock(sectionId, rowId, columnId, type); }
    deleteBlock(context) { this.blockActions.deleteBlock(context); }
    duplicateBlock(context) { return this.blockActions.duplicateBlock(context); }
    moveBlockUp(context) { this.blockActions.moveBlockUp(context); }
    moveBlockDown(context) { this.blockActions.moveBlockDown(context); }
    updateTextBlockContent(blockId, content) { this.blockActions.updateTextBlockContent(blockId, content); }
    updateBlockHorizontalAlignment(alignment) { this.blockActions.updateBlockHorizontalAlignment(alignment); }
}
