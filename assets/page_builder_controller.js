import { Controller } from '@hotwired/stimulus';
import { SectionsManager } from './page_builder/model/sections.js';
import { Column } from './page_builder/model/column.js';
import { Row } from "./page_builder/model/row.js";
import { CanvasRenderer } from './page_builder/view/canvas_renderer.js';
import { DeviceManager } from './page_builder/view/device.js';
import { InspectorView } from './page_builder/view/inspector.js';
import { TabsView } from './page_builder/view/tabs.js';
import { initImageManager } from '/bundles/aropixeladmin/js/module/image-manager/launcher.js';
import { IM_Library } from '/bundles/aropixeladmin/js/module/image-manager/library.js';


export default class extends Controller {
    static targets = [
        'tabButton',
        'panel',
        'localeSelect',
        'syncLocaleInput',
        'inspectorPanelDefault',
        //'inspectorPanelPage',
        'inspectorPanelSection',
        'inspectorPanelColumn',
        'inspectorPanelBlock',
        'deviceButton',
        'canvas',
        'canvasPlaceholder',
        // 'sectionTitle',
        'sectionNameInput',
        'sectionColumnsEditor',
        'sectionColumnsCount',
        'sectionVisibleDesktopInput',
        'sectionVisibleMobileInput',
        'sectionLayoutContainer',
        'sectionBackgroundType',
        'sectionBackgroundColorInput',
        'sectionBackgroundImageInput',
        'sectionBackgroundImageValue',
        'sectionBackgroundImageName',
        'sectionBackgroundClassInput',
        'blockTitle',
        'blockContentInput',
        'blockUrlInput',
        'blockAlignmentButton',
        'nameDisplay',
        'nameText',
        'nameInput',
        'titleField',
        'statusField',
        'columnLinkTypeSelect',
        'columnUrlInputContainer',
        'columnPagePathSelectContainer',
        'columnUrlInput',
        'columnPagePathSelect',
        'columnHeightSelect',
        'columnBackgroundTypeSelect',
        'columnBackgroundColorInput',
        'columnBackgroundImageInput',
        'columnBackgroundImageValue',
        'columnBackgroundImageName',
        'columnBackgroundClassInput',
        'columnAlignmentButton',
        'blockLinkTypeSelect',
        'blockUrlInputContainer',
        'blockPagePathSelectContainer',
        'blockPagePathSelect',
        'rowReverseMobileInput',
        'rowTypeSelect',
        'rowModeSelect',
        'rowImgWidthInput',
        'rowFixedParams',
        'rowAutoParams',
        'rowSliderSelector'
    ];

    static values = {
        initialContent: String,
        device: { type: String, default: 'desktop' },
    };

    connect() {
        const configEl = document.getElementById('page-builder-config');
        this.pageBuilderConfig = configEl ? JSON.parse(configEl.textContent) : {};
        window.__pbTranslations = this.pageBuilderConfig.translations || {};

        this.columnPresets = ['1-1', '1-2', '1-3', '1-4', '1-5', '1-6'];

        // Gestion Multilingue : Initialisation
        this.managers = {};
        this.locales = this.pageBuilderConfig?.locales || [];
        this.primaryLocale = this.locales[0] || 'fr';
        this.currentLocale = this.primaryLocale;

        // On initialise le manager pour la langue courante
        this.sectionsManager = new SectionsManager(this.columnPresets);

        // CHARGEMENT DU CONTENU INITIAL (BDD)
        if (this.hasInitialContentValue && this.initialContentValue) {
            try {
                const initialData = JSON.parse(this.initialContentValue);

                if (initialData && Array.isArray(initialData.sections)) {
                    // On réhydrate les sections, rows ET les instances de Column
                    this.sectionsManager.sections = initialData.sections.map(section => {
                        if (section.rows) {
                            section.rows = section.rows.map(rowData => {
                                // ← RÉHYDRATER la Row
                                const row = new Row();
                                row.id = rowData.id;
                                row.align = rowData.align || 'center';
                                row.justify = rowData.justify || 'center';
                                row.reverseMobile = rowData.reverseMobile || false;
                                row.type = rowData.type || 'default';
                                row.mode = rowData.mode || 'fixed';
                                row.imgWidth = rowData.imgWidth || '60';
                                row.slider = (rowData.slider !== undefined) ? rowData.slider : null;

                                if (rowData.columns) {
                                    row.columns = rowData.columns.map(colData => {
                                        const col = new Column(colData.width);
                                        col.id = colData.id;  // ← Préserver l'ID
                                        col.width.xl = colData.width.xl || colData.width;
                                        col.width.l = colData.width.l || colData.width;
                                        col.width.m = colData.width.m || colData.width;
                                        col.width.s = colData.width.s || colData.width;
                                        col.blocks = colData.blocks || [];
                                        col.horizontalAlignment = colData.horizontalAlignment;
                                        col.background = colData.background;
                                        col.url = colData.url;
                                        col.pagePath = colData.pagePath;
                                        col.linkType = colData.linkType;
                                        col.height = colData.height;
                                        return col;
                                    });
                                }

                                return row;
                            });
                        }
                        return section;
                    });
                }
            } catch (e) {
                console.error("Erreur lors du chargement du contenu initial :", e);
            }
        }

        this.managers[this.currentLocale] = this.sectionsManager;

        this.canvasRenderer = new CanvasRenderer(this);
        this.deviceManager = new DeviceManager(this);
        this.inspector = new InspectorView(this, this.sectionsManager);
        this.tabs = new TabsView(this);

        this.deviceManager.init();

        this.isEditingName = false;

        // Synchroniser l'affichage initial
        if (this.hasTitleFieldTarget && this.hasNameTextTarget) {
            const initialTitle = this.titleFieldTarget.value.trim();
            if (initialTitle) {
                this.nameTextTarget.textContent = initialTitle;
            }
        }

        // Écouter les changements du champ #page_title
        if (this.hasTitleFieldTarget) {
            this.titleFieldTarget.addEventListener('input', (e) => {
                this.syncFromTitleField(e);
            });
        }

        this.showInspectorDefault();
        this.renderCanvas();
    }

