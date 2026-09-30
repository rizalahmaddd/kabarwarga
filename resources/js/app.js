// Admin Mobile Bottom Sheet Drawer
function setupAdminDrawer() {
    const drawer = document.getElementById('admin-drawer');
    const backdrop = document.getElementById('admin-drawer-backdrop');
    const panel = document.getElementById('admin-drawer-panel');
    const closeBtn = document.getElementById('admin-drawer-close');
    const triggers = document.querySelectorAll('[data-admin-drawer-trigger]');

    if (!drawer || !backdrop || !panel) return;

    function openDrawer() {
        drawer.classList.remove('hidden');
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('translate-y-full');
            panel.classList.add('translate-y-0');
        });
        document.body.classList.add('overflow-hidden');
    }

    function closeDrawer() {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        panel.classList.remove('translate-y-0');
        panel.classList.add('translate-y-full');
        document.body.classList.remove('overflow-hidden');
        setTimeout(() => {
            drawer.classList.add('hidden');
        }, 250);
    }

    triggers.forEach(t => t.addEventListener('click', openDrawer));
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !drawer.classList.contains('hidden')) {
            closeDrawer();
        }
    });
}

// Menu toggle for regular dropdowns
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-menu-toggle]');
    if (!toggle) return;

    const menu = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!menu) return;
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    menu.classList.toggle('hidden', !open);
});

