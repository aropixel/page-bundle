
import { Row } from '../row.js';
import { Column } from '../column.js';

export class SectionActions {
    constructor(sectionsManager) {
        this.manager = sectionsManager;
    }

    addSection() {
        const id = Date.now().toString();
        //const defaultPreset = this.manager.columnPresets[1][0]; // 2 colonnes 50/50

        const defaultRow = new Row();
        defaultRow.setColumns(this.manager.createColumnsFromPreset());

        const section = {
            id,
            name: '',
            layout: 'container',
            rows: [defaultRow],
            visibleDesktop: true,
            visibleMobile: true,
            background: { type: null, value: null }
        };

        this.manager.sections.push(section);
        this.manager.selectSection(id);
        this.manager.selectRow(id, 0);
        this.manager.selectColumn(id, 0, 0);

        return section;
    }

    addSectionBefore(sectionId) {
        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        if (index === -1) return null;

        const section = this.#createEmptySection();
        this.manager.sections.splice(index, 0, section);
        this.manager.selectSection(section.id);

        return section;
    }

    addSectionAfter(sectionId) {
        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        if (index === -1) return null;

        const section = this.#createEmptySection();
        this.manager.sections.splice(index + 1, 0, section);
        this.manager.selectSection(section.id);

        return section;
    }