    // ========================================
    // SYNCHRONISATION BIDIRECTIONNELLE
    // ========================================

    syncFromTitleField(event) {
        if (this.hasNameTextTarget && !this.isEditingName) {
            this.nameTextTarget.textContent = event.target.value;
        }
    }

    syncToTitleField(newTitle) {
        if (this.hasTitleFieldTarget) {
            this.titleFieldTarget.value = newTitle;
            // Déclencher l'événement input pour que d'autres listeners soient notifiés
            this.titleFieldTarget.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    // ========================================
    // ÉDITION DU NOM DE LA PAGE
    // ========================================

    startEditName() {
        if (this.isEditingName) return;

        this.isEditingName = true;
        const currentName = this.nameTextTarget.textContent.trim();

        this.nameDisplayTarget.classList.add('d-none');
        this.nameInputTarget.classList.remove('d-none');
        this.nameInputTarget.value = currentName;
        this.nameInputTarget.dataset.previousValue = currentName;

        this.nameInputTarget.focus();
        this.nameInputTarget.select();
    }

    saveNameEdit() {
        if (!this.isEditingName) return;

        const newName = this.nameInputTarget.value.trim();
        const oldName = this.nameInputTarget.dataset.previousValue || '';

        if (newName && newName !== oldName) {
            // Mettre à jour l'affichage
            this.nameTextTarget.textContent = newName;

            // Synchroniser avec le champ #page_title
            this.syncToTitleField(newName);
        } else if (!newName) {
            // Si vide, restaurer l'ancienne valeur
            this.nameInputTarget.value = oldName;
        }

        this.exitNameEdit();
    }

    handleNameKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            this.nameInputTarget.blur();
        } else if (event.key === 'Escape') {
            event.preventDefault();
            this.cancelNameEdit();
        }
    }

    cancelNameEdit() {
        this.nameInputTarget.value = this.nameInputTarget.dataset.previousValue || '';
        this.exitNameEdit();
    }

    exitNameEdit() {
        this.isEditingName = false;
        this.nameInputTarget.classList.add('d-none');
        this.nameDisplayTarget.classList.remove('d-none');
    }


    // --- Utilitaires de synchro ---

    /**
     * Vérifie si on doit synchroniser vers les autres langues.
     * Condition : être sur la langue primaire, avoir plusieurs langues configurées,
     * et la case "Synchroniser" doit être cochée.
     */
    shouldSyncToOthers() {
        const isPrimary = this.currentLocale === this.primaryLocale;
        const hasMultipleLocales = this.locales.length > 1;
        const isSyncChecked = this.hasSyncLocaleInputTarget && this.syncLocaleInputTarget.checked;
        return isPrimary && hasMultipleLocales && isSyncChecked;
    }

    /** Action Stimulus branchée sur la checkbox de sync (data-action="change->page-builder#toggleSync") */
    toggleSync() {
        // Pas d'action directe — la checkbox est lue par shouldSyncToOthers() à chaque opération.
    }

    /**
     * Retourne les locales secondaires (toutes sauf la primaire).
     */
    getSecondaryLocales() {
        return this.locales.slice(1);
    }

    /**
     * Récupère ou initialise le manager pour une locale donnée (pour la synchro).
     * Si le manager n'existe pas encore, il est créé comme une copie profonde de la locale primaire.
     */
    getOrCreateManagerForSync(locale) {
        if (!this.managers[locale]) {
            const newManager = new SectionsManager(this.columnPresets);
            if (this.sectionsManager.sections) {
                const rawSections = JSON.parse(JSON.stringify(this.sectionsManager.sections));
                newManager.sections = rawSections.map(section => {
                    if (section.rows) {
                        section.rows = section.rows.map(rowData => {
                            const row = new Row();
                            row.id = rowData.id;
                            row.align = rowData.align || 'center';
                            row.justify = rowData.justify || 'center';
                            row.reverseMobile = rowData.reverseMobile || false;

                            if (rowData.columns) {
                                row.columns = rowData.columns.map(colData => {
                                    const col = new Column(colData.width);
                                    col.id = colData.id;
                                    col.blocks = colData.blocks || [];
                                    col.horizontalAlignment = colData.horizontalAlignment;
                                    col.background = colData.background;
                                    return col;
                                });
                            }

                            return row;
                        });
                    }
                    return section;
                });
            }
            this.managers[locale] = newManager;
        }
        return this.managers[locale];
    }

    // --- Multilingue ---

