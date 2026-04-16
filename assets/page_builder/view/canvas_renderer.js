import { t } from '../i18n.js';

export class CanvasRenderer {
    constructor(controllerContext) {
        this.ctx = controllerContext;
        this.editingElements = new Map();
    }

    render() {
        const canvas = this.ctx.canvasTarget;
        const placeholder = this.ctx.hasCanvasPlaceholderTarget
            ? this.ctx.canvasPlaceholderTarget
            : null;

        const sectionsManager = this.ctx.sectionsManager;
        const sections = sectionsManager.sections;

        // Sauvegarder la position du scroll avant le rendu complet
        const scrollX = window.scrollX;
        const scrollY = window.scrollY;

        // Sauvegarder les éléments en cours d'édition avant de nettoyer
        const editingStates = new Map();
        canvas.querySelectorAll('[contenteditable="true"][data-editing="true"]').forEach((el) => {
            const blockId = el.dataset.blockId;
            if (blockId) {
                const selection = window.getSelection();
                if (selection.rangeCount > 0 && el.contains(selection.anchorNode)) {
                    editingStates.set(blockId, {
                        content: el.innerHTML,
                        selection: this.saveSelection(el)
                    });
                }
            }
        });

        // Nettoyage
        canvas.querySelectorAll('.pb-page-section').forEach((el) => el.remove());

        if (sections.length === 0) {
            if (placeholder) {
                placeholder.classList.remove('d-none');
            }
            return;
        }

        if (placeholder) {
            placeholder.classList.add('d-none');
        }

        const currentDevice = this.ctx.deviceManager
            ? this.ctx.deviceManager.current
            : (this.ctx.deviceValue || 'desktop');

        const breakpointMap = {
            'desktop': 'xl',
            'mobile': 's'
        };
        const currentBreakpoint = breakpointMap[currentDevice] || 'xl';

        sections.forEach((section) => {
            if (currentDevice === 'desktop' && !section.visibleDesktop) {
                return;
            }
            if (currentDevice === 'mobile' && !section.visibleMobile) {
                return;
            }

            const wrapper = this.#renderSection(section, currentDevice, currentBreakpoint);
            canvas.appendChild(wrapper);
        });

        // Restaurer la position du scroll après le rendu
        window.scrollTo(scrollX, scrollY);
    }

    updateSelection() {
        const { selectedSectionId, selectedRowId, selectedColumnId, selectedBlockId } = this.ctx.sectionsManager;
        const canvas = this.ctx.canvasTarget;

        // 1. Sections
        canvas.querySelectorAll('.pb-page-section').forEach(el => {
            if (el.dataset.sectionId === selectedSectionId) {
                el.classList.add('pb-page-section--selected');
            } else {
                el.classList.remove('pb-page-section--selected');
            }
        });

        // 2. Rows
        canvas.querySelectorAll('.pb-page-section-row, .pb-nested-row').forEach(el => {
            const sectionId = el.dataset.sectionId;
            const rowId = el.dataset.rowId;

            if (sectionId === selectedSectionId && rowId === selectedRowId) {
                el.classList.add('pb-page-section-row--selected');
            } else {
                el.classList.remove('pb-page-section-row--selected');
            }
        });

        // 3. Columns
        canvas.querySelectorAll('.pb-page-column').forEach(el => {
            const sectionId = el.dataset.sectionId;
            const rowId = el.dataset.rowId;
            const columnId = el.dataset.columnId;

            // Highlight columns of the selected row
            if (sectionId === selectedSectionId && rowId === selectedRowId) {
                el.classList.add('pb-column-highlight');
            } else {
                el.classList.remove('pb-column-highlight');
            }

            // Selection
            if (sectionId === selectedSectionId && rowId === selectedRowId && columnId === selectedColumnId) {
                el.classList.add('pb-page-column--selected');
            } else {
                el.classList.remove('pb-page-column--selected');
            }
        });

        // 4. Blocks
        canvas.querySelectorAll('.pb-block').forEach(el => {
            const sectionId = el.dataset.sectionId;
            const rowId = el.dataset.rowId;
            const columnId = el.dataset.columnId;
            const blockId = el.dataset.blockId;

            if (sectionId === selectedSectionId &&
                rowId === selectedRowId &&
                columnId === selectedColumnId &&
                blockId === selectedBlockId) {
                el.classList.add('pb-block--selected');
            } else {
                el.classList.remove('pb-block--selected');
            }
        });
    }

    // --- Rendu de section ---

    #renderSection(section, currentDevice, currentBreakpoint) {
        const { selectedSectionId, selectedRowId, selectedBlockId } = this.ctx.sectionsManager;