    addSectionFromTemplate(templateType) {
        const id = Date.now().toString();

        const templateStructures = {
            'hero': {
                name: 'Hero',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'text' }] },
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'button' }] }
                ]
            },
            'split': {
                name: 'Image/Texte',
                rows: [
                    { columns: ['1-2', '1-2'], blocks: [
                            { columnIndex: 0, type: 'image' },
                            { columnIndex: 1, type: 'title' },
                            { columnIndex: 1, type: 'text' }
                    ]},
                ]
            },
            'slider': {
                name: 'Slider/Texte',
                rows: [
                    { columns: ['1-2', '1-2'], blocks: [
                            { columnIndex: 0, type: 'slider' },
                            { columnIndex: 1, type: 'title' },
                            { columnIndex: 1, type: 'text' }
                        ]},
                ]
            },
            'iconbox': {
                name: 'Icon-box',
                rows: [
                    { columns: ['1-3', '1-3', '1-3'], blocks: [
                            { columnIndex: 0, type: 'image' },
                            { columnIndex: 0, type: 'title' },
                            { columnIndex: 1, type: 'image' },
                            { columnIndex: 1, type: 'title' },
                            { columnIndex: 2, type: 'image' },
                            { columnIndex: 2, type: 'title' },
                        ]},
                ]
            },
            'sidebar': {
                name: 'Titre + Image/Contenu',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-3', '2-3'], blocks: [
                            { columnIndex: 0, type: 'image' },
                            { columnIndex: 1, type: 'text' },
                            { columnIndex: 1, type: 'button' }
                        ]}
                ]
            },
            'trio': {
                name: '3 colonnes de texte',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-3', '1-3', '1-3'], blocks: [
                            { columnIndex: 0, type: 'text' },
                            { columnIndex: 1, type: 'text' },
                            { columnIndex: 2, type: 'text' }
                        ]},
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'button' }] }
                ]
            },
            'cards': {
                name: '3 colonnes avec bouton',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-3', '1-3', '1-3'], blocks: [
                            { columnIndex: 0, type: 'text' },
                            { columnIndex: 0, type: 'button' },
                            { columnIndex: 1, type: 'text' },
                            { columnIndex: 1, type: 'button' },
                            { columnIndex: 2, type: 'text' },
                            { columnIndex: 2, type: 'button' }
                        ]}
                ]
            },
            'gallery-3': {
                name: 'Galerie 3 images',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-3', '1-3', '1-3'], blocks: [
                            { columnIndex: 0, type: 'image' },
                            { columnIndex: 1, type: 'image' },
                            { columnIndex: 2, type: 'image' }
                        ]},
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'button' }] }
                ]
            },
            'gallery-4': {
                name: 'Galerie 4 images',
                rows: [
                    { columns: ['1-1'], blocks: [{ columnIndex: 0, type: 'title' }] },
                    { columns: ['1-4', '1-4', '1-4', '1-4'], blocks: [
                            { columnIndex: 0, type: 'image' },
                            { columnIndex: 1, type: 'image' },
                            { columnIndex: 2, type: 'image' },
                            { columnIndex: 3, type: 'image' }
                        ]}
                ]
            }
        };

        const structure = templateStructures[templateType];
        if (!structure) return null;

        const section = {
            id,
            name: structure.name,
            layout: 'container',
            rows: [],
            visibleDesktop: true,
            visibleMobile: true,
            background: { type: null, value: null }
        };

        structure.rows.forEach(rowDef => {
            const row = new Row();
            if (templateType === 'iconbox') {
                row.type = 'icon-box';
            }

            if (templateType === 'ticketing') {
                row.type = 'ticketing';
                row.align = 'stretch';
                row.justify = 'center';
            }

            rowDef.columns.forEach((width, i) => {
                const col = new Column(width);
                row.columns.push(col);
            });

            rowDef.blocks.forEach(blockDef => {
                const blockData = this.manager.blockTypes.createBlock(blockDef.type);
                if (templateType === 'iconbox') {
                    if (blockData.type === 'image') {
                        blockData.maxWidth = 60;
                        blockData.maxHeight = 60;
                        blockData.width = 100;
                        blockData.useOriginalSize = false;
                    }
                    if (blockData.type === 'title') {
                        blockData.content = 'Titre de la card';
                        blockData.size = 'h3';
                        blockData.horizontalAlignment = 'center';
                    }
                }
                const column = row.columns[blockDef.columnIndex];
                if (column) {
                    column.addBlock(blockData);
                    if (templateType === 'iconbox') {
                        column.width.m = '1-2';
                        column.width.s = '1-1';
                    }

                    if (templateType === 'ticketing') {
                        column.width.l = '1-3';
                        column.width.m = '1-2';
                        column.width.s = '1-1';
                    }
                }
            });

            section.rows.push(row);
        });

        this.manager.sections.push(section);

        if (section.rows[0] && section.rows[0].columns[0] && section.rows[0].columns[0].blocks.length > 0) {
            this.manager.selectedSectionId = id;
            this.manager.selectedRowId = 0;
            this.manager.selectedColumnId = 0;
            this.manager.selectedBlockId = section.rows[0].columns[0].blocks[0].id;
        }

        return section;
    }

    deleteSection(sectionId) {
        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        if (index === -1) return;

        this.manager.sections.splice(index, 1);
        this.manager.selectedSectionId = null;
        this.manager.selectedRowId = null;
        this.manager.selectedColumnId = null;
        this.manager.selectedBlockId = null;
    }

    duplicateSection(sectionId) {
        const section = this.manager.sections.find((s) => s.id === sectionId);
        if (!section) return null;

        const newId = Date.now().toString();

        const duplicated = {
            id: newId,
            name: section.name,
            layout: section.layout,
            rows: [],
            visibleDesktop: section.visibleDesktop,
            visibleMobile: section.visibleMobile,
            background: section.background
        };

        section.rows.forEach((row) => {
            const newRow = new Row();

            const newColumns = row.columns.map((col) => {
                const newCol = new Column(col.width);

                col.blocks.forEach((block) => {
                    const duplicatedBlock = JSON.parse(JSON.stringify(block));
                    duplicatedBlock.id = `block-${Date.now()}-${Math.random().toString(16).slice(2)}`;
                    newCol.addBlock(duplicatedBlock);
                });

                newCol.horizontalAlignment = col.horizontalAlignment;
                newCol.background = col.background;

                return newCol;
            });

            newRow.setColumns(newColumns);
            duplicated.rows.push(newRow);
        });

        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        this.manager.sections.splice(index + 1, 0, duplicated);
        this.manager.selectSection(newId);

        return duplicated;
    }

    moveSectionUp(sectionId) {
        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        if (index <= 0) return;

        [this.manager.sections[index - 1], this.manager.sections[index]] =
            [this.manager.sections[index], this.manager.sections[index - 1]];
    }

    moveSectionDown(sectionId) {
        const index = this.manager.sections.findIndex((s) => s.id === sectionId);
        if (index === -1 || index >= this.manager.sections.length - 1) return;

        [this.manager.sections[index], this.manager.sections[index + 1]] =
            [this.manager.sections[index + 1], this.manager.sections[index]];
    }

    updateSectionName(name) {
        const section = this.manager.selectedSection;
        if (!section) return;
        section.name = name;
    }

    updateSectionLayout(layout) {
        const section = this.manager.selectedSection;
        if (!section) return;
        section.layout = layout;
    }

    updateSectionVisibility({ desktop, mobile }) {
        const section = this.manager.selectedSection;
        if (!section) return;

        section.visibleDesktop = desktop;
        section.visibleMobile = mobile;
    }


    updateSectionBackground(type, value, imageId = null) {
        const section = this.manager.selectedSection;
        if (!section) return;

        if (!type) {
            section.background = null;
        } else {
            section.background = {
                type: type,
                value: value,
                imageId: imageId,
            };
        }
    }

    #createEmptySection() {
        const id = Date.now().toString();

        const defaultRow = new Row();
        defaultRow.setColumns(this.manager.createColumnsFromPreset());

        return {
            id,
            name: '',
            layout: 'container',
            rows: [defaultRow],
            visibleDesktop: true,
            visibleMobile: true,
            background: { type: null, value: null }
        };
    }
}
