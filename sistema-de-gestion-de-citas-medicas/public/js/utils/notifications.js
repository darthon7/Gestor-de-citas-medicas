// js/utils/notifications.js - Sistema de Toast Notifications

let toastContainer = null;

function ensureContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
}

export const notify = {
    show(message, type = 'info', duration = 4000) {
        ensureContainer();

        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;

        const icon = document.createElement('i');
        if (type === 'success') icon.className = 'lucide-check-circle';
        else if (type === 'error') icon.className = 'lucide-alert-triangle';
        else if (type === 'warning') icon.className = 'lucide-alert-circle';
        else icon.className = 'lucide-info';

        const text = document.createElement('span');
        text.textContent = message;

        toast.appendChild(icon);
        toast.appendChild(text);
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s forwards';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duration);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg) { this.show(msg, 'error'); },
    warning(msg) { this.show(msg, 'warning'); },
    info(msg) { this.show(msg, 'info'); }
};