    switchLocale(event) {
        let newLocale = null;

        // Cas 1 : Appel direct avec une string (ex: depuis le saver)
        if (typeof event === 'string') {
            newLocale = event;
        }
        // Cas 2 : Appel Stimulus standard avec params (ce qui existait avant)
        else if (event.params && event.params.locale) {
            newLocale = event.params.locale;
        }
        // Cas 3 : Appel Stimulus via dataset (fallback)
        else if (event.currentTarget && event.currentTarget.dataset.locale) {
            newLocale = event.currentTarget.dataset.locale;
        }

        if (!newLocale) return;

        if (newLocale === this.currentLocale) return;

        // 1. Création ou récupération du manager pour la nouvelle langue
        if (!this.managers[newLocale]) {
            const newManager = new SectionsManager(this.columnPresets);

            // LOGIQUE DE COPIE : "Par défaut même structure"
            // On clone les sections de la langue actuelle vers la nouvelle
            // On suppose ici que sectionsManager expose une propriété 'sections' clonable
            if (this.sectionsManager.sections) {
                try {
                    // Deep copy simple pour éviter les références partagées
                    const rawSections = JSON.parse(JSON.stringify(this.sectionsManager.sections));

                    // Réhydrater les instances de Column
                    newManager.sections = rawSections.map(section => {
                        if (section.rows) {
                            section.rows.forEach(row => {
                                if (row.columns) {
                                    row.columns = row.columns.map(colData => {
                                        const col = new Column(colData.width);
                                        col.blocks = colData.blocks || [];
                                        col.horizontalAlignment = colData.horizontalAlignment;
                                        col.background = colData.background;
                                        return col;
                                    });
                                }
                            });
                        }
                        return section;
                    });
                } catch (e) {
                    console.warn('Impossible de cloner la structure pour la nouvelle langue', e);
                }
            }

            this.managers[newLocale] = newManager;
        }

        // 2. Bascule du contexte
        this.currentLocale = newLocale;
        this.sectionsManager = this.managers[newLocale];

        // 3. Mise à jour des composants dépendants du manager
        // L'inspecteur garde une référence au manager, on doit le recréer ou le mettre à jour
        this.inspector = new InspectorView(this, this.sectionsManager);

        // 4. Rafraîchissement de l'interface
        const localeBtn = document.getElementById('btnCurrentLocale');
        if (localeBtn) localeBtn.textContent = newLocale.toUpperCase();
        this.showInspectorDefault(); // Retour à l'inspecteur par défaut pour éviter les conflits d'ID de blocs
        this.renderCanvas();
    }

    // --- Rendu ---

    renderCanvas(onlySelection = false) {
        if (onlySelection) {
            this.canvasRenderer.updateSelection();
        } else {
            this.canvasRenderer.render();
        }
    }

    // --- Tabs Bibliothèque / Inspecteur ---

    switchTab(event) {
        const tabName = event.currentTarget.dataset.tab;
        this.tabs.activate(tabName);
    }

    showInspectorDefault() {
        this.inspector.showDefault();
    }

    // --- Handlers pour le CanvasRenderer ---

    onSectionClick(sectionId) {
        this.sectionsManager.selectSection(sectionId);
        this.tabs.activate('inspector');
        this.showSectionInspector();
        this.renderCanvas(true);
    }

    onRowClick(sectionId, rowId) {
        this.sectionsManager.selectRow(sectionId, rowId);
        this.tabs.activate('inspector');
        this.showSectionInspector();
        this.showRowInspector();
        this.renderCanvas(true);
    }

    onColumnClick(sectionId, rowId, columnId) {
        this.sectionsManager.selectColumn(sectionId, rowId, columnId);
        this.tabs.activate('inspector');
        this.showRowInspector()
        this.showColumnInspector()
        this.renderCanvas(true);
    }

    onBlockClick(sectionId, rowId, columnId, blockId) {
        this.sectionsManager.selectBlock(sectionId, rowId, columnId, blockId);
        this.tabs.activate('inspector');
        this.showBlockInspector();
        this.renderCanvas(true);
    }

    // --- Actions liées aux boutons / inputs Stimulus ---

    addSection() {
        this.sectionsManager.addSection();
        this.tabs.activate('inspector');
        this.renderCanvas();
        this.showSectionInspector();

        // SYNCHRO
        if (this.shouldSyncToOthers()) {
            const lastSectionFr = this.sectionsManager.sections[this.sectionsManager.sections.length - 1];
            this.getSecondaryLocales().forEach(locale => {
                const existed = !!this.managers[locale];
                const manager = this.getOrCreateManagerForSync(locale);
                if (existed && lastSectionFr) {
                    const sectionCopy = JSON.parse(JSON.stringify(lastSectionFr));
                    if (sectionCopy.rows) {
                        sectionCopy.rows = sectionCopy.rows.map(rowData => {
                            const row = new Row();
                            row.id = rowData.id;
                            row.align = rowData.align || 'center';
                            row.justify = rowData.justify || 'center';
                            row.reverseMobile = rowData.reverseMobile || false;
                            if (rowData.columns) {
                                row.columns = rowData.columns.map(colData => {
                                    const col = new Column(colData.width);
                                    col.id = colData.id;
                                    col.blocks = colData.blocks || [];
                                    col.horizontalAlignment = colData.horizontalAlignment;
                                    col.background = colData.background;
                                    return col;
                                });
                            }
                            return row;
                        });
                    }
                    manager.sections.push(sectionCopy);
                }
            });
        }
    }

