import './bootstrap';

const updateOfflineBanner = () => {
    document.querySelectorAll('[data-offline-indicator]').forEach((element) => {
        element.classList.toggle('hidden', navigator.onLine);
    });
};

window.addEventListener('online', updateOfflineBanner);
window.addEventListener('offline', updateOfflineBanner);
document.addEventListener('DOMContentLoaded', () => {
    updateOfflineBanner();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.warn('Service worker registration failed', error);
            });
        });
    }
});
