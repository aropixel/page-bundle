import { t } from '../page_builder/i18n.js';
import { icon } from './icons.js';

/**
 * Affiche une notification toast
 * @param {string} message
 * @param {string} type 'success', 'error', 'warning', 'info'
 */
export function showNotification(message, type = 'info') {
    const container = ensureToastContainer();

    const toast = document.createElement('div');
    toast.className = `notification-toast toast-${type}`;

    const icons = {
        success: icon('circle-check', { size: 18 }),
        error: icon('circle-alert', { size: 18 }),
        warning: icon('triangle-alert', { size: 18 }),
        info: icon('info', { size: 18 })
    };

    toast.innerHTML = `
        <div class="toast-icon">
            ${icons[type] || icons.info}
        </div>
        <div class="toast-content">
            ${escapeHtml(message)}
        </div>
        <button type="button" class="toast-close" aria-label="${t('page.builder.notification.close')}">
            ${icon('x', { size: 14 })}
        </button>
    `;

    // Gérer la fermeture
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        removeToast(toast);
    });

    container.appendChild(toast);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        removeToast(toast);
    }, 5000);
}

function ensureToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    return container;
}

function removeToast(toast) {
    if (!toast.classList.contains('toast-exit')) {
        toast.classList.add('toast-exit');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