    addBlockFromLibrary(event) {
        const type = event.currentTarget.dataset.blockType;
        if (!type) {
            return;
        }

        // Si c'est un template, créer une nouvelle section
        if (type === "template") {
            const templateType = event.currentTarget.dataset.templateType;
            this.sectionsManager.addSectionFromTemplate(templateType);

            // SYNCHRO TEMPLATE
            if (this.shouldSyncToOthers()) {
                const lastSectionFr = this.sectionsManager.sections[this.sectionsManager.sections.length - 1];
                this.getSecondaryLocales().forEach(locale => {
                    const existed = !!this.managers[locale];
                    const manager = this.getOrCreateManagerForSync(locale);
                    if (existed && lastSectionFr) {
                        const sectionCopy = JSON.parse(JSON.stringify(lastSectionFr));
                        if (sectionCopy.rows) {
                            sectionCopy.rows = sectionCopy.rows.map(rowData => {
                                const row = new Row();
                                row.id = rowData.id;
                                row.align = rowData.align || 'center';
                                row.justify = rowData.justify || 'center';
                                row.reverseMobile = rowData.reverseMobile || false;
                                if (rowData.columns) {
                                    row.columns = rowData.columns.map(colData => {
                                        const col = new Column(colData.width);
                                        col.id = colData.id;
                                        col.blocks = colData.blocks || [];
                                        col.horizontalAlignment = colData.horizontalAlignment;
                                        col.background = colData.background;
                                        return col;
                                    });
                                }
                                return row;
                            });
                        }
                        manager.sections.push(sectionCopy);
                    }
                });
            }

            this.renderCanvas();
            this.tabs.activate('inspector');
            this.showBlockInspector();
            return;
        }

        // Vérifier si on est dans une grille imbriquée
        const selectedBlock = this.sectionsManager.selectedBlock;
        if (selectedBlock && selectedBlock.type === 'nested-row' && selectedBlock.selectedColumnId) {
            // Ajouter le bloc dans la colonne imbriquée
            const nestedCol = selectedBlock.row.findColumn(selectedBlock.selectedColumnId);
            if (nestedCol) {
                const newBlock = this.sectionsManager.blockTypes.createBlock(type);
                nestedCol.addBlock(newBlock);
                selectedBlock.selectedBlockId = newBlock.id;

                this.renderCanvas();
                this.tabs.activate('inspector');
                this.showBlockInspector();
                return;
            }
        }

        // Sinon, comportement normal pour les blocs simples
        let sectionId = this.sectionsManager.selectedSectionId;
        let columnId = this.sectionsManager.selectedColumnId;
        let rowId = this.sectionsManager.selectedRowId;
        let newSection = false;
        let newRow = false;

        if (!sectionId) {
            this.addSection();
            newSection = true;

            sectionId = this.sectionsManager.selectedSectionId;
            columnId = this.sectionsManager.selectedColumnId;
            rowId = this.sectionsManager.selectedRowId;
        }

        const section = this.sectionsManager.sections.find(s => s.id === sectionId);
        if (section) {
            // Dans le cas où la section vient d'être créée, on récupère la première row
            // Sinon, on crée une nouvelle row
            if (!rowId) {
                if (newSection) {
                    const currentRow = section.rows[0];
                    rowId = currentRow.id;
                } else {
                    this.sectionsManager.addRow(section.id);
                    newRow = true;
                    rowId = this.sectionsManager.selectedRowId;
                }
                this.sectionsManager.selectRow(sectionId, rowId);
            }
            // Dans le cas où la section vient d'être créée, on ajoute le block dans sa 1ère colonne
            // Sinon, on crée une nouvelle colonne
            if (!columnId) {
                if (newSection || newRow) {
                    const currentColumn = this.sectionsManager.selectedRow.columns[0];
                    columnId = currentColumn.id;
                } else {
                    const newCol = new Column('1-1');
                    columnId = newCol.id
                    const currentRow = this.sectionsManager.selectedRow;
                    currentRow.addColumn(newCol);
                }
                this.sectionsManager.selectColumn(sectionId, rowId, columnId);
            }
        }

        this.sectionsManager.addBlock(sectionId, rowId, columnId, type);

        // SYNCHRO BLOC
        if (this.shouldSyncToOthers()) {
            this.getSecondaryLocales().forEach(locale => {
                const existed = !!this.managers[locale];
                const manager = this.getOrCreateManagerForSync(locale);
                // On n'ajoute le bloc que si le manager existait déjà.
                // Sinon, le clone contient déjà le nouveau bloc.
                if (existed) {
                    const targetSection = manager.sections.find(s => s.id === sectionId);
                    if (targetSection) {
                        manager.addBlock(sectionId, rowId, columnId, type);
                    }
                }
            });
        }

        this.renderCanvas();

        this.tabs.activate('inspector');
        this.showSectionInspector();
        this.showBlockInspector();
    }

    updateBlockContent(event) {
        // Vérifier si on est dans une grille imbriquée
        const selectedBlock = this.sectionsManager.selectedBlock;
        if (selectedBlock && selectedBlock.type === 'nested-row' && selectedBlock.selectedBlockId) {
            const nestedCol = selectedBlock.row.findColumn(selectedBlock.selectedColumnId);
            if (nestedCol) {
                const nestedBlock = nestedCol.blocks.find(b => b.id === selectedBlock.selectedBlockId);
                if (nestedBlock) {
                    const blockType = this.sectionsManager.blockTypes.types[nestedBlock.type];
                    if (blockType && blockType.handleInspectorInput) {
                        blockType.handleInspectorInput(nestedBlock, event);
                    }
                }
            }
            this.renderCanvas();
            return;
        }

        // délégation à l'inspector / registry
        this.inspector.handleBlockInspectorInput(event);
        this.renderCanvas();
    }

    // --- Inspecteur : Section / Block ---

    showSectionInspector(shouldActivateTab = true) {
        const section = this.sectionsManager.selectedSection;
        this.inspector.showSection(section);
        if (shouldActivateTab) {
            this.activateTab('nav-structure');
        }
    }

    showRowInspector() {
        const row = this.sectionsManager.selectedRow;
        this.inspector.showRow(row);
    }

