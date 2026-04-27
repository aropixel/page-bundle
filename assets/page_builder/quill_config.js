/**
 * Configuration partagée pour l'éditeur Quill utilisé dans le Page Builder.
 */

let initialized = false;

function ensureQuillSetup() {
    if (initialized || typeof Quill === 'undefined') return;
    initialized = true;

    // 1. Autoriser mailto: dans le format Link
    const Link = Quill.import('formats/link');
    class MailtoLink extends Link {
        static sanitize(url) {
            if (url.startsWith('mailto:')) return url;
            return super.sanitize(url);
        }
    }
    Quill.register(MailtoLink, true);

    // 2. Blot personnalisé : <br> (SoftBreak)
    const Embed = Quill.import('blots/embed');
    class SoftBreak extends Embed {
        static create() {
            return document.createElement('br');
        }
        static formats() { return true; }
        optimize(context) {}
        length() { return 1; }
        value() { return '\n'; }
    }
    SoftBreak.blotName = 'softbreak';
    SoftBreak.tagName  = 'BR';
    Quill.register(SoftBreak);

    // 3. Configuration des icônes
    const icons = Quill.import('ui/icons');
    if (!icons['mailto']) {
        icons['mailto'] = `<svg viewBox="0 0 24 24"><rect class="ql-stroke" x="2" y="4" width="20" height="16" rx="2"/><polyline class="ql-stroke" points="2,4 12,13 22,4"/></svg>`;
    }

    // 4. Correction des listes (forcer <ul> pour bullet)
    const List = Quill.import('formats/list');
    class CustomList extends List {
        static create(value) {
            const node = super.create(value);
            if (value === 'bullet') {
                // Quill utilise souvent <ol> avec data-list="bullet" par défaut
                // pour gérer l'indentation de manière uniforme.
                // Note: On ne change pas le tagName ici car Quill s'attend à <ol>
                // pour sa logique interne de liste. La transformation se fait dans cleanHTML.
            }
            return node;
        }
    }
    Quill.register(CustomList, true);
}

/**
 * Nettoie le HTML généré par Quill
 */
export const cleanHTML = (html) => {
    if (!html) return '';

    // Transformer <ol><li data-list="bullet">...</li></ol> en <ul><li>...</li></ul>
    // Quill regroupe les <li> consécutifs dans le même <ol>
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    doc.querySelectorAll('ol').forEach(ol => {
        const firstLi = ol.querySelector('li');
        if (firstLi && firstLi.getAttribute('data-list') === 'bullet') {
            const ul = document.createElement('ul');
            // Copier les classes si nécessaire
            if (ol.className) ul.className = ol.className;

            // Déplacer les enfants et nettoyer l'attribut data-list
            Array.from(ol.childNodes).forEach(child => {
                if (child.tagName === 'LI') {
                    child.removeAttribute('data-list');
                }
                ul.appendChild(child);
            });
            ol.parentNode.replaceChild(ul, ol);
        }
    });

    return doc.body.innerHTML
        .replace(/<br><\/br>/g, '<br>')
        .replace(/<br\s*\/?><\/p>/g, '</p>')
        .replace(/<p><br\s*(?:<\/br>)?\s*<\/p>\s*$/g, '')
        .trim();
};

/**
 * Flag global pour distinguer un collage du chargement initial
 */
let isPasting = false;

export const setPasting = (value) => { isPasting = value; };

/**
 * Retourne la configuration de base de Quill
 */
export const getQuillConfig = (theme = 'bubble', bounds = null) => {
    ensureQuillSetup();

    const Keyboard = Quill.import('modules/keyboard');

    return {
        placeholder: 'Composez votre contenu...',
        theme: theme,
        bounds: bounds,
        modules: {
            clipboard: {
                matchVisual: false,
                matchers: [
                    // Convertit les <br> du HTML source en SoftBreak
                    ['BR', (node, delta) => {
                        const Delta = Quill.import('delta');
                        return new Delta().insert({ softbreak: true });
                    }],
                    // Nettoie le texte collé (ne garde que le texte brut et les sauts de ligne)
                    [Node.ELEMENT_NODE, (node, delta) => {
                        if (!isPasting) return delta;

                        const Delta = Quill.import('delta');
                        const ops = delta.ops.map(op => {
                            if (typeof op.insert === 'string') {
                                // Ne garder que le texte brut, les attributs de liste sont sur les \n
                                return { insert: op.insert };
                            }
                            if (op.attributes && op.attributes.list) {
                                // Préserver les listes si on les insère via un \n (Quill way)
                                return { insert: op.insert, attributes: { list: op.attributes.list } };
                            }
                            if (op.insert?.softbreak) return op;
                            return { insert: '' };
                        });
                        return new Delta(ops);
                    }]
                ]
            },
            keyboard: {
                bindings: {
                    ...Keyboard.DEFAULTS.bindings,
                    // Shift+Entrée → insérer un <br> (SoftBreak) au lieu de \n
                    softBreak: {
                        key: 'Enter',
                        shiftKey: true,
                        handler(range) {
                            this.quill.insertEmbed(range.index, 'softbreak', true, 'user');
                            this.quill.setSelection(range.index + 1, 0, 'silent');
                            return false;
                        }
                    }
                }
            },
            toolbar: {
                container: [
                    ['bold', 'italic', 'underline'],
                    ['link', 'mailto'],
                    [{'list': 'bullet'}, {'list': 'ordered'}],
                    [{'align': 'right'}, {'align': ''}, {'align': 'center'}, {'align': 'justify'}],
                    ['clean']
                ],
                handlers: {
                    mailto: function () {
                        const range = this.quill.getSelection();
                        if (!range || range.length === 0) {
                            alert('Veuillez sélectionner du texte avant d\'ajouter un lien mailto.');
                            return;
                        }

                        const [leaf] = this.quill.getLeaf(range.index);
                        const existing = leaf?.parent?.domNode?.getAttribute('href') || '';
                        const defaultValue = existing.startsWith('mailto:')
                            ? existing.replace('mailto:', '')
                            : '';

                        const email = prompt('Adresse e-mail :', defaultValue);
                        if (email === null) return;

                        const href = email.trim() ? `mailto:${email.trim()}` : '';
                        this.quill.format('link', href || false);
                    },
                },
            }
        },
    };
};
