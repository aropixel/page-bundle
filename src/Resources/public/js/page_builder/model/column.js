// application/assets/controllers/page_builder/model/column.js

export class Column {
    constructor(width) {
        this.id = `col-${Date.now()}-${Math.random().toString(16).slice(2)}`;
        // Si width est déjà un objet (lors du clonage), on le copie
        // Sinon, on crée un nouvel objet avec la même valeur pour tous les breakpoints
        if (typeof width === 'object' && width !== null) {
            this.width = { ...width }; // Copie superficielle de l'objet
        } else {
            this.width = {
                'xl': width,
                'l': width,
                'm': '1-1',
                's': '1-1',
            };
        }
        this.blocks = [];
        this.url = null;
        this.pagePath = null;
        this.linkType = null;
        this.height = 'auto';
        this.horizontalAlignment = 'left';
        this.background = {
            type: null, // 'color', 'image', 'class'
            value: null
        };
    }

    addBlock(block) {
        this.blocks.push(block);
        return block;
    }

    findBlock(blockId) {
        return this.blocks.find((b) => b.id === blockId) || null;
    }

    clone() {
        const cloned = new Column(this.width);
        cloned.url = this.url;
        cloned.pagePath = this.pagePath;
        cloned.linkType = this.linkType;
        cloned.height = this.height;
        cloned.horizontalAlignment = this.horizontalAlignment;
        cloned.background = JSON.parse(JSON.stringify(this.background));
        cloned.blocks = this.blocks.map(block => {
            const clonedBlock = JSON.parse(JSON.stringify(block));
            clonedBlock.id = `block-${Date.now()}-${Math.random().toString(16).slice(2)}`;
            return clonedBlock;
        });
        return cloned;
    }
}