    showColumnInspector() {
        const column = this.sectionsManager.selectedColumn;
        this.inspector.showColumn(column);
        this.activateTab('nav-structure');
    }

    showBlockInspector() {
        const block = this.sectionsManager.selectedBlock;
        if (!block) {
            this.inspector.showSection(this.sectionsManager.selectedSection);
            return;
        }

        // Si c'est un conteneur (nested-row ou icon-box) avec un bloc imbriqué sélectionné
        if ((block.type === 'nested-row') && block.selectedBlockId && block.selectedColumnId) {
            const nestedCol = block.row.findColumn(block.selectedColumnId);
            if (nestedCol) {
                const nestedBlock = nestedCol.blocks.find(b => b.id === block.selectedBlockId);
                if (nestedBlock) {
                    this.renderNestedBlockInspector(nestedBlock);
                    return;
                }
            }
        }

        // Sinon, afficher l'inspecteur du bloc normal
        this.inspector.showBlock(block);
    }

    // Nouvelle méthode pour gérer l'inspecteur d'un bloc imbriqué
    renderNestedBlockInspector(nestedBlock) {
        // Activer le mode inspecteur
        this.inspectorPanelDefaultTarget.classList.add('d-none');

        const container = this.element.querySelector('[data-page-builder-target="inspectorAccordionContainer"]');
        if (container) {
            container.classList.remove('d-none');
        }

        // Masquer Section, Row, Column - Afficher seulement Block
        const sectionPanel = this.element.querySelector('[data-page-builder-target="inspectorPanelSection"]');
        const rowPanel = this.element.querySelector('[data-page-builder-target="inspectorPanelRow"]');
        const columnPanel = this.element.querySelector('[data-page-builder-target="inspectorPanelColumn"]');
        const blockPanel = this.element.querySelector('[data-page-builder-target="inspectorPanelBlock"]');

        if (sectionPanel) sectionPanel.classList.add('d-none');
        if (rowPanel) rowPanel.classList.add('d-none');
        if (columnPanel) columnPanel.classList.add('d-none');
        if (blockPanel) blockPanel.classList.remove('d-none');

        // Ouvrir l'accordéon du bloc
        const collapseBlock = document.getElementById('collapseBlock');
        if (collapseBlock) {
            collapseBlock.classList.add('show');
            const button = document.querySelector('[data-bs-target="#collapseBlock"]');
            if (button) {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            }
        }

        // Rendre l'inspecteur du bloc imbriqué
        this.sectionsManager.blockTypes.renderInspector(nestedBlock, this);
    }
    updateSectionLayout(event) {
        this.sectionsManager.updateSectionLayout(event.target.value);
        this.renderCanvas();
        this.showSectionInspector();
    }

    updateSectionName(event) {
        this.sectionsManager.updateSectionName(event.target.value);
        const section = this.sectionsManager.selectedSection;
        if (section) {
            this.inspector.showSection(section);
        }
        this.renderCanvas();
    }

    updateSectionVisibility() {
        this.sectionsManager.updateSectionVisibility({
            desktop: this.sectionVisibleDesktopInputTarget.checked,
            mobile: this.sectionVisibleMobileInputTarget.checked,
        });
        this.renderCanvas();
        this.showSectionInspector();
    }


    updateRowColumnsAlignment(event) {
        this.sectionsManager.updateRowAlignment(event.target.value);
        this.renderCanvas();
        this.showRowInspector();
    }

    updateRowColumnsJustify(event) {
        this.sectionsManager.updateRowJustify(event.target.value);
        this.renderCanvas();
        this.showRowInspector();
    }

    updateRowResponsive(event) {
        this.sectionsManager.updateRowResponsive({
            reverseMobile: this.rowReverseMobileInputTarget.checked,
        });
        this.renderCanvas();
        this.showRowInspector();
    }

    updateRowMode(event) {
        const mode = this.sectionsManager.updateRowMode(event.target.value);

        // Afficher/masquer les sections appropriées
        this.toggleRowModeParams(mode);

        this.renderCanvas();
        this.showRowInspector();
    }

    // Méthode pour afficher/masquer les paramètres selon le mode
    toggleRowModeParams(mode) {
        if (!this.hasRowFixedParamsTarget || !this.hasRowAutoParamsTarget) {
            return;
        }

        const fixedParams = this.rowFixedParamsTarget;
        const autoParams = this.rowAutoParamsTarget;

        if (mode === 'fixed') {
            fixedParams.classList.remove('d-none');
            autoParams.classList.add('d-none');
        } else if (mode === 'auto') {
            fixedParams.classList.add('d-none');
            autoParams.classList.remove('d-none');
        }
    }

    updateRowType(event) {
        this.sectionsManager.updateRowType(event.target.value);
        this.renderCanvas();
        this.showRowInspector();
    }

    updateRowImgWidth(event) {
        this.sectionsManager.updateRowImgWidth(event.target.value);
        this.renderCanvas();
        this.showRowInspector();
    }

    changeRowSliderBreakpoint(event) {
        const bp = event.currentTarget.dataset.breakpoint;
        const value = (bp === 'none') ? null : bp; // null | 's' | 'm' | 'l' | 'xl'

        // Mettre à jour le modèle
        this.sectionsManager.updateRowSlider(value);

        // Mettre à jour l'état actif des boutons (sélection en cascade vers le bas)
        const order = ['s', 'm', 'l', 'xl'];
        const container = event.currentTarget.closest('.pb-breakpoint-slider-selector');
        if (container) {
            const buttons = container.querySelectorAll('.pb-breakpoint-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            if (value) {
                const maxIndex = order.indexOf(value);
                buttons.forEach(btn => {
                    const b = btn.dataset.breakpoint;
                    if (order.indexOf(b) !== -1 && order.indexOf(b) <= maxIndex) {
                        btn.classList.add('active');
                    }
                });
            } else {
                buttons[0].classList.add('active');
            }
        }

        this.renderCanvas();
        this.showRowInspector();
    }

