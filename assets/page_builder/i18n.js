/**
 * Returns the translated string for the given key.
 * Falls back to the key itself if no translation is found.
 * Translations are loaded from `page_builder_config.translations` by the
 * page-builder Stimulus controller and stored in `window.__pbTranslations`.
 *
 * @param {string} key
 * @returns {string}
 */
export function t(key) {
    return (window.__pbTranslations && window.__pbTranslations[key]) || key;
}
