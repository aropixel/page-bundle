import { Row } from '../row.js';
import { Column } from '../column.js';

export class RowActions {
    constructor(sectionsManager) {
        this.manager = sectionsManager;
    }

    // --- Rows ---

    addRow(sectionId) {
        const section = this.manager.sections.find((s) => s.id === sectionId);
        if (!section) return null;

        const newRow = new Row();
        const col = new Column('1-1');
        newRow.addColumn(col);

        section.rows.push(newRow);

        this.manager.selectedSectionId = sectionId;
        this.manager.selectedRowId = newRow.id;  // ← ID au lieu d'index
        this.manager.selectedColumnId = col.id;
        this.manager.selectedBlockId = null;

        return newRow;
    }

    deleteRow(sectionId, rowId) {
        const section = this.manager.sections.find((s) => s.id === sectionId);
        if (!section) return;

        const rowIndex = section.rows.findIndex(r => r.id === rowId);
        if (rowIndex === -1) return;

        section.rows.splice(rowIndex, 1);
        this.manager.selectedRowId = null;
        this.manager.selectedColumnId = null;
        this.manager.selectedBlockId = null;
    }

    duplicateRow(sectionId, rowId) {
        const section = this.manager.sections.find(s => s.id === sectionId);
        if (!section) return null;

        const rowIndex = section.rows.findIndex(r => r.id === rowId);
        if (rowIndex === -1) return null;

        const row = section.rows[rowIndex];
        const duplicated = row.clone();

        section.rows.splice(rowIndex + 1, 0, duplicated);
        this.manager.selectedRowId = duplicated.id;

        return duplicated;
    }

    moveRowUp(sectionId, rowId) {
        const section = this.manager.sections.find(s => s.id === sectionId);
        if (!section) return;

        const rowIndex = section.rows.findIndex(r => r.id === rowId);
        if (rowIndex <= 0) return;

        this.#swapRows(section.rows, rowIndex, rowIndex - 1);
    }

    moveRowDown(sectionId, rowId) {
        const section = this.manager.sections.find(s => s.id === sectionId);
        if (!section) return;

        const rowIndex = section.rows.findIndex(r => r.id === rowId);
        if (rowIndex === -1 || rowIndex >= section.rows.length - 1) return;

        this.#swapRows(section.rows, rowIndex, rowIndex + 1);
    }

    #swapRows(rows, i, j) {
        [rows[i], rows[j]] = [rows[j], rows[i]];
    }

    updateRowAlignment(alignment) {
        const row = this.manager.selectedRow;
        if (!row) return;

        row.align = alignment;
    }

    updateRowJustify(justify) {
        const row = this.manager.selectedRow;
        if (!row) return;

        row.justify = justify;
    }

    updateRowResponsive(options) {
        const row = this.manager.selectedRow;
        if (!row) return;

        if (options.reverseMobile !== undefined) {
            row.reverseMobile = options.reverseMobile;
        }
    }

    updateRowMode(mode) {
        const row = this.manager.selectedRow;
        if (!row) return;
        row.mode = mode;

        return mode;
    }

    updateRowType(type) {
        const row = this.manager.selectedRow;
        if (!row) return;
        row.type = type;
    }

    updateRowImgWidth(width) {
        const row = this.manager.selectedRow;
        if (!row) return;
        row.imgWidth = width;
    }

    updateRowSlider(breakpointOrNull) {
        const row = this.manager.selectedRow;
        if (!row) return;
        row.slider = breakpointOrNull; // null | 's' | 'm' | 'l' | 'xl'
    }

}