        const wrapper = document.createElement('div');
        wrapper.classList.add('pb-page-section');
        wrapper.dataset.sectionId = section.id;

        if (section.id === selectedSectionId) {
            wrapper.classList.add('pb-page-section--selected');
        }


        wrapper.classList.add(section.layout);

        this.#applyBackground(wrapper, section.background);

        const inner = document.createElement('div');
        inner.classList.add('pb-page-section-inner');

        // Parcourir toutes les rows de la section
        section.rows.forEach((row) => {
            const rowEl = this.renderRow(row, {
                sectionId: section.id,
                rowId: row.id,
                isSelected: section.id === selectedSectionId && row.id === selectedRowId,
                isBlockSelected: selectedBlockId !== null,
                currentDevice: currentDevice,
                currentBreakpoint: currentBreakpoint
            });

            inner.appendChild(rowEl);
        });

        wrapper.appendChild(inner);

        // Ajouter les boutons d'insertion de section
        this.#renderSectionInsertButtons(wrapper, section.id);

        // Ajouter la toolbar de la section
        this.#renderSectionToolbar(wrapper, section.id);

        wrapper.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ctx.onSectionClick(section.id);
        });

        return wrapper;
    }

    #applyBackground(element, background) {
        element.style.backgroundColor = '';
        element.style.backgroundImage = '';
        element.className = element.className.replace(/\bbg-\S+/g, '');

        if (background && background.type && background.value) {
            if (background.type === 'color') {
                element.style.backgroundColor = background.value;
            } else if (background.type === 'image') {
                element.style.backgroundImage = `url('${background.value}')`;
                element.style.backgroundSize = 'cover';
                element.style.backgroundPosition = 'center';
                element.style.backgroundRepeat = 'no-repeat';
            } else if (background.type === 'class') {
                const classes = background.value.split(' ').filter(cls => cls.trim() !== '');
                classes.forEach(cls => element.classList.add(cls));
            }
        }
    }

    // --- Rendu de row (utilisé pour les rows normales ET les nested-rows) ---

    renderRow(row, context) {
        const { sectionId, rowId, isSelected, isBlockSelected, currentDevice, isNested, parentBlock, parentColumnIndex } = context;

        const rowEl = document.createElement('div');
        rowEl.classList.add(isNested ? 'pb-nested-row' : 'pb-page-section-row');
        rowEl.dataset.rowId = row.id;
        rowEl.dataset.sectionId = sectionId;

        if (row.mode === 'auto') {
            // Logique pour le mode auto
            rowEl.classList.add('pb-row--auto');
        } else {
            // Logique pour le mode fixe (par défaut)
            rowEl.classList.add('pb-row--fixed');
        }

        rowEl.classList.add(row.type);

        // Marquer la row comme sélectionnée
        if (isSelected) {
            rowEl.classList.add('pb-page-section-row--selected');

            // Si aucun bloc n'est sélectionné, surligner les colonnes
            // if (!isBlockSelected) {
            //     rowEl.classList.add('pb-row-highlight-columns');
            // }
        }

        const cols = document.createElement('div');
        cols.classList.add('pb-page-section-columns');

        // Appliquer l'alignement vertical des colonnes
        if (row.align) {
            cols.dataset.align = row.align;
        }

        // Appliquer l'alignement horizontal des colonnes
        if (row.justify) {
            cols.dataset.justify = row.justify;
        }

        let columnsToRender = row.columns;
        if (currentDevice === 'mobile' && row.reverseMobile) {
            columnsToRender = [...row.columns].reverse();
        }

        columnsToRender.forEach((colData) => {
            const col = this.#renderColumn(colData, {
                sectionId,
                rowId,
                isNested,
                parentBlock,
                parentColumnIndex: parentColumnIndex,
                isRowSelected: isSelected,
                currentDevice,
                currentBreakpoint: context.currentBreakpoint
            });

            cols.appendChild(col);
        });

        rowEl.appendChild(cols);

        this.#renderRowToolbar(rowEl, sectionId, rowId);

        rowEl.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ctx.onRowClick(sectionId, rowId);
            this.ctx.toggleRowModeParams(row.mode);
        });

        return rowEl;
    }

    #normalizeWidth(width) {
        // Si c'est déjà un nombre, le retourner tel quel
        if (typeof width === 'number') {
            return width;
        }

        // Si c'est une chaîne au format '1-2'
        if (typeof width === 'string' && width.includes('-')) {
            const parts = width.split('-');
            if (parts.length === 2) {
                const numerator = parseInt(parts[0], 10);
                const denominator = parseInt(parts[1], 10);

                if (!isNaN(numerator) && !isNaN(denominator) && denominator > 0) {
                    return parseFloat((numerator / denominator * 100).toFixed(2));
                }
            }
        }

        // Fallback: essayer de parser comme nombre
        const parsed = parseFloat(width);
        return isNaN(parsed) ? 100 : parsed;
    }

    // --- Rendu de colonne (utilisé pour les colonnes normales ET nested) ---

    #renderColumn(colData, context) {
        const { sectionId, rowId, isNested, parentBlock, isRowSelected, parentColumnIndex, currentDevice, currentBreakpoint } = context;

        const { selectedSectionId, selectedRowId, selectedColumnId } = this.ctx.sectionsManager;

        const col = document.createElement('div');
        col.classList.add('pb-page-column');

        // Ne pas mettre de largeur fixe si la row est auto
        const row = this.ctx.sectionsManager.findRowById(rowId);
        if (row && row.mode === 'auto') {
            col.style.width = 'auto';
        } else {
            const bp = currentBreakpoint || 'xl';
            const rawWidth = (colData.width && colData.width[bp]) ? colData.width[bp] : (colData.width ? colData.width.xl : null);
            const width = this.#normalizeWidth(rawWidth);
            col.style.width = `calc(${width}% - 10px)`;
        }

        col.dataset.columnId = colData.id;
        col.dataset.sectionId = sectionId;
        col.dataset.rowId = rowId;

        if (colData.horizontalAlignment) {
            col.dataset.horizontalAlignment = colData.horizontalAlignment;
        }

        if (colData.url) {
            col.dataset.hasUrl = "true";
            col.title = `Lien: ${colData.url}`;
        }

        this.#applyBackground(col, colData.background);

        if (isRowSelected) {
            col.classList.add('pb-column-highlight');
        }

        if (sectionId === selectedSectionId &&
            rowId === selectedRowId &&
            colData.id === selectedColumnId) {
            col.classList.add('pb-page-column--selected');
        }

        // Rendu du contenu de la colonne
        if (colData.blocks.length === 0) {
            this.#renderEmptyColumn(col, { sectionId, rowId, colData, isNested, parentBlock, parentColumnIndex });
        } else {
            colData.blocks.forEach((block) => {
                const blockEl = this.#renderBlock(block, {
                    sectionId,
                    rowId,
                    colData,
                    isNested,
                    parentBlock,
                    parentColumnIndex: parentColumnIndex
                });
                col.appendChild(blockEl);
            });

            const addContext = isNested ? { type: 'nested', parentBlock } : null;
            this.#renderAddToolbar(col, sectionId, rowId, colData.id, true, false, addContext);

        }

        // TOOLBAR UNIFIÉE POUR COLONNE
        const columnContext = isNested ? {
            type: 'nested',
            parentBlock,
            columnId: colData.id
        } : {
            type: 'normal',
            sectionId,
            rowId,
            columnId: colData.id
        };

        this.#renderColumnToolbar(col, columnContext);

        col.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            this.ctx.onColumnClick(sectionId, rowId, colData.id);

            // Retirer la classe border de toutes les colonnes
            // this.ctx.canvasTarget.querySelectorAll('.pb-page-column').forEach((el) => {
            //     el.classList.remove('pb-column-highlight');
            // });

            // Ajouter la classe border uniquement à cette colonne
            // col.classList.add('pb-column-highlight');
        });

        return col;
    }

    // --- Rendu de colonne vide ---

    #renderEmptyColumn(container, context) {
        const { sectionId, rowId, colData, isNested, parentBlock } = context;

        const empty = document.createElement('div');
        empty.classList.add('pb-column-empty');
        const addContext = isNested ? { type: 'nested', parentBlock } : null;
        this.#renderAddToolbar(empty, sectionId, rowId, colData.id, false, true, addContext);

        container.appendChild(empty);
    }

    #renderBlock(block, context) {
        const { sectionId, rowId, colData, isNested, parentBlock } = context;
        const { selectedSectionId, selectedRowId, selectedColumnId, selectedBlockId } = this.ctx.sectionsManager;

        const blockEl = document.createElement('div');
        blockEl.classList.add('pb-block');
        blockEl.dataset.sectionId = sectionId;
        blockEl.dataset.rowId = rowId;
        blockEl.dataset.columnId = colData.id;
        blockEl.dataset.blockId = block.id;

        if (sectionId === selectedSectionId &&
            rowId === selectedRowId &&
            colData.id === selectedColumnId &&
            block.id === selectedBlockId) {
            blockEl.classList.add('pb-block--selected');
        }

        const { container, contentElement } =
            this.ctx.sectionsManager.blockTypes.renderPreview(block, this.ctx, rowId);

        blockEl.appendChild(container);

        if (contentElement && contentElement.getAttribute('contenteditable') === 'true') {
            contentElement.dataset.blockId = block.id;

            contentElement.addEventListener('focus', () => {
                contentElement.dataset.editing = 'true';
                this.ctx.canvasTarget.querySelectorAll('.pb-page-section-row').forEach((el) => el.classList.remove('pb-row-highlight-columns'));
            });

            contentElement.addEventListener('blur', () => {
                delete contentElement.dataset.editing;
            });
        }

        // TOOLBAR UNIFIÉE
        const actionContext = isNested ? {
            type: 'nested',
            parentBlock,
            columnId: colData.id,
            blockId: block.id
        } : {
            type: 'normal',
            sectionId,
            rowId,
            columnId: colData.id,
            blockId: block.id
        };

        this.#renderBlockToolbar(blockEl, actionContext);

        // Gestion du clic
        blockEl.addEventListener('click', (event) => {
            if (event.target.getAttribute('contenteditable') === 'true') {
                event.stopPropagation();
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            this.ctx.onBlockClick(sectionId, rowId, colData.id, block.id);
            this.#showInspectorTab();
        });

        return blockEl;
    }

    #showInspectorTab() {
        const inspectorTab = document.getElementById('nav-structure-tab');
        if (inspectorTab) {
            const bsTab = new bootstrap.Tab(inspectorTab);
            bsTab.show();
        }
    }

    // --- Toolbars pour éléments normaux ---

    #renderSectionInsertButtons(wrapper, sectionId) {
        const toolbar = document.createElement('div');
        toolbar.classList.add('pb-section-insert-toolbar');

        const btnBefore = document.createElement('button');
        btnBefore.type = 'button';
        btnBefore.classList.add('pb-section-insert-btn', 'pb-section-insert-btn--before');
        btnBefore.innerHTML = '+';
        btnBefore.title = t('page.builder.canvas.section.insert_before');
        btnBefore.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ctx.sectionsManager.addSectionBefore(sectionId);
            this.ctx.renderCanvas();
            this.ctx.showSectionInspector(false);
        });

        const btnAfter = document.createElement('button');
        btnAfter.type = 'button';
        btnAfter.classList.add('pb-section-insert-btn', 'pb-section-insert-btn--after');
        btnAfter.innerHTML = '+';
        btnAfter.title = t('page.builder.canvas.section.insert_after');
        btnAfter.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.ctx.sectionsManager.addSectionAfter(sectionId);
            this.ctx.renderCanvas();
            this.ctx.showSectionInspector(false);
        });

        toolbar.appendChild(btnBefore);
        toolbar.appendChild(btnAfter);
        wrapper.appendChild(toolbar);
    }

    #renderSectionToolbar(wrapper, sectionId) {
        const toolbar = this.#createToolbar([
            { icon: '↑', title: t('page.builder.canvas.move_up'), action: () => this.ctx.sectionsManager.moveSectionUp(sectionId) },
            { icon: '↓', title: t('page.builder.canvas.move_down'), action: () => this.ctx.sectionsManager.moveSectionDown(sectionId) },
            { icon: '⎘', title: t('page.builder.canvas.section.duplicate'), action: () => this.ctx.sectionsManager.duplicateSection(sectionId) },
            { icon: '×', title: t('page.builder.canvas.section.delete'), action: () => {
                    if (confirm(t('page.builder.canvas.section.delete_confirm'))) {
                        this.ctx.sectionsManager.deleteSection(sectionId);
                    }
                }, className: 'pb-toolbar-btn--danger' }
        ], 'pb-toolbar--section');

        wrapper.appendChild(toolbar);
    }

    #renderRowToolbar(rowEl, sectionId, rowId) {
        const toolbar = this.#createToolbar([
            { icon: '↑', title: t('page.builder.canvas.move_up'), action: () => this.ctx.sectionsManager.moveRowUp(sectionId, rowId) },
            { icon: '↓', title: t('page.builder.canvas.move_down'), action: () => this.ctx.sectionsManager.moveRowDown(sectionId, rowId) },
            { icon: '⎘', title: t('page.builder.canvas.row.duplicate'), action: () => this.ctx.sectionsManager.duplicateRow(sectionId, rowId) },
            { icon: '×', title: t('page.builder.canvas.row.delete'), action: () => {
                    if (confirm(t('page.builder.canvas.row.delete_confirm'))) {
                        this.ctx.sectionsManager.deleteRow(sectionId, rowId);
                    }
                }, className: 'pb-toolbar-btn--danger' }
        ], 'pb-toolbar--row');

        rowEl.appendChild(toolbar);
    }

    #renderColumnToolbar(col, context) {
        const toolbar = this.#createToolbar([
            { icon: '←', title: t('page.builder.canvas.move_left'), action: () => this.ctx.sectionsManager.moveColumnLeft(context) },
            { icon: '→', title: t('page.builder.canvas.move_right'), action: () => this.ctx.sectionsManager.moveColumnRight(context) },
            { icon: '⎘', title: t('page.builder.canvas.column.duplicate'), action: () => this.ctx.sectionsManager.duplicateColumn(context) },
            { icon: '×', title: t('page.builder.canvas.column.delete'), action: () => {
                    if (confirm(t('page.builder.canvas.column.delete_confirm'))) {
                        this.ctx.sectionsManager.deleteColumn(context);
                    }
                }, className: 'pb-toolbar-btn--danger' }
        ], 'pb-toolbar--column');

        col.appendChild(toolbar);
    }

    #renderBlockToolbar(blockEl, context) {
        const toolbar = this.#createToolbar([
            { icon: '↑', title: t('page.builder.canvas.move_up'), action: () => this.ctx.sectionsManager.moveBlockUp(context) },
            { icon: '↓', title: t('page.builder.canvas.move_down'), action: () => this.ctx.sectionsManager.moveBlockDown(context) },
            { icon: '⎘', title: t('page.builder.canvas.block.duplicate'), action: () => this.ctx.sectionsManager.duplicateBlock(context) },
            { icon: '×', title: t('page.builder.canvas.block.delete'), action: () => {
                    if (confirm(t('page.builder.canvas.block.delete_confirm'))) {
                        this.ctx.sectionsManager.deleteBlock(context);
                    }
                }, className: 'pb-toolbar-btn--danger' }
        ], 'pb-toolbar--block');

        blockEl.appendChild(toolbar);
    }

    // --- Toolbar générique ---

    #createToolbar(buttons, extraClass = '') {
        const toolbar = document.createElement('div');
        toolbar.classList.add('pb-toolbar');
        if (extraClass) {
            toolbar.classList.add(extraClass);
        }

        buttons.forEach(({ icon, title, action, className }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.classList.add('pb-toolbar-btn');
            if (className) btn.classList.add(className);
            btn.innerHTML = icon;
            btn.title = title;
            btn.addEventListener('click', (event) => {
                event.stopPropagation();
                action();
                this.ctx.renderCanvas();
            });
            toolbar.appendChild(btn);
        });

        return toolbar;
    }

    // --- Add toolbar ---

    #renderAddToolbar(container, sectionId, rowId, columnId, withPrefix, withToolbar) {
        const makeButton = (label, type, showInspector) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = withPrefix ? `+ ${label}` : label;
            btn.addEventListener('click', (event) => {
                event.stopPropagation();

                this.ctx.sectionsManager.addBlock(sectionId, rowId, columnId, type);
                this.ctx.renderCanvas();

                if (showInspector) {
                    this.ctx.tabs.activate('inspector');
                    this.ctx.showSectionInspector();
                    this.ctx.showBlockInspector();
                }
            });
            return btn;
        };

        if (withToolbar) {
            const toolbar = document.createElement('div');
            toolbar.className = 'pb-column-add-toolbar';
            toolbar.appendChild(makeButton(t('page.builder.block.title'), 'title', true));
            toolbar.appendChild(makeButton(t('page.builder.block.text'), 'text', true));
            toolbar.appendChild(makeButton(t('page.builder.block.button'), 'button', false));
            toolbar.appendChild(makeButton(t('page.builder.block.image'), 'image', false));
            toolbar.appendChild(makeButton(t('page.builder.canvas.column.quick_add_icon_box'), 'icon-box', true));

            container.appendChild(toolbar);
        }
    }

    // --- Utilitaires ---

    saveSelection(containerEl) {
        const selection = window.getSelection();
        if (selection.rangeCount === 0) return null;

        const range = selection.getRangeAt(0);
        const preSelectionRange = range.cloneRange();
        preSelectionRange.selectNodeContents(containerEl);
        preSelectionRange.setEnd(range.startContainer, range.startOffset);
        const start = preSelectionRange.toString().length;

        return {
            start: start,
            end: start + range.toString().length
        };
    }
}