    updateColumnUrl(event) {
        this.sectionsManager.updateColumnUrl(event.target.value);
        this.renderCanvas(true);
    }

    updateBlockUrl(event) {
        const block = this.sectionsManager.selectedBlock;
        if (block) {
            block.url = event.target.value;
            this.renderCanvas(true);
        }
    }

    updateBlockLinkType(event) {
        const type = event.target.value;
        const block = this.sectionsManager.selectedBlock;

        if (block) {
            block.linkType = type;

            // Masquer tous les conteneurs
            if (this.hasBlockUrlInputContainerTarget) {
                this.blockUrlInputContainerTarget.classList.add('d-none');
            }
            if (this.hasBlockPagePathSelectContainerTarget) {
                this.blockPagePathSelectContainerTarget.classList.add('d-none');
            }

            // Afficher l'input correspondant
            if (type === 'url' && this.hasBlockUrlInputContainerTarget) {
                this.blockUrlInputContainerTarget.classList.remove('d-none');
            } else if (type === 'page' && this.hasBlockPagePathSelectContainerTarget) {
                this.blockPagePathSelectContainerTarget.classList.remove('d-none');
            }
        }
    }

    updateBlockPagePath(event) {
        const block = this.sectionsManager.selectedBlock;
        if (block) {
            block.pagePath = event.target.value;
            this.renderCanvas(true);
        }
    }

    updateColumnLinkType(event) {
        const type = event.target.value;

        // Masquer tous les conteneurs
        if (this.hasColumnUrlInputContainerTarget) {
            this.columnUrlInputContainerTarget.classList.add('d-none');
        }
        if (this.hasColumnPagePathSelectContainerTarget) {
            this.columnPagePathSelectContainerTarget.classList.add('d-none');
        }

        // Afficher l'input correspondant
        if (type === 'url' && this.hasColumnUrlInputContainerTarget) {
            this.columnUrlInputContainerTarget.classList.remove('d-none');
        } else if (type === 'page' && this.hasColumnPagePathSelectContainerTarget) {
            this.columnPagePathSelectContainerTarget.classList.remove('d-none');
        }

        // On pourrait aussi vouloir sauvegarder le type dans le modèle si on veut qu'il persiste à la réouverture de l'inspecteur
        if (this.sectionsManager.selectedColumn) {
            this.sectionsManager.selectedColumn.linkType = type;
        }
    }

    updateColumnPagePath(event) {
        this.sectionsManager.updateColumnPagePath(event.target.value);
        this.renderCanvas(true);
    }

    updateColumnHeight(event) {
        this.sectionsManager.updateColumnHeight(event.target.value);
        this.renderCanvas(true);
    }

    updateBlockHorizontalAlignment(event) {
        // Retirer la classe active de tous les boutons
        const alignmentButtons = document.querySelectorAll('[data-page-builder-target="blockAlignmentButton"]');
        alignmentButtons.forEach(btn => btn.classList.remove('active'));

        // Ajouter la classe active au bouton cliqué
        event.currentTarget.classList.add('active');

        this.sectionsManager.updateBlockHorizontalAlignment(event.currentTarget.dataset.alignment);
        this.renderCanvas();
        this.showBlockInspector();
    }

    updateColumnBackgroundType(event) {
        const type = event.target.value;

        // Masquer tous les inputs
        if (this.hasColumnBackgroundColorInputTarget) {
            this.columnBackgroundColorInputTarget.classList.add('d-none');
        }
        if (this.hasColumnBackgroundImageInputTarget) {
            this.columnBackgroundImageInputTarget.classList.add('d-none');
        }
        if (this.hasColumnBackgroundClassInputTarget) {
            this.columnBackgroundClassInputTarget.classList.add('d-none');
        }

        // Afficher l'input correspondant
        if (type === 'color' && this.hasColumnBackgroundColorInputTarget) {
            this.columnBackgroundColorInputTarget.classList.remove('d-none');
        } else if (type === 'image' && this.hasColumnBackgroundImageInputTarget) {
            this.columnBackgroundImageInputTarget.classList.remove('d-none');

            const sectionImage = document.getElementById('section-image');
            if (sectionImage) {
                // Cloner le contenu pour le déplacer dans l'inspector
                const imageContent = sectionImage.cloneNode(true);
                imageContent.classList.remove('d-none');
                imageContent.removeAttribute('id'); // Éviter les doublons d'ID

                // Trouver le conteneur dans columnBackgroundImageInput et y ajouter le widget
                const containerTarget = this.columnBackgroundImageInputTarget.querySelector('.pb-column-background-image-content');
                if (containerTarget) {
                    containerTarget.innerHTML = '';
                    containerTarget.appendChild(imageContent);

                    // Récupérer la colonne sélectionnée
                    const column = this.sectionsManager.selectedColumn;

                    // Si la colonne a déjà une image en background, la pré-remplir
                    if (column && column.background && column.background.type === 'image' && column.background.value) {
                        const preview = imageContent.querySelector('.im-manager .preview');
                        if (preview) {
                            const existingInput = preview.querySelector('input[type="hidden"]');
                            preview.innerHTML = `<img src="${column.background.value}" alt="" />`;
                            if (existingInput && column.background.imageId) {
                                existingInput.value = column.background.imageId;
                                preview.appendChild(existingInput);
                            } else if (column.background.imageId) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.value = column.background.imageId;
                                preview.appendChild(input);
                            }
                            preview.removeAttribute('data-new');
                        }
                    }

                    // Initialiser l'image manager
                    this.initializeColumnBackgroundImageManager(column);
                }
            }
        } else if (type === 'class' && this.hasColumnBackgroundClassInputTarget) {
            this.columnBackgroundClassInputTarget.classList.remove('d-none');
        }

