/**
 * Les icônes dessinées depuis le JavaScript.
 *
 * Les gabarits Twig du bundle passent par `ux_icon('lucide:…')`, mais un panneau construit en JS n'a
 * pas accès à Twig : il posait jusqu'ici des `<i class="fas fa-…">`, c'est-à-dire une dépendance
 * silencieuse à FontAwesome. Une application qui ne le charge pas — et l'administration d'aropixel
 * ne le charge pas — n'affichait rien du tout à ces endroits.
 *
 * Le SVG est donc écrit ici, dans le même jeu que les gabarits (lucide, licence ISC). Seule la
 * géométrie est stockée : les attributs de tracé sont posés une fois sur la racine, et `currentColor`
 * laisse l'icône prendre la couleur de son bouton.
 */

const GEOMETRIES = {
    'align-left': '<path d="M15 12H3m14 6H3M21 6H3"/>',
    'align-center': '<path d="M17 12H7m12 6H5M21 6H3"/>',
    'align-right': '<path d="M21 12H9m12 6H7M21 6H3"/>',
    'circle-alert': '<circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>',
    'circle-check': '<circle cx="12" cy="12" r="10"/><path d="m9 12l2 2l4-4"/>',
    'image': '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
    'images': '<path d="m22 11l-1.296-1.296a2.4 2.4 0 0 0-3.408 0L11 16"/><path d="M4 8a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2"/><circle cx="13" cy="7" r="1" fill="currentColor"/><rect width="14" height="14" x="8" y="2" rx="2"/>',
    'info': '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/>',
    'loader-circle': '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>',
    'plus': '<path d="M5 12h14m-7-7v14"/>',
    'triangle-alert': '<path d="m21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3M12 9v4m0 4h.01"/>',
    'video': '<path d="m16 13l5.223 3.482a.5.5 0 0 0 .777-.416V7.87a.5.5 0 0 0-.752-.432L16 10.5"/><rect width="14" height="12" x="2" y="6" rx="2"/>',
    'x': '<path d="M18 6L6 18M6 6l12 12"/>',
};

/**
 * @param {string} name Un nom de `GEOMETRIES`
 * @param {{size?: number, className?: string}} options
 * @returns {string} Le SVG, prêt à être inséré
 */
export function icon(name, { size = 16, className = '' } = {}) {
    const geometry = GEOMETRIES[name];

    if (!geometry) {
        console.warn(`[page-builder] Icône inconnue : ${name}`);

        return '';
    }

    const classes = ['pb-icon', className].filter(Boolean).join(' ');

    return `<svg class="${classes}" width="${size}" height="${size}" viewBox="0 0 24 24"`
        + ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"'
        + ` stroke-linejoin="round" aria-hidden="true">${geometry}</svg>`;
}
