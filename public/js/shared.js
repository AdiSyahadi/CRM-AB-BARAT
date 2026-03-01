/**
 * Abbarat Management System - Shared JavaScript
 * Global utilities used across all pages.
 */

/**
 * Show a toast notification.
 * @param {string} message - The message to display.
 * @param {string} [type='success'] - Type: 'success', 'error', or 'info'.
 */
window.showToast = function(message, type) {
    if (!type) type = 'success';
    var bg = type === 'success' ? '#16a34a' : type === 'error' ? '#dc2626' : '#059669';
    var icon = type === 'success' ? 'check-circle-fill' : type === 'error' ? 'x-circle-fill' : 'info-circle-fill';
    var toast = document.createElement('div');
    toast.innerHTML = '<i class="bi bi-' + icon + '"></i> ' + message;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:' + bg + ';color:#fff;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:8px;animation:crud-fadeInUp .3s ease;max-width:90vw';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
};
