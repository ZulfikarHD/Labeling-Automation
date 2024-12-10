export const months = [
    "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
    "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
];

export function formatDate(date) {
    return `${date.getDate()}-${months[date.getMonth()]}-${date.getFullYear()}`;
}

export function formatTime(date) {
    return `${date.getHours()} : ${date.getMinutes()}`;
}
