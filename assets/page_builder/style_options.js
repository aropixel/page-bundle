import { t } from './i18n.js';

/**
 * Project-defined style lists (`title_styles`, `button_colors`).
 *
 * The bundle ships no style of its own: a style is a CSS class that only the host application's
 * stylesheet can define, so hardcoding a list here would offer authors options that render as
 * nothing. Each list is declared under `aropixel_page.page_builder` and reaches the browser through
 * the page-builder Stimulus controller.
 *
 * An empty list means the corresponding selector is not displayed at all — see `renderStyleSelect`.
 */

/**
 * @param {object} ctx     The page-builder Stimulus controller.
 * @param {string} key     Configuration key, e.g. 'title_styles'.
 * @returns {Array<{value: string, label: string}>}
 */
export function getStyleOptions(ctx, key) {
    const options = ctx?.pageBuilderConfig?.[key];

    return Array.isArray(options) ? options : [];
}

/**
 * Builds a labelled `<select>` for a style list, or an empty string when the project declared none.
 *
 * @param {object} params
 * @param {Array<{value: string, label: string}>} params.options
 * @param {string} params.selected  Currently selected value.
 * @param {string} params.id        Field id, also used as the label's `for`.
 * @param {string} params.label     Field label.
 * @param {string} params.target    Stimulus target name of the `<select>`.
 * @returns {string} HTML, empty when there is nothing to choose from.
 */
export function renderStyleSelect({options, selected, id, label, target}) {
    if (options.length === 0) {
        return '';
    }

    // A saved value whose style has since left the configuration is kept and flagged, never
    // silently swapped: opening the inspector must not rewrite a page's content.
    const known = options.some(option => option.value === selected);
    const orphan = selected && !known
        ? [{value: selected, label: t('page.builder.style.orphan').replace('{style}', selected)}]
        : [];

    const choices = [...orphan, ...options]
        .map(option => `<option value="${escapeAttribute(option.value)}"${option.value === selected ? ' selected' : ''}>${escapeText(option.label)}</option>`)
        .join('');

    return `
                <div class="mb-2">
                    <label class="form-label pb-label" for="${id}">${escapeText(label)}</label>
                    <select
                        class="form-select form-select-sm"
                        id="${id}" name="${id}"
                        data-page-builder-target="${target}"
                        data-action="change->page-builder#updateBlockContent"
                    >${choices}</select>
                </div>
            `;
}

function escapeAttribute(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
}

function escapeText(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
