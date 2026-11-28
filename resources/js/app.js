import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Global Custom Form Validation (LapangIn)
 * Mengganti tooltip bawaan browser ("Please fill out this field.") dengan
 * pesan inline Bahasa Indonesia yang konsisten di seluruh form aplikasi.
 */
(function initCustomFormValidation() {
    const ERROR_CLASS = 'lapangin-field-error';
    const INVALID_CLASS = 'lapangin-input-invalid';

    function getValidationMessage(el) {
        const v = el.validity;
        if (!v || v.valid) return '';

        if (v.valueMissing) {
            if (el.dataset.errorRequired) return el.dataset.errorRequired;
            if (el.type === 'checkbox') return 'Harap centang kotak persetujuan ini terlebih dahulu.';
            if (el.type === 'radio') return 'Silakan pilih salah satu opsi yang tersedia.';
            if (el.tagName === 'SELECT') return 'Silakan pilih salah satu opsi terlebih dahulu.';
            return 'Kolom ini wajib diisi.';
        }
        if (v.typeMismatch) {
            if (el.type === 'email') return 'Format email belum valid (contoh: nama@email.com).';
            if (el.type === 'url') return 'Format tautan URL belum valid.';
            return 'Format isian belum sesuai.';
        }
        if (v.tooShort) {
            return `Minimal ${el.minLength} karakter.`;
        }
        if (v.tooLong) {
            return `Maksimal ${el.maxLength} karakter.`;
        }
        if (v.rangeUnderflow) {
            return `Nilai minimal adalah ${el.min}.`;
        }
        if (v.rangeOverflow) {
            return `Nilai maksimal adalah ${el.max}.`;
        }
        if (v.stepMismatch) {
            return 'Nilai yang dimasukkan tidak sesuai kelipatan yang diizinkan.';
        }
        if (v.patternMismatch) {
            return el.title || 'Format isian belum sesuai ketentuan.';
        }
        return 'Harap periksa kembali isian pada kolom ini.';
    }

    function getFieldContainer(el) {
        if (el.type === 'checkbox' || el.type === 'radio') {
            return el.closest('label') || el.parentElement;
        }
        const wrapper = el.closest('.relative.rounded-xl, .flex.items-center.rounded-xl');
        return wrapper || el;
    }

    function clearFieldError(el) {
        const container = getFieldContainer(el);
        if (!container) return;
        container.classList.remove(INVALID_CLASS);
        el.classList.remove(INVALID_CLASS);
        el.removeAttribute('aria-invalid');
        const next = container.nextElementSibling;
        if (next && next.classList.contains(ERROR_CLASS)) {
            next.remove();
        }
    }

    function showFieldError(el, message) {
        clearFieldError(el);
        const container = getFieldContainer(el);
        if (!container) return;

        if (el.type !== 'checkbox' && el.type !== 'radio') {
            container.classList.add(INVALID_CLASS);
        }
        el.setAttribute('aria-invalid', 'true');

        const err = document.createElement('p');
        err.className = ERROR_CLASS;
        err.setAttribute('role', 'alert');
        err.innerHTML =
            '<svg class="w-3.5 h-3.5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<circle cx="12" cy="12" r="10"></circle>' +
            '<line x1="12" y1="8" x2="12" y2="12"></line>' +
            '<line x1="12" y1="16" x2="12.01" y2="16"></line>' +
            '</svg><span></span>';
        err.querySelector('span').textContent = message;

        container.insertAdjacentElement('afterend', err);
    }

    function applyNoValidate(root = document) {
        root.querySelectorAll('form:not([data-native-validation])').forEach((form) => {
            form.noValidate = true;
        });
        // Hilangkan tooltip hover bawaan browser "Please fill out this field." pada input required
        root.querySelectorAll('input[required]:not([title]), select[required]:not([title]), textarea[required]:not([title])').forEach((el) => {
            el.setAttribute('title', '');
        });
    }

    document.addEventListener('DOMContentLoaded', () => applyNoValidate());

    // Pastikan form dinamis (modal / Alpine) juga nonaktif tooltip bawaan sebelum submit
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('button, input[type="submit"]');
        if (btn && btn.form && !btn.form.hasAttribute('data-native-validation')) {
            btn.form.noValidate = true;
        }
    }, true);

    // Tangkap submit di fase capture sebelum Alpine @submit berjalan
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-native-validation')) return;

        form.noValidate = true;

        // Bersihkan error lama di form ini
        form.querySelectorAll('.' + ERROR_CLASS).forEach((n) => n.remove());
        form.querySelectorAll('.' + INVALID_CLASS).forEach((n) => n.classList.remove(INVALID_CLASS));

        let firstInvalid = null;

        Array.from(form.elements).forEach((el) => {
            if (!el.willValidate || el.disabled) return;
            if (el.offsetParent === null && el.type !== 'checkbox' && el.type !== 'radio') return;

            if (!el.validity.valid) {
                const msg = getValidationMessage(el);
                showFieldError(el, msg);
                if (!firstInvalid) firstInvalid = el;

                const clearOnce = () => clearFieldError(el);
                el.addEventListener('input', clearOnce, { once: true });
                el.addEventListener('change', clearOnce, { once: true });
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            firstInvalid.focus({ preventScroll: true });
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, true);
})();

