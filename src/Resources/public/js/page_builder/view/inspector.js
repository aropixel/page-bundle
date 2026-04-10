export class InspectorView {
    constructor(controllerContext, sectionsManager) {
        this.ctx = controllerContext;      // controller Stimulus
        this.sectionsManager = sectionsManager;
        this.currentBreakpoint = 'xl';

        // Écouteurs d'événements Bootstrap pour les accordéons
        // Utilisation de bind(this) pour garder le contexte de la classe
        const accordionContainer = this.ctx.element.querySelector('#inspectorAccordion');
        if (accordionContainer) {
            accordionContainer.addEventListener('shown.bs.collapse', this.#onAccordionShown.bind(this));
            accordionContainer.addEventListener('hidden.bs.collapse', this.#onAccordionHidden.bind(this));
        }
    }

    // --- États globaux ---

    showDefault() {
        if (!this.ctx.hasInspectorPanelDefaultTarget) {
            return;
        }

        // Masquer le container accordéon
        const accordionContainer = this.ctx.element.querySelector('[data-page-builder-target="inspectorAccordionContainer"]');
        if (accordionContainer) {
            accordionContainer.classList.add('d-none');
        }

        this.ctx.inspectorPanelDefaultTarget.classList.remove('d-none');
        if (this.ctx.hasInspectorPanelPageTarget) {
            this.ctx.inspectorPanelPageTarget.classList.add('d-none');
        }
    }

    showPage() {
        this.ctx.inspectorPanelDefaultTarget.classList.add('d-none');
        this.ctx.inspectorPanelPageTarget.classList.remove('d-none');

        const accordionContainer = this.ctx.element.querySelector('[data-page-builder-target="inspectorAccordionContainer"]');
        if (accordionContainer) {
            accordionContainer.classList.add('d-none');
        }
    }

    // --- Section ---

    showSection(section) {
        if (!section) {
            this.showDefault();
            return;
        }

        this.#toggleInspectorMode(true);

        // Visibilité : Uniquement Section
        this.#toggleAccordionItem('inspectorPanelSection', true);
        this.#toggleAccordionItem('inspectorPanelRow', false);
        this.#toggleAccordionItem('inspectorPanelColumn', false);
        this.#toggleAccordionItem('inspectorPanelBlock', false);

        // Ouverture
        this.#setAccordionOpen('collapseSection', true);
        this.#setAccordionOpen('collapseRow', false);
        this.#setAccordionOpen('collapseColumn', false);
        this.#setAccordionOpen('collapseBlock', false);

        this.#updateSectionFields(section);
        this.#updateRowFields(section);
        // Pas besoin d'updateRowFields ici car on est sur la section
    }

    // --- Row ---

    showRow(row) {
        if (!row) {
            this.showDefault();
            return;
        }

        this.#toggleInspectorMode(true);

        // Visibilité : Section + Ligne
        this.#toggleAccordionItem('inspectorPanelSection', true);
        this.#toggleAccordionItem('inspectorPanelRow', true);
        this.#toggleAccordionItem('inspectorPanelColumn', false);
        this.#toggleAccordionItem('inspectorPanelBlock', false);

        // Ouverture : Ligne
        this.#setAccordionOpen('collapseSection', false);
        this.#setAccordionOpen('collapseRow', true);
        this.#setAccordionOpen('collapseColumn', false);
        this.#setAccordionOpen('collapseBlock', false);

        // Récupérer la section parente
        const section = this.sectionsManager.selectedSection;
        if (section) {
            this.#updateSectionFields(section);
            this.#updateRowFields(section);
        }
    }


    // --- Column ---

    showColumn(column) {
        if (!column) {
            this.showSection(this.sectionsManager.selectedSection);
            return;
        }

        this.#toggleInspectorMode(true);

        // Visibilité : Tout visible
        this.#toggleAccordionItem('inspectorPanelSection', true);
        this.#toggleAccordionItem('inspectorPanelRow', true);
        this.#toggleAccordionItem('inspectorPanelColumn', true);
        this.#toggleAccordionItem('inspectorPanelBlock', false);

        // Ouverture : Bloc
        this.#setAccordionOpen('collapseSection', false);
        this.#setAccordionOpen('collapseRow', false);
        this.#setAccordionOpen('collapseColumn', true);
        this.#setAccordionOpen('collapseBlock', false);

        // Récupérer la section parente
        const section = this.sectionsManager.selectedSection;
        if (section) {
            this.#updateSectionFields(section);
            this.#updateRowFields(section);
            this.#updateColumnFields(section);
        }

    }

    // --- Block ---

    showBlock(block) {
        if (!block) {
            this.showSection(this.sectionsManager.selectedSection);
            return;
        }

        this.#toggleInspectorMode(true);

        // Visibilité : Tout visible
        this.#toggleAccordionItem('inspectorPanelSection', true);
        this.#toggleAccordionItem('inspectorPanelRow', true);
        this.#toggleAccordionItem('inspectorPanelColumn', true);
        this.#toggleAccordionItem('inspectorPanelBlock', true);

        // Ouverture : Bloc
        this.#setAccordionOpen('collapseSection', false);
        this.#setAccordionOpen('collapseRow', false);
        this.#setAccordionOpen('collapseColumn', false);
        this.#setAccordionOpen('collapseBlock', true);

        // Récupérer la section parente
        const section = this.sectionsManager.selectedSection;
        if (section) {
            this.#updateSectionFields(section);
            this.#updateRowFields(section);
        }

        this.sectionsManager.blockTypes.renderInspector(block, this.ctx);
    }

    handleBlockInspectorInput(event) {
        const block = this.sectionsManager.selectedBlock;
        this.sectionsManager.blockTypes.handleInspectorInput(block, event);
    }

    // --- Helpers ---

    #toggleInspectorMode(active) {
        this.ctx.inspectorPanelDefaultTarget.classList.toggle('d-none', active);

        const container = this.ctx.element.querySelector('[data-page-builder-target="inspectorAccordionContainer"]');
        if (container) {
            if (active) container.classList.remove('d-none');
            else container.classList.add('d-none');
        }
    }

    #toggleAccordionItem(targetName, show) {
        const el = this.ctx.element.querySelector(`[data-page-builder-target="${targetName}"]`);
        if (el) {
            el.classList.toggle('d-none', !show);
        }
    }

    #setAccordionOpen(id, isOpen) {
        const collapseEl = document.getElementById(id);
        if (!collapseEl) return;

        const button = document.querySelector(`[data-bs-target="#${id}"]`);

        if (isOpen) {
            collapseEl.classList.add('show');
            if (button) {
                button.classList.remove('collapsed');
                button.setAttribute('aria-expanded', 'true');
            }
        } else {
            collapseEl.classList.remove('show');
            if (button) {
                button.classList.add('collapsed');
                button.setAttribute('aria-expanded', 'false');
            }
        }
    }

    #updateSectionFields(section) {
        if (this.ctx.hasSectionNameInputTarget) {
            this.ctx.sectionNameInputTarget.value = section.name;
        }
        if (this.ctx.hasSectionVisibleDesktopInputTarget) {
            this.ctx.sectionVisibleDesktopInputTarget.checked = section.visibleDesktop;
        }
        if (this.ctx.hasSectionVisibleMobileInputTarget) {
            this.ctx.sectionVisibleMobileInputTarget.checked = section.visibleMobile;
        }
    }

    #updateRowFields(section) {

        this.#updateSelectOptions('#section-width', section.layout);

        // Masquer tous les champs de background d'abord
        if (this.ctx.hasSectionBackgroundClassInputTarget) {
            this.ctx.sectionBackgroundClassInputTarget.classList.add('d-none');
        }
        if (this.ctx.hasSectionBackgroundColorInputTarget) {
            this.ctx.sectionBackgroundColorInputTarget.classList.add('d-none');
        }
        if (this.ctx.hasSectionBackgroundImageInputTarget) {
            this.ctx.sectionBackgroundImageInputTarget.classList.add('d-none');
        }

        if (null !== section.background) {
            const backgroundType = section.background.type;
            this.#updateSelectOptions('#section-background-type', backgroundType);

            // Mapper les types de background avec leurs sélecteurs
            const backgroundInputs = {
                'class': {
                    target: 'sectionBackgroundClassInputTarget',
                    selector: '#section-bg-class',
                    value: section.background.value
                },
                'color': {
                    target: 'sectionBackgroundColorInputTarget',
                    selector: '#section-bg-color',
                    value: section.background.value
                },
                'image': {
                    target: 'sectionBackgroundImageInputTarget',
                    selector: '#section-bg-image',
                    value: section.background.image
                }
            };

            // Si un type de background est défini, afficher et remplir le champ correspondant
            const config = backgroundInputs[backgroundType];
            if (config && this.ctx[`has${config.target.charAt(0).toUpperCase() + config.target.slice(1)}`]) {
                const targetElement = this.ctx[config.target];
                targetElement.classList.remove('d-none');
                // Pour la couleur, mettre à jour directement l'input
                if (backgroundType === 'color') {
                    const colorInput = targetElement.querySelector('input[type="color"]');
                    if (colorInput && config.value) {
                        colorInput.value = config.value;
                    }
                } else {
                    // Pour les autres types (select), utiliser la méthode existante
                    this.#updateSelectOptions(config.selector, config.value);
                }
            }
        } else {
            // Si pas de background, réinitialiser les valeurs
            this.#updateSelectOptions('#section-background-type', '');
            this.#updateSelectOptions('#section-bg-class', '');
            this.#updateSelectOptions('#section-bg-color', '');
            this.#updateSelectOptions('#section-bg-image', '');
        }



        // Mettre à jour le sélecteur d'alignement
        const row = this.sectionsManager.selectedRow || (section.rows && section.rows[0]);
        if (row) {
            const alignmentSelect = document.getElementById('row-align');
            if (alignmentSelect) {
                alignmentSelect.value = row.align || 'center';
            }
            const justifySelect = document.getElementById('row-justify');
            if (justifySelect) {
                justifySelect.value = row.justify || 'center';
            }
            if (this.ctx.hasRowReverseMobileInputTarget) {
                this.ctx.rowReverseMobileInputTarget.checked = row.reverseMobile;
            }
            if (this.ctx.hasRowTypeSelectTarget) {
                this.ctx.rowTypeSelectTarget.value = row.type || 'default';
            }
            if (this.ctx.hasSectionColumnsCountTarget) {
                const colLength = row.columns.length;
                document.querySelectorAll('[data-columns]').forEach(btn => btn.classList.remove('active'));
                const columnBtn = document.querySelector('[data-columns="' + colLength + '"]');
                if (columnBtn) {
                    columnBtn.classList.add('active');
                }
            }
            if (this.ctx.hasRowModeSelectTarget) {
                this.ctx.rowModeSelectTarget.value = row.mode || 'fixed';
            }
            if (this.ctx.hasRowImgWidthInputTarget) {
                this.ctx.rowImgWidthInputTarget.value = row.imgWidth || '60';
            }

            // Mettre à jour le sélecteur du slider par breakpoint
            const sliderContainer = document.querySelector('[data-page-builder-target="rowSliderSelector"]');
            if (sliderContainer) {
                const buttons = sliderContainer.querySelectorAll('.pb-breakpoint-btn');
                buttons.forEach(btn => btn.classList.remove('active'));

                const value = row.slider || null;
                if (value) {
                    const order = ['s', 'm', 'l', 'xl'];
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
        }

        this.#renderSectionColumnsEditor(section);
    }

    // Nouvelle méthode helper
    #updateSelectOptions(selector, value) {
        const selectElement = document.querySelector(selector);
        if (!selectElement) return;

        const options = selectElement.querySelectorAll('option');
        options.forEach((option) => {
            option.selected = option.value === value;
        });
    }

    #updateColumnFields(section) {
        // Mettre à jour le sélecteur d'alignement
        const column = this.sectionsManager.selectedColumn || (section.rows && section.rows[0]);
        if (column) {
            const currentAlignment = column.horizontalAlignment || 'center';

            // Mettre à jour les boutons d'alignement
            const alignmentButtons = document.querySelectorAll('[data-page-builder-target="columnAlignmentButton"]');
            alignmentButtons.forEach(button => {
                if (button.dataset.alignment === currentAlignment) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        if (this.ctx.hasColumnUrlInputTarget) {
            this.ctx.columnUrlInputTarget.value = column.url || '';
        }

        if (this.ctx.hasColumnHeightSelectTarget) {
            this.ctx.columnHeightSelectTarget.value = column.height || '';
        }

        if (this.ctx.hasColumnPagePathSelectTarget) {
            this.ctx.columnPagePathSelectTarget.value = column.pagePath || '';
            const pageSelect = this.ctx.columnPagePathSelectTarget;
            const getPagePathJsonListUrl = JSON.parse(document.getElementById('page-json-list-url').textContent);
            // Charger la liste des pages seulement si elle est vide (pour éviter les doublons au fetch successif)
            if (pageSelect.options.length <= 1) {
                fetch(getPagePathJsonListUrl)
                    .then(r => r.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.slug;
                            option.textContent = item.title;
                            if (column.pagePath === item.slug) {
                                option.selected = true;
                            }
                            pageSelect.appendChild(option);
                        });
                    })
                    .catch(err => {
                        const errOpt = document.createElement('option');
                        errOpt.textContent = "Erreur de chargement";
                        pageSelect.appendChild(errOpt);
                    });
            } else {
                // S'assurer que la bonne option est sélectionnée
                Array.from(pageSelect.options).forEach(option => {
                    option.selected = option.value === (column.pagePath || '');
                });
            }
        }

        // Pré-remplir le type de lien
        if (this.ctx.hasColumnLinkTypeSelectTarget) {
            let linkType = column.linkType;

            // Heuristique si linkType n'est pas défini (ancien contenu)
            if (!linkType) {
                if (column.url) {
                    linkType = 'url';
                } else if (column.pagePath) {
                    linkType = 'page';
                } else {
                    linkType = '';
                }
                column.linkType = linkType;
            }

            this.ctx.columnLinkTypeSelectTarget.value = linkType;

            // Déclencher l'événement change pour afficher le bon input
            const changeEvent = new Event('change');
            this.ctx.columnLinkTypeSelectTarget.dispatchEvent(changeEvent);
        }

        // Pré-remplir le type de background
        if (this.ctx.hasColumnBackgroundTypeSelectTarget) {
            const bgType = column.background?.type || '';
            this.ctx.columnBackgroundTypeSelectTarget.value = bgType;

            // Déclencher l'événement change pour afficher le bon input
            const changeEvent = new Event('change');
            this.ctx.columnBackgroundTypeSelectTarget.dispatchEvent(changeEvent);

            // Pré-remplir la valeur selon le type
            if (bgType === 'color' && this.ctx.hasColumnBackgroundColorInputTarget) {
                const colorInput = this.ctx.columnBackgroundColorInputTarget.querySelector('input[type="color"]');
                if (colorInput && column.background?.value) {
                    colorInput.value = column.background.value;
                }
            } else if (bgType === 'class' && this.ctx.hasColumnBackgroundClassInputTarget) {
                const classInput = this.ctx.columnBackgroundClassInputTarget.querySelector('input[type="text"]');
                if (classInput && column.background?.value) {
                    classInput.value = column.background.value;
                }
            }
        }

        this.#renderSectionColumnsEditor(section);
    }

    // --- Colonnes : presets ---

    #renderSectionColumnsEditor(section) {
        const container = this.ctx.sectionColumnsEditorTarget;
        container.innerHTML = '';

        // Utiliser la row sélectionnée, ou la première row par défaut
        const row = this.sectionsManager.selectedRow || (section.rows && section.rows[0]);
        if (!row) return;

        let presets = this.sectionsManager.columnPresets || [];
        const currentWidths = row.columns.map(col => col[`width.${this.currentBreakpoint}`] || col.width);

        presets.forEach((preset) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.classList.add('pb-button', 'pb-button--ghost', 'flex-fill');
            button.dataset.width = preset;
            button.innerText = preset.substring(2);

            if (this.isPresetActive(preset, this.currentBreakpoint, currentWidths)) {
                button.classList.add('active'); // ou 'pb-button--active', selon votre CSS
            }

            button.addEventListener('click', (e) => {
                this.sectionsManager.setRowColumnsPresetFromString(
                    e.currentTarget.dataset.width || '',
                    this.currentBreakpoint
                );
                if (this.currentBreakpoint === "xl") {
                    this.ctx.renderCanvas();
                }
                container.querySelectorAll('.pb-columns-editor .active').forEach((el) => el.classList.remove('active'));
                button.classList.add('active');
            });

            container.appendChild(button);
        });

    }

    /**
     * Vérifie si un preset correspond aux largeurs actuelles des colonnes
     * @param {string} preset - Le preset à vérifier (ex: '1-6')
     * @param {string} currentBreakpoint - Le breakpoint actuel (ex: 'xl')
     * @param {Array} currentWidths - Tableau des objets width des colonnes
     * @returns {boolean} - true si le preset est actif
     */
    isPresetActive(preset, currentBreakpoint, currentWidths) {
        if (!currentWidths || currentWidths.length === 0) return false;

        // Obtenir la première largeur pour le breakpoint actuel
        const firstColWidth = currentWidths[0][currentBreakpoint];

        // Vérifier que toutes les colonnes ont la même largeur pour ce breakpoint
        const allSameWidth = currentWidths.every(widthObj =>
            widthObj[currentBreakpoint] === firstColWidth
        );

        // Si toutes les colonnes n'ont pas la même largeur, aucun preset n'est actif
        if (!allSameWidth) return false;

        // Comparer le preset avec la largeur actuelle
        return firstColWidth === preset;
    }

    setBreakpoint(breakpoint) {
        this.currentBreakpoint = breakpoint;

        // Update UI buttons
        const buttons = this.ctx.element.querySelectorAll('.pb-breakpoint-selector .pb-breakpoint-btn');
        buttons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.breakpoint === breakpoint);
        });

        // Re-render the editor to show correct active preset
        const section = this.sectionsManager.selectedSection;
        if (section) {
            //this.#updateRowFields(section);
            this.#renderSectionColumnsEditor(section);
        }
    }

    // --- Events Accordion ---

    #onAccordionShown(event) {
        const targetId = event.target.id;

        // Si on ouvre l'accordéon "Section" -> Sélectionner la Section
        if (targetId === 'collapseSection') {
            // On garde la section active mais on désélectionne ligne/bloc
            this.sectionsManager.selectSection(this.sectionsManager.selectedSectionId);
            this.ctx.showSectionInspector();
            this.ctx.renderCanvas();
        }
        // Si on ouvre l'accordéon "Ligne" -> Sélectionner la Ligne
        else if (targetId === 'collapseRow') {
            if (this.sectionsManager.selectedRowId !== null) {
                this.sectionsManager.selectRow(
                    this.sectionsManager.selectedSectionId,
                    this.sectionsManager.selectedRowId  // ← ID au lieu d'index
                );

                // Trouver la bonne row par son ID
                const canvas = this.ctx.canvasTarget;
                const sectionId = this.sectionsManager.selectedSectionId;
                const rowId = this.sectionsManager.selectedRowId;

                // Nettoyer les highlights existants
                canvas.querySelectorAll('.pb-page-section-row--selected').forEach(el => {
                    el.classList.remove('pb-page-section-row--selected');
                });

                canvas.querySelectorAll('.pb-page-column--selected').forEach(el => {
                    el.classList.remove('pb-page-column--selected');
                })

                // Trouver la bonne row par son ID
                const rowEl = canvas.querySelector(`.pb-page-section-row[data-section-id="${sectionId}"][data-row-id="${rowId}"]`);
                if (rowEl) {
                    rowEl.classList.add('pb-page-section-row--selected');
                }

                this.ctx.showRowInspector();
            }
        }
        // Pour le Bloc, on laisse le comportement par défaut (nécessite souvent un clic explicite pour choisir LE bloc)
    }

    #onAccordionHidden(event) {
        // Plus besoin de gérer le highlight manuellement ici, le renderCanvas s'en charge via l'état de sélection
    }

}