        // Si on sélectionne "Aucun", réinitialiser le background
        if (!type) {
            this.sectionsManager.updateColumnBackground(null, null);
            this.renderCanvas();
        }
    }

    initializeColumnBackgroundImageManager(column) {
        // (Ré)initialise les widgets .im-manager de la page
        document.querySelectorAll('.im-manager').forEach((el) => {
            if (el.dataset.imManagerLoaded !== true) {
                initImageManager(el);
                el.dataset.imManagerLoaded = true;
            }
        });

        // Initialise la bibliothèque (modale + uploader)
        if (!window.imLibrary) {
            const imLibrary = new IM_Library();
            window.imLibrary = imLibrary;
        } else {
            window.imLibrary.modal.init(); // rebind la modale
        }

        // Observer les changements dans le widget
        const targetNode = document.querySelector('.pb-column-background-image-content .im-manager .preview');

        if (!targetNode) return;

        const config = { attributes: true, childList: true, subtree: true };

        const callback = (mutationList, observer) => {
            for (const mutation of mutationList) {
                if (mutation.type === "childList") {
                    const imgElement = mutation.target.querySelector('img');
                    if (imgElement) {
                        const imgSrc = imgElement.getAttribute('src');

                        // Remplacer admin_thumbnail par admin_preview pour avoir l'image originale
                        const previewSrc = imgSrc.replace('/media/cache/admin_thumbnail/', '/media/cache/resolve/admin_preview/');

                        // Récupérer l'ID de l'image depuis l'input caché
                        const inputId = document.querySelector('.pb-column-background-image-content .im-manager input[type="hidden"]');
                        let imageId = null;
                        if (inputId) {
                            imageId = inputId.value;
                        }

                        // Mettre à jour le background de la colonne
                        this.sectionsManager.updateColumnBackground('image', previewSrc, imageId);

                        // Re-render le canvas
                        this.renderCanvas();
                    }
                }
            }
        };

        const observer = new MutationObserver(callback);
        observer.observe(targetNode, config);
    }

    updateColumnBackgroundValue(event) {
        const column = this.sectionsManager.selectedColumn;
        if (!column) return;

        const type = this.columnBackgroundTypeSelectTarget.value;
        const value = event.target.value;

        this.sectionsManager.updateColumnBackground(type, value);
        this.renderCanvas();
    }

    updateColumnHorizontalAlignment(event) {
        const column = this.sectionsManager.selectedColumn;
        if (!column) return;

        const target = event.currentTarget;
        const value = target.dataset.alignment;

        // Retirer la classe active de tous les boutons
        const alignmentButtons = document.querySelectorAll('[data-page-builder-target="columnAlignmentButton"]');
        alignmentButtons.forEach(btn => btn.classList.remove('active'));

        target.classList.add('active');

        this.sectionsManager.updateColumnHorizontalAlignment(value);
        this.renderCanvas();
    }

    changeColumnBreakpoint(event) {
        const breakpoint = event.currentTarget.dataset.breakpoint;
        this.inspector.setBreakpoint(breakpoint);
    }

    updateSectionBackgroundType(event) {
        const type = event.target.value;

        // Masquer tous les inputs
        if (this.hasSectionBackgroundColorInputTarget) {
            this.sectionBackgroundColorInputTarget.classList.add('d-none');
        }
        if (this.hasSectionBackgroundImageInputTarget) {
            this.sectionBackgroundImageInputTarget.classList.add('d-none');
        }
        if (this.hasSectionBackgroundClassInputTarget) {
            this.sectionBackgroundClassInputTarget.classList.add('d-none');
        }

        // Afficher l'input correspondant
        if (type === 'color' && this.hasColumnBackgroundColorInputTarget) {
            this.sectionBackgroundColorInputTarget.classList.remove('d-none');
        } else if (type === 'image' && this.hasColumnBackgroundImageInputTarget) {
            this.sectionBackgroundImageInputTarget.classList.remove('d-none');

            const sectionImage = document.getElementById('section-image');
            if (sectionImage) {
                // Cloner le contenu pour le déplacer dans l'inspector
                const imageContent = sectionImage.cloneNode(true);
                imageContent.classList.remove('d-none');
                imageContent.removeAttribute('id'); // Éviter les doublons d'ID

                // Trouver le conteneur dans columnBackgroundImageInput et y ajouter le widget
                const containerTarget = this.sectionBackgroundImageInputTarget.querySelector('.pb-section-background-image-content');
                if (containerTarget) {
                    containerTarget.innerHTML = '';
                    containerTarget.appendChild(imageContent);

                    // Récupérer la colonne sélectionnée
                    const section = this.sectionsManager.selectedSection;

                    // Si la colonne a déjà une image en background, la pré-remplir
                    if (section && section.background && section.background.type === 'image' && section.background.value) {
                        const preview = imageContent.querySelector('.im-manager .preview');
                        if (preview) {
                            const existingInput = preview.querySelector('input[type="hidden"]');
                            preview.innerHTML = `<img src="${section.background.value}" alt="" />`;
                            if (existingInput && section.background.imageId) {
                                existingInput.value = section.background.imageId;
                                preview.appendChild(existingInput);
                            } else if (section.background.imageId) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.value = section.background.imageId;
                                preview.appendChild(input);
                            }
                            preview.removeAttribute('data-new');
                        }
                    }

                    // Initialiser l'image manager
                    this.initializeSectionBackgroundImageManager(section);
                }
            }
        } else if (type === 'class' && this.hasSectionBackgroundClassInputTarget) {
            this.sectionBackgroundClassInputTarget.classList.remove('d-none');
        }

        // Si on sélectionne "Aucun", réinitialiser le background
        if (!type) {
            this.sectionsManager.updateSectionBackground(null, null);
            this.renderCanvas();
        }
    }

    updateSectionBackgroundValue(event) {
        const section = this.sectionsManager.selectedSection;
        if (!section) return;

        const type = this.sectionBackgroundTypeTarget.value;
        const value = event.target.value;

        this.sectionsManager.updateSectionBackground(type, value);
        this.renderCanvas();
    }

    // ... existing code ...

    setNestedRowColumnsCount(event) {
        const count = parseInt(event.currentTarget.dataset.columns, 10);
        if (!count || count < 1 || count > 4) return;

        const block = this.sectionsManager.selectedBlock;
        if (!block || block.type !== 'nested-row') return;

        const presets = this.columnPresets[count] || [];
        const preset = presets.length ? presets[0] : Array.from({ length: count }, () => 100 / count);

        // Recréer les colonnes avec le nouveau preset
        const newColumns = preset.map(width => {
            const col = new Column(width);
            return col;
        });

        block.row.columns = newColumns;
        block.selectedColumnId = null;
        block.selectedBlockId = null;

        this.renderCanvas();
        this.showBlockInspector();
    }

    updateNestedRowAlignment(event) {
        const block = this.sectionsManager.selectedBlock;
        if (!block || block.type !== 'nested-row') return;

        block.row.align = event.target.value;
        block.row.justify = event.target.value;
        this.renderCanvas(true);
    }

    updateNestedColumnAlignment(event) {
        const block = this.sectionsManager.selectedBlock;
        if (!block || block.type !== 'nested-row') return;
        if (!block.selectedColumnId) return;

        const col = block.row.findColumn(block.selectedColumnId);
        if (!col) return;

        const alignmentButtons = document.querySelectorAll('[data-action="click->page-builder#updateNestedColumnAlignment"]');
        alignmentButtons.forEach(btn => btn.classList.remove('btn-primary', 'btn-outline-secondary'));
        alignmentButtons.forEach(btn => btn.classList.add('btn-outline-secondary'));
        event.currentTarget.classList.remove('btn-outline-secondary');
        event.currentTarget.classList.add('btn-primary');

        col.horizontalAlignment = event.currentTarget.dataset.alignment;
        this.renderCanvas();
        this.showBlockInspector();
    }

    initializeSectionBackgroundImageManager(section) {
        // (Ré)initialise les widgets .im-manager de la page
        document.querySelectorAll('.im-manager').forEach((el) => {
            if (el.dataset.imManagerLoaded !== true) {
                initImageManager(el);
                el.dataset.imManagerLoaded = true;
            }
        });

        // Initialise la bibliothèque (modale + uploader)
        if (!window.imLibrary) {
            window.imLibrary = new IM_Library();
        } else {
            window.imLibrary.modal.init(); // rebind la modale
        }

        // Observer les changements dans le widget
        const targetNode = document.querySelector('.pb-section-background-image-content .im-manager .preview');

        if (!targetNode) return;

        const config = { attributes: true, childList: true, subtree: true };

        const callback = (mutationList, observer) => {
            for (const mutation of mutationList) {
                if (mutation.type === "childList") {
                    const imgElement = mutation.target.querySelector('img');
                    if (imgElement) {
                        const imgSrc = imgElement.getAttribute('src');

                        // Remplacer admin_thumbnail par admin_preview pour avoir l'image originale
                        const previewSrc = imgSrc.replace('/media/cache/admin_thumbnail/', '/media/cache/resolve/admin_preview/');

                        // Récupérer l'ID de l'image depuis l'input caché
                        const inputId = document.querySelector('.pb-section-background-image-content .im-manager input[type="hidden"]');
                        let imageId = null;
                        if (inputId) {
                            imageId = inputId.value;
                        }

                        // Mettre à jour le background de la colonne
                        this.sectionsManager.updateSectionBackground('image', previewSrc, imageId);

                        // Re-render le canvas
                        this.renderCanvas();
                    }
                }
            }
        };

        const observer = new MutationObserver(callback);
        observer.observe(targetNode, config);
    }

    activateTab(tabId) {
        const tabTrigger = document.querySelector(`#${tabId}-tab`);
        if (tabTrigger) {
            // Sauvegarder la position du scroll
            const scrollX = window.scrollX;
            const scrollY = window.scrollY;

            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();

            // Restaurer la position du scroll
            window.scrollTo(scrollX, scrollY);
        }
    }

    // --- Device ---

    switchDevice(event) {
        const device = event.currentTarget.dataset.device;
        this.deviceManager.switchTo(device);
        this.renderCanvas();
        let wrapper = document.querySelector('.pb-canvas-wrapper');
        if (device === 'mobile' && !wrapper.classList.contains( 'pb-canvas-wrapper--mobile' )) {
            wrapper.classList.add('pb-canvas-wrapper--mobile');
        } else if (device === 'desktop') {
            wrapper.classList.remove('pb-canvas-wrapper--mobile');
        }
    }
}
