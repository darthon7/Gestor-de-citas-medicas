// js/utils/formatters.js - Utilidades de formato

export function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).format(date);
}

export function formatTime(timeString) {
    if (!timeString) return '';
    // soporta "10:00:00" o "2026-07-22 10:00:00"
    const parts = timeString.split(' ');
    const t = parts.length > 1 ? parts[1] : parts[0];
    const [h, m] = t.split(':');
    let hours = parseInt(h, 10);
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return `${hours}:${m} ${ampm}`;
}

export function formatFullDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    const date = new Date(dateTimeString.replace(' ', 'T'));
    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).format(date);
}

export function getStatusBadgeHtml(estado) {
    const est = (estado || '').toLowerCase();
    let badgeClass = 'badge--info';

    if (est === 'confirmada' || est === 'completada' || est === 'activo') {
        badgeClass = 'badge--success';
    } else if (est === 'pendiente') {
        badgeClass = 'badge--warning';
    } else if (est === 'cancelada' || est === 'inactivo') {
        badgeClass = 'badge--danger';
    }

    return `<span class="badge ${badgeClass}">${estado}</span>`;
}
