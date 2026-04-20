import { t } from '../page_builder/i18n.js';

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
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };

    toast.innerHTML = `
        <div class="toast-icon">
            ${icons[type] || icons.info}
        </div>
        <div class="toast-content">
            ${escapeHtml(message)}
        </div>
        <button type="button" class="toast-close" aria-label="${t('page.builder.notification.close')}">
            <i class="fas fa-times"></i>
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
