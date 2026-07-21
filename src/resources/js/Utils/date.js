export function formatDate(value) {
    if (!value) {
        return '';
    }

    return value.slice(0, 10).replaceAll('-', '/');
}

export function formatDateTime(value) {
    if (!value) {
        return '';
    }

    return `${formatDate(value)} ${value.slice(11, 19)}`;
}
