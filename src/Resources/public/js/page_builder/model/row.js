import { Column } from './column.js';

export class Row {
    constructor() {
        this.id = `row-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        this.columns = [];
        this.align = 'center';
        this.justify = 'center';
        this.reverseMobile = false;
        this.type = 'default'; // 'default' ou 'icon-box'
        this.mode = 'fixed';
        this.imgWidth = 60;
        this.slider = null; // null | 's' | 'm' | 'l' | 'xl'
    }

    addColumn(column) {
        this.columns.push(column);
        return column;
    }

    setColumns(columns) {
        this.columns = columns;
    }

    findColumn(columnId) {
        return this.columns.find(col => col.id === columnId) || null;
    }

    findBlock(columnId, blockId) {
        const col = this.findColumn(columnId);
        return col ? col.findBlock(blockId) : null;
    }

    clone() {
        const cloned = new Row();
        cloned.align = this.align;
        cloned.justify = this.justify;
        cloned.reverseMobile = this.reverseMobile;
        cloned.type = this.type;
        cloned.mode = this.mode;
        cloned.columns = this.columns.map(col => col.clone());
        cloned.imgWidth = this.imgWidth;
        cloned.slider = this.slider;
        return cloned;
    }
}
