import { Column } from '../column.js';

export class ColumnActions {
    constructor(sectionsManager) {
        this.manager = sectionsManager;
    }

    // --- Méthode helper unifiée pour trouver une row (normale OU nested) ---
    #findRow(context) {
        if (context.type === 'nested') {
            // Cas nested : on retourne directement parentBlock.row
            return context.parentBlock.row;
        } else {
            // Cas normal : on cherche dans section > row
            const section = this.manager.sections.find(s => s.id === context.sectionId);
            if (!section) return null;

            return section.rows.find(r => r.id === context.rowId);
        }
    }

    setRowColumnsPresetFromString(widthString, breakpoint = null) {
        const row = this.manager.selectedRow;
        if (!row) return;

        this.manager.applyColumnsPreset(row, widthString, breakpoint);
    }

    // --- Columns ---

    deleteColumn(context) {
        const row = this.#findRow(context);
        if (!row) return;

        row.columns = row.columns.filter(col => col.id !== context.columnId);

        if (context.type === 'nested') {
            context.parentBlock.selectedColumnId = null;
            context.parentBlock.selectedBlockId = null;
        } else {
            this.manager.selectedColumnId = null;
            this.manager.selectedBlockId = null;
        }
    }

    duplicateColumn(context) {
        const row = this.#findRow(context);
        if (!row) return null;

        const colIndex = row.columns.findIndex(col => col.id === context.columnId);
        if (colIndex === -1) return null;

        const col = row.columns[colIndex];
        const duplicated = col.clone();

        row.columns.splice(colIndex + 1, 0, duplicated);

        if (context.type === 'nested') {
            context.parentBlock.selectedColumnId = duplicated.id;
        } else {
            this.manager.selectedColumnId = duplicated.id;
        }

        this.manager.selectedRowId = null;

        return duplicated;
    }

    moveColumnLeft(context) {
        const row = this.#findRow(context);
        if (!row) return;

        const index = row.columns.findIndex(col => col.id === context.columnId);
        if (index <= 0) return;

        this.#swapColumns(row.columns, index, index - 1);
    }

    moveColumnRight(context) {
        const row = this.#findRow(context);
        if (!row) return;

        const index = row.columns.findIndex(col => col.id === context.columnId);
        if (index === -1 || index >= row.columns.length - 1) return;

        this.#swapColumns(row.columns, index, index + 1);
    }

    #swapColumns(columns, i, j) {
        [columns[i], columns[j]] = [columns[j], columns[i]];
    }

    updateColumnHorizontalAlignment(alignment) {
        const column = this.manager.selectedColumn;
        if (!column) return;

        column.horizontalAlignment = alignment;
    }

    updateColumnBackground(type, value, imageId = null) {
        const column = this.manager.selectedColumn;
        if (!column) return;

        if (!type) {
            column.background = null;
        } else {
            column.background = {
                type: type,
                value: value,
                imageId: imageId,
            };
        }
    }

    updateColumnUrl(url) {
        const column = this.manager.selectedColumn;
        if (!column) return;

        column.url = url;
    }

    updateColumnPagePath(slug) {
        const column = this.manager.selectedColumn;
        if (!column) return;

        column.pagePath = slug;
    }

    updateColumnHeight(height) {
        const column = this.manager.selectedColumn;
        if (!column) return;

        column.height = height;
    }
}