// Form confirm & busy states
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }

    const button = event.submitter;
    if (button && button.dataset.busy) {
        setTimeout(() => {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${button.dataset.busy}
            `;
        });
    }
});

// Auto-submit forms on select change
document.addEventListener('change', (event) => {
    if (event.target.matches('[data-autosubmit]')) {
        event.target.form.requestSubmit();
    }
});

// Client-side quick filter
function setupQuickFilters() {
    document.querySelectorAll('[data-filter]').forEach((input) => {
        const rows = document.querySelectorAll(input.dataset.filter);
        const empty = document.getElementById(input.dataset.filterEmpty);

        input.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            let shown = 0;
            rows.forEach((row) => {
                const search = (row.dataset.search || '').toLowerCase();
                const match = search.includes(term);
                row.hidden = !match;
                if (match) shown++;
            });
            if (empty) empty.hidden = shown > 0;
        });
    });
}

// Show/hide based on form state
function setupShowWhen() {
    document.querySelectorAll('[data-show-when]').forEach((block) => {
        const [name, value] = block.dataset.showWhen.split('=');
        const inputs = document.querySelectorAll(`[name="${name}"]`);
        const sync = () => {
            const current = [...inputs].find((i) => i.type !== 'radio' || i.checked)?.value;
            block.hidden = current !== value;
        };
        inputs.forEach((i) => i.addEventListener('change', sync));
        sync();
    });
}

// Horizontal table auto-scroll to current month
function setupScrollCurrent() {
    document.querySelectorAll('[data-scroll-current]').forEach((region) => {
        const current = region.querySelector('[data-current-month]');
        if (current && region.scrollWidth > region.clientWidth) {
            region.scrollLeft = current.offsetLeft - 120;
        }
    });
}

// Admin Payment Form Live Calculator & Quick Selectors
function setupPaymentCalculator() {
    const container = document.getElementById('payment-month-picker');
    if (!container) return;

    const unitAmount = parseInt(container.dataset.unitAmount || '0', 10);
    const checkboxes = container.querySelectorAll('input[type="checkbox"][name="periods[]"]');
    const summaryCount = document.getElementById('selected-months-count');
    const summaryTotal = document.getElementById('selected-months-total');
    const amountInput = document.getElementById('amount');

    function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function recalculate() {
        const checked = [...checkboxes].filter(cb => cb.checked);
        const count = checked.length;
        const total = count * unitAmount;

        if (summaryCount) summaryCount.textContent = count;
        if (summaryTotal) summaryTotal.textContent = formatRupiah(total);
        if (amountInput && count > 0 && !amountInput.dataset.manual) {
            amountInput.value = total;
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (amountInput) delete amountInput.dataset.manual;
            recalculate();
        });
    });

    if (amountInput) {
        amountInput.addEventListener('input', () => {
            amountInput.dataset.manual = 'true';
        });
    }

    // Quick action buttons
    document.querySelectorAll('[data-period-action]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = btn.dataset.periodAction;
            const currentPeriod = container.dataset.currentPeriod;

            if (action === 'current') {
                checkboxes.forEach(cb => {
                    cb.checked = (cb.value === currentPeriod);
                });
            } else if (action === 'all-unpaid') {
                checkboxes.forEach(cb => {
                    cb.checked = true;
                });
            } else if (action === 'until-current') {
                checkboxes.forEach(cb => {
                    cb.checked = cb.value <= currentPeriod;
                });
            } else if (action === 'clear') {
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
            }
            if (amountInput) delete amountInput.dataset.manual;
            recalculate();
        });
    });

    recalculate();
}

// View switcher for Dues page (Cards vs Spreadsheet Matrix)
function setupViewSwitcher() {
    const switchers = document.querySelectorAll('[data-view-switcher]');
    if (!switchers.length) return;

    function setView(mode) {
        document.querySelectorAll('[data-view-target]').forEach(el => {
            el.hidden = (el.dataset.viewTarget !== mode);
        });
        switchers.forEach(btn => {
            const active = btn.dataset.viewSwitcher === mode;
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('text-slate-900', active);
            btn.classList.toggle('shadow-xs', active);
            btn.classList.toggle('font-bold', active);
            btn.classList.toggle('text-slate-600', !active);
        });
        try {
            localStorage.setItem('warga_dues_view', mode);
        } catch (_) {}
    }

    switchers.forEach(btn => {
        btn.addEventListener('click', () => {
            setView(btn.dataset.viewSwitcher);
        });
    });

    // Auto load preference or default to cards on mobile
    const saved = localStorage.getItem('warga_dues_view');
    const defaultView = saved || (window.innerWidth < 768 ? 'cards' : 'matrix');
    setView(defaultView);
}

// Custom Searchable Dropdown Component
function setupCustomDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    if (!dropdowns.length) return;

    function closeAllDropdowns(except = null) {
        dropdowns.forEach(dd => {
            if (dd === except) return;
            const menu = dd.querySelector('[data-dropdown-menu]');
            const arrow = dd.querySelector('[data-dropdown-arrow]');
            const trigger = dd.querySelector('[data-dropdown-trigger]');
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        });
    }

    dropdowns.forEach(dd => {
        const trigger = dd.querySelector('[data-dropdown-trigger]');
        const menu = dd.querySelector('[data-dropdown-menu]');
        const arrow = dd.querySelector('[data-dropdown-arrow]');
        const input = dd.querySelector('[data-dropdown-input]');
        const labelEl = dd.querySelector('[data-dropdown-label]');
        const searchInput = dd.querySelector('[data-dropdown-search]');
        const options = dd.querySelectorAll('[data-dropdown-option]');
        const emptyEl = dd.querySelector('[data-dropdown-empty]');
        const isAutosubmit = dd.dataset.autosubmit === 'true';

        if (!trigger || !menu || !input) return;

        function toggleDropdown(e) {
            e.stopPropagation();
            const isClosed = menu.classList.contains('hidden');
            closeAllDropdowns(isClosed ? dd : null);

            if (isClosed) {
                menu.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                if (arrow) arrow.classList.add('rotate-180');
                if (searchInput) {
                    searchInput.value = '';
                    filterOptions('');
                    setTimeout(() => searchInput.focus(), 60);
                }
            } else {
                menu.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        }

        trigger.addEventListener('click', toggleDropdown);

        function filterOptions(query) {
            const term = query.trim().toLowerCase();
            let matches = 0;
            options.forEach(opt => {
                const text = (opt.dataset.label || opt.textContent).toLowerCase();
                const match = text.includes(term);
                opt.style.display = match ? 'flex' : 'none';
                if (match) matches++;
            });
            if (emptyEl) {
                emptyEl.classList.toggle('hidden', matches > 0);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                filterOptions(e.target.value);
            });
            searchInput.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = opt.dataset.value;
                const label = opt.dataset.label || opt.textContent.trim();

                input.value = val;
                if (labelEl) {
                    labelEl.textContent = label;
                    labelEl.classList.remove('text-slate-400', 'font-normal');
                    labelEl.classList.add('text-slate-800', 'font-semibold');
                }

                options.forEach(o => {
                    const selected = o === opt;
                    o.classList.toggle('bg-emerald-50', selected);
                    o.classList.toggle('text-daun-dark', selected);
                    o.setAttribute('aria-selected', String(selected));
                });

                menu.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.classList.remove('rotate-180');

                // Trigger change event for listeners
                input.dispatchEvent(new Event('change', { bubbles: true }));

                if (isAutosubmit && input.form) {
                    input.form.requestSubmit();
                }
            });
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-dropdown]')) {
            closeAllDropdowns();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
}

// Announcement Modal Popup Handler
function setupAnnouncementPopup() {
    const popup = document.getElementById('home-announcement-popup');
    const panel = document.getElementById('home-announcement-panel');
    const closeBtn = document.getElementById('popup-close-btn');
    const dismissBtn = document.getElementById('popup-dismiss-btn');

    if (!popup || !panel) return;

    const popupId = popup.dataset.popupId;
    const storageKey = 'warga_popup_closed_' + popupId;

    // Jangan tampilkan jika sudah ditutup di sesi browser ini
    if (sessionStorage.getItem(storageKey)) {
        return;
    }

    // Tampilkan popup otomatis secara halus setelah halaman siap
    setTimeout(() => {
        popup.classList.remove('pointer-events-none', 'opacity-0');
        popup.classList.add('opacity-100');
        panel.classList.remove('scale-95');
        panel.classList.add('scale-100');
    }, 250);

    function closePopup() {
        popup.classList.remove('opacity-100');
        popup.classList.add('opacity-0', 'pointer-events-none');
        panel.classList.remove('scale-100');
        panel.classList.add('scale-95');
        try {
            sessionStorage.setItem(storageKey, 'true');
        } catch (_) {}
    }

    if (closeBtn) closeBtn.addEventListener('click', closePopup);
    if (dismissBtn) dismissBtn.addEventListener('click', closePopup);

    popup.addEventListener('click', (e) => {
        if (!panel.contains(e.target)) {
            closePopup();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !popup.classList.contains('pointer-events-none')) {
            closePopup();
        }
    });
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy]');
    if (!button) return;

    event.preventDefault();
    try {
        await navigator.clipboard.writeText(button.dataset.copy);
    } catch (_) {
        window.prompt('Salin nomor ini:', button.dataset.copy);
        return;
    }
    const original = button.textContent;
    button.textContent = 'Tersalin ✓';
    setTimeout(() => { button.textContent = original; }, 1500);
});

function setupProofPreview() {
    const input = document.querySelector('[data-proof-input]');
    if (!input) return;

    const preview = document.querySelector('[data-proof-preview]');
    const name = document.querySelector('[data-proof-name]');

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        name.textContent = `${file.name} · ketuk untuk ganti`;
        if (file.type.startsWith('image/')) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }
    });
}

function setupPartialApprove() {
    document.querySelectorAll('[data-partial-approve]').forEach((form) => {
        const boxes = form.querySelectorAll('input[name="periods[]"]');
        const reason = form.querySelector('[data-partial-reason]');
        const total = form.querySelector('[data-partial-total]');
        if (!boxes.length) return;

        const unit = parseInt(form.dataset.unitAmount || '0', 10);
        const sync = () => {
            const checked = [...boxes].filter((b) => b.checked).length;
            if (reason) reason.hidden = checked === boxes.length;
            if (total) total.textContent = 'Rp' + (checked * unit).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };
        boxes.forEach((b) => b.addEventListener('change', sync));
        sync();
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    setupPartialApprove();
    setupProofPreview();
    setupAdminDrawer();
    setupQuickFilters();
    setupShowWhen();
    setupScrollCurrent();
    setupPaymentCalculator();
    setupViewSwitcher();
    setupCustomDropdowns();
    setupAnnouncementPopup();
});
