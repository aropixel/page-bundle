
import { imageBlockType } from './image.js';
import { titleBlockType } from './title.js';
import { textBlockType } from './text.js';

export const ctaBlockType = {
    type: 'cta',

    create(generateId) {
        const image = imageBlockType.create(generateId);
        const title = titleBlockType.create(generateId);
        const text = textBlockType.create(generateId);

        title.content = 'Titre de la card';
        title.size = 'h3';
        title.horizontalAlignment = 'center';
        text.content = 'Description de la card.';
        text.horizontalAlignment = 'center';
        image.width = 100;
        image.maxWidth = 60;
        image.maxHeight = 60;
        image.useOriginalSize = false;

        // Retourne un tableau de blocs au lieu d'un seul bloc
        return [image, title, text];
    },
};
