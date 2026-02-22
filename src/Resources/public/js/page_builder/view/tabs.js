
export class TabsView {
    constructor(controllerContext) {
        this.ctx = controllerContext; // controller Stimulus
    }

    activate(tabName) {
        // Boutons
        this.ctx.tabButtonTargets.forEach((btn) => {
            const isActive = btn.dataset.tab === tabName;
            btn.classList.toggle('pb-tab--active', isActive);
        });

        // Panneaux
        this.ctx.panelTargets.forEach((panel) => {
            const isActive = panel.dataset.panel === tabName;
            panel.classList.toggle('pb-panel--active', isActive);
        });
    }
}
