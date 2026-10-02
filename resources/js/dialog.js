/**
 * Premium Dialog, Modal Confirmation & Toast Notification System
 * Replaces native window.confirm(), alert(), and prompt() with modern, beautiful UI components.
 */

let activeDialog = null;

export function showConfirm({
    title = 'Konfirmasi Tindakan',
    message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    confirmText = 'Ya, Lanjutkan',
    cancelText = 'Batal',
    type = 'primary', // 'primary' | 'danger' | 'warning'
} = {}) {
    return new Promise((resolve) => {
        closeCurrentDialog(false);

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-200 opacity-0';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        const isDanger = type === 'danger';
        const isWarning = type === 'warning';

        let iconBg = 'bg-emerald-100 text-emerald-700 border-emerald-200/80';
        let iconSvg = `
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        `;
        let confirmBtnClass = 'btn btn-sm btn-primary shadow-xs';

        if (isDanger) {
            iconBg = 'bg-rose-100 text-rose-600 border-rose-200/80';
            iconSvg = `
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            `;
            confirmBtnClass = 'btn btn-sm bg-rose-600 hover:bg-rose-700 text-white border-transparent shadow-xs';
        } else if (isWarning) {
            iconBg = 'bg-amber-100 text-amber-700 border-amber-200/80';
            iconSvg = `
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            `;
            confirmBtnClass = 'btn btn-sm bg-amber-600 hover:bg-amber-700 text-white border-transparent shadow-xs';
        }

        overlay.innerHTML = `
            <div class="dialog-card w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 transition-all duration-200 transform scale-95 opacity-0">
                <div class="flex items-start gap-4">
                    <div class="size-11 sm:size-12 rounded-2xl flex items-center justify-center shrink-0 border ${iconBg}">
                        ${iconSvg}
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-snug">
                            ${escapeHtml(title)}
                        </h3>
                        <div class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed space-y-1 font-medium whitespace-pre-line">
                            ${escapeHtml(message)}
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-dialog-cancel btn btn-sm btn-quiet text-xs font-bold text-slate-600 hover:text-slate-900">
                        ${escapeHtml(cancelText)}
                    </button>
                    <button type="button" class="btn-dialog-confirm ${confirmBtnClass} text-xs font-bold">
                        ${escapeHtml(confirmText)}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const card = overlay.querySelector('.dialog-card');
        const btnConfirm = overlay.querySelector('.btn-dialog-confirm');
        const btnCancel = overlay.querySelector('.btn-dialog-cancel');

        // Animate entrance
        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
            btnConfirm.focus();
        });

        function cleanup(result) {
            overlay.classList.add('opacity-0');
            card.classList.add('scale-95', 'opacity-0');
            card.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                document.body.style.overflow = '';
                if (activeDialog === cleanup) activeDialog = null;
                resolve(result);
            }, 180);
        }

        activeDialog = cleanup;

        btnConfirm.addEventListener('click', () => cleanup(true));
        btnCancel.addEventListener('click', () => cleanup(false));

        // Click outside on backdrop
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cleanup(false);
        });

        // Keyboard navigation
        overlay.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                cleanup(false);
            } else if (e.key === 'Tab') {
                // Trap focus
                const focusables = [btnCancel, btnConfirm];
                if (e.shiftKey && document.activeElement === focusables[0]) {
                    e.preventDefault();
                    focusables[1].focus();
                } else if (!e.shiftKey && document.activeElement === focusables[1]) {
                    e.preventDefault();
                    focusables[0].focus();
                }
            }
        });
    });
}

export function showAlert({
    title = 'Perhatian',
    message = '',
    buttonText = 'Mengerti',
    type = 'info', // 'info' | 'warning' | 'success' | 'danger'
} = {}) {
    return new Promise((resolve) => {
        closeCurrentDialog(false);

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-200 opacity-0';
        overlay.setAttribute('role', 'alertdialog');
        overlay.setAttribute('aria-modal', 'true');

        let iconBg = 'bg-blue-100 text-blue-700 border-blue-200/80';
        let iconSvg = `
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
        `;

        if (type === 'warning') {
            iconBg = 'bg-amber-100 text-amber-700 border-amber-200/80';
            iconSvg = `
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            `;
        } else if (type === 'danger') {
            iconBg = 'bg-rose-100 text-rose-600 border-rose-200/80';
            iconSvg = `
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            `;
        } else if (type === 'success') {
            iconBg = 'bg-emerald-100 text-emerald-700 border-emerald-200/80';
            iconSvg = `
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            `;
        }

        overlay.innerHTML = `
            <div class="dialog-card w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 transition-all duration-200 transform scale-95 opacity-0">
                <div class="flex items-start gap-4">
                    <div class="size-11 sm:size-12 rounded-2xl flex items-center justify-center shrink-0 border ${iconBg}">
                        ${iconSvg}
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-snug">
                            ${escapeHtml(title)}
                        </h3>
                        <div class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed space-y-1 font-medium whitespace-pre-line">
                            ${escapeHtml(message)}
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" class="btn-dialog-close btn btn-sm btn-primary text-xs font-bold w-full sm:w-auto px-6">
                        ${escapeHtml(buttonText)}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const card = overlay.querySelector('.dialog-card');
        const btnClose = overlay.querySelector('.btn-dialog-close');

        requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
            btnClose.focus();
        });

        function cleanup() {
            overlay.classList.add('opacity-0');
            card.classList.add('scale-95', 'opacity-0');
            card.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                document.body.style.overflow = '';
                if (activeDialog === cleanup) activeDialog = null;
                resolve();
            }, 180);
        }

        activeDialog = cleanup;

        btnClose.addEventListener('click', cleanup);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cleanup();
        });
        overlay.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' || e.key === 'Enter') {
                e.preventDefault();
                cleanup();
            }
        });
    });
}

export function showToast({
    message = '',
    type = 'success', // 'success' | 'info' | 'error'
    duration = 3000,
} = {}) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-[110] flex flex-col gap-2.5 max-w-sm pointer-events-none';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const isError = type === 'error';
    const isSuccess = type === 'success';

    let bgClass = 'bg-slate-900 text-white border-slate-800';
    let iconSvg = `
        <svg class="size-4.5 text-blue-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    `;

    if (isSuccess) {
        bgClass = 'bg-slate-900 text-white border-slate-800';
        iconSvg = `
            <svg class="size-4.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        `;
    } else if (isError) {
        bgClass = 'bg-rose-950 text-white border-rose-800';
        iconSvg = `
            <svg class="size-4.5 text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        `;
    }

    toast.className = `flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl border ${bgClass} text-xs sm:text-sm font-semibold pointer-events-auto transition-all duration-300 transform translate-y-3 opacity-0`;
    toast.innerHTML = `
        ${iconSvg}
        <span class="flex-1 leading-snug">${escapeHtml(message)}</span>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 300);
    }, duration);
}

function closeCurrentDialog(result = false) {
    if (typeof activeDialog === 'function') {
        activeDialog(result);
        activeDialog = null;
    }
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Expose globally to window object for convenient access
if (typeof window !== 'undefined') {
    window.showConfirm = showConfirm;
    window.showAlert = showAlert;
    window.showToast = showToast;
}
