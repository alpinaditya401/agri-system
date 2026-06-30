import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const methodField = form.querySelector('input[name="_method"]');
            const spoofedMethod = methodField?.value?.toUpperCase();
            const isDestructive = spoofedMethod === 'DELETE' || form.dataset.confirm;

            if (isDestructive) {
                const message = form.dataset.confirm || 'Yakin ingin menghapus data ini?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                    return;
                }
            }

            if (form.dataset.loading === 'off' || form.method.toLowerCase() === 'get') {
                return;
            }

            const submitter = event.submitter;
            if (!submitter || submitter.dataset.loadingBound === '1') {
                return;
            }

            submitter.dataset.loadingBound = '1';
            submitter.dataset.originalHtml = submitter.innerHTML;
            submitter.disabled = true;
            submitter.innerHTML = `
                <span class="inline-flex items-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"></span>
                    <span>${submitter.dataset.loadingText || 'Memproses...'}</span>
                </span>
            `;
        });
    });

    const revealTargets = Array.from(document.querySelectorAll('[data-reveal], .ag-card, .ag-card-soft, .ag-table-wrap'));
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (revealTargets.length) {
        revealTargets.forEach((target, index) => {
            target.classList.add('reveal');
            if (!target.style.getPropertyValue('--reveal-delay') && index < 12) {
                target.style.setProperty('--reveal-delay', `${Math.min(index * 35, 210)}ms`);
            }
        });

        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealTargets.forEach((target) => target.classList.add('is-visible'));
            return;
        }

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -8% 0px',
            threshold: 0.12,
        });

        revealTargets.forEach((target) => revealObserver.observe(target));
    }
});