/**
 * Global Custom Hover Tooltip (LapangIn)
 * Mengganti kotak tooltip bawaan browser (title="...") dengan tooltip modern
 * bergaya LapangIn di seluruh halaman aplikasi.
 */
(function initCustomTooltips() {
    if (window.matchMedia && !window.matchMedia('(hover: hover)').matches) return;

    let tooltipEl = null;
    let activeTarget = null;
    let showTimer = null;

    function ensureTooltipEl() {
        if (tooltipEl) return tooltipEl;
        tooltipEl = document.createElement('div');
        tooltipEl.className = 'lapangin-tooltip';
        tooltipEl.setAttribute('role', 'tooltip');
        document.body.appendChild(tooltipEl);
        return tooltipEl;
    }

    function positionTooltip(target) {
        if (!tooltipEl || !target) return;
        const rect = target.getBoundingClientRect();
        const tipRect = tooltipEl.getBoundingClientRect();
        const gap = 8;

        let top = rect.top - tipRect.height - gap;
        if (top < 8) {
            top = rect.bottom + gap;
        }
        let left = rect.left + (rect.width - tipRect.width) / 2;
        const maxLeft = window.innerWidth - tipRect.width - 10;
        left = Math.max(10, Math.min(left, maxLeft));

        tooltipEl.style.top = `${Math.round(top)}px`;
        tooltipEl.style.left = `${Math.round(left)}px`;
    }

    function hideTooltip() {
        clearTimeout(showTimer);
        activeTarget = null;
        if (tooltipEl) {
            tooltipEl.classList.remove('is-visible');
        }
    }

    function findTooltipTarget(node) {
        if (!node || node.nodeType !== 1) return null;
        return node.closest('[title], [data-lapangin-tooltip]');
    }

    document.addEventListener('pointerover', (e) => {
        const target = findTooltipTarget(e.target);
        if (!target) return;

        const nativeTitle = target.getAttribute('title');
        if (nativeTitle !== null) {
            if (nativeTitle.trim() !== '') {
                target.dataset.lapanginTooltip = nativeTitle.trim();
            }
            target.removeAttribute('title');
        }

        const text = target.dataset.lapanginTooltip;
        if (!text) return;

        if (activeTarget === target) return;
        activeTarget = target;
        clearTimeout(showTimer);

        showTimer = setTimeout(() => {
            if (activeTarget !== target) return;
            const el = ensureTooltipEl();
            el.textContent = text;
            el.classList.add('is-visible');
            positionTooltip(target);
        }, 180);
    }, true);

    document.addEventListener('pointerout', (e) => {
        if (!activeTarget) return;
        if (!e.relatedTarget || !activeTarget.contains(e.relatedTarget)) {
            hideTooltip();
        }
    }, true);

    document.addEventListener('pointerdown', hideTooltip, true);
    window.addEventListener('scroll', hideTooltip, { passive: true, capture: true });
})();

/**
 * Global Custom Toast Notification (Pengganti alert() bawaan browser)
 */
window.lapanginNotify = function (message, duration = 4200) {
    let stack = document.querySelector('.lapangin-toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'lapangin-toast-stack';
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = 'lapangin-toast';
    toast.setAttribute('role', 'status');
    toast.innerHTML =
        '<svg class="w-4 h-4 shrink-0 mt-0.5 text-[#acf847]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<circle cx="12" cy="12" r="10"></circle>' +
        '<path d="m9 12 2 2 4-4"></path>' +
        '</svg><span class="flex-1"></span>';
    toast.querySelector('span').textContent = String(message || '');
    stack.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('is-visible'));

    const dismiss = () => {
        toast.classList.remove('is-visible');
        setTimeout(() => toast.remove(), 200);
    };

    toast.addEventListener('click', dismiss);
    setTimeout(dismiss, duration);
};

Alpine.start();
