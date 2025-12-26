/**
 * UIU Supplements - Custom Modal Utility
 * Beautiful modal replacements for browser alert() and confirm()
 */

// Create modal container if not exists
function initModalContainer() {
    if (!document.getElementById('custom-modal-container')) {
        const container = document.createElement('div');
        container.id = 'custom-modal-container';
        document.body.appendChild(container);
    }
}

// Modal icons
const MODAL_ICONS = {
    success: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>',
    warning: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>',
    info: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>',
    confirm: '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>'
};

// Color schemes
const MODAL_COLORS = {
    success: { bg: 'linear-gradient(135deg, #10b981, #059669)', icon: '#10b981' },
    error: { bg: 'linear-gradient(135deg, #ef4444, #dc2626)', icon: '#ef4444' },
    warning: { bg: 'linear-gradient(135deg, #f59e0b, #d97706)', icon: '#f59e0b' },
    info: { bg: 'linear-gradient(135deg, #6366f1, #4f46e5)', icon: '#6366f1' },
    confirm: { bg: 'linear-gradient(135deg, #6366f1, #4f46e5)', icon: '#6366f1' }
};

/**
 * Show alert modal (replacement for alert())
 * @param {string} message - Message to display
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} title - Optional title
 * @returns {Promise} - Resolves when modal is closed
 */
function showAlert(message, type = 'info', title = null) {
    return new Promise((resolve) => {
        initModalContainer();

        const defaultTitles = {
            success: 'Success!',
            error: 'Error!',
            warning: 'Warning!',
            info: 'Information'
        };

        const modalTitle = title || defaultTitles[type] || 'Notice';
        const colors = MODAL_COLORS[type] || MODAL_COLORS.info;
        const icon = MODAL_ICONS[type] || MODAL_ICONS.info;

        const modalId = 'modal-' + Date.now();
        const modalHTML = `
            <div class="custom-modal-overlay" id="${modalId}" onclick="closeModalOnOverlay(event, '${modalId}')">
                <div class="custom-modal custom-modal-${type}" onclick="event.stopPropagation()">
                    <div class="custom-modal-icon" style="color: ${colors.icon}">
                        ${icon}
                    </div>
                    <h3 class="custom-modal-title">${modalTitle}</h3>
                    <p class="custom-modal-message">${message}</p>
                    <div class="custom-modal-buttons">
                        <button class="custom-modal-btn custom-modal-btn-primary" style="background: ${colors.bg}" onclick="closeModal('${modalId}')">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('custom-modal-container').insertAdjacentHTML('beforeend', modalHTML);

        // Animate in
        setTimeout(() => {
            document.getElementById(modalId).classList.add('active');
        }, 10);

        // Store resolve function
        window['modalResolve_' + modalId] = resolve;
    });
}

/**
 * Show confirmation modal (replacement for confirm())
 * @param {string} message - Message to display
 * @param {object} options - Optional settings
 * @returns {Promise<boolean>} - Resolves with true (confirm) or false (cancel)
 */
function showConfirm(message, options = {}) {
    return new Promise((resolve) => {
        initModalContainer();

        const {
            title = 'Confirm Action',
            confirmText = 'Yes, Continue',
            cancelText = 'Cancel',
            type = 'confirm',
            dangerous = false
        } = options;

        const colors = dangerous ? MODAL_COLORS.error : MODAL_COLORS.confirm;
        const icon = dangerous ? MODAL_ICONS.warning : MODAL_ICONS.confirm;

        const modalId = 'modal-' + Date.now();
        const modalHTML = `
            <div class="custom-modal-overlay" id="${modalId}" onclick="closeModalOnOverlay(event, '${modalId}', false)">
                <div class="custom-modal custom-modal-confirm" onclick="event.stopPropagation()">
                    <div class="custom-modal-icon" style="color: ${colors.icon}">
                        ${icon}
                    </div>
                    <h3 class="custom-modal-title">${title}</h3>
                    <p class="custom-modal-message">${message}</p>
                    <div class="custom-modal-buttons">
                        <button class="custom-modal-btn custom-modal-btn-secondary" onclick="resolveConfirm('${modalId}', false)">
                            ${cancelText}
                        </button>
                        <button class="custom-modal-btn custom-modal-btn-primary ${dangerous ? 'custom-modal-btn-danger' : ''}" style="background: ${colors.bg}" onclick="resolveConfirm('${modalId}', true)">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('custom-modal-container').insertAdjacentHTML('beforeend', modalHTML);

        // Animate in
        setTimeout(() => {
            document.getElementById(modalId).classList.add('active');
        }, 10);

        // Store resolve function
        window['modalResolve_' + modalId] = resolve;
    });
}

/**
 * Close modal and resolve
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
            if (window['modalResolve_' + modalId]) {
                window['modalResolve_' + modalId]();
                delete window['modalResolve_' + modalId];
            }
        }, 300);
    }
}

/**
 * Close modal when clicking overlay
 */
function closeModalOnOverlay(event, modalId, resolveValue = undefined) {
    if (event.target.classList.contains('custom-modal-overlay')) {
        if (resolveValue !== undefined) {
            resolveConfirm(modalId, resolveValue);
        } else {
            closeModal(modalId);
        }
    }
}

/**
 * Resolve confirmation modal
 */
function resolveConfirm(modalId, result) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
            if (window['modalResolve_' + modalId]) {
                window['modalResolve_' + modalId](result);
                delete window['modalResolve_' + modalId];
            }
        }, 300);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModalContainer);
} else {
    initModalContainer();
}
