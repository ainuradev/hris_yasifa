import './bootstrap.js';

const initializeAppShell = () => {
    document.querySelectorAll('[data-app-shell]').forEach((shell) => {
        const storageKey = `hris-shell-${shell.dataset.shellRole}-collapsed`;
        const prefersCollapsed = window.localStorage.getItem(storageKey) === 'true';

        if (prefersCollapsed && window.innerWidth >= 1024) {
            shell.classList.add('is-sidebar-collapsed');
        }

        const syncExpandedButtons = () => {
            shell.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
                button.setAttribute('aria-expanded', String(!shell.classList.contains('is-sidebar-collapsed')));
            });
        };

        shell.querySelectorAll('[data-sidebar-open]').forEach((button) => {
            button.addEventListener('click', () => {
                shell.classList.add('is-sidebar-open');
            });
        });

        shell.querySelectorAll('[data-sidebar-close]').forEach((button) => {
            button.addEventListener('click', () => {
                shell.classList.remove('is-sidebar-open');
            });
        });

        shell.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                shell.classList.toggle('is-sidebar-collapsed');
                window.localStorage.setItem(storageKey, String(shell.classList.contains('is-sidebar-collapsed')));
                syncExpandedButtons();
            });
        });

        shell.querySelectorAll('.app-nav-link, .mobile-dock-link').forEach((link) => {
            link.addEventListener('click', () => {
                shell.classList.remove('is-sidebar-open');
            });
        });

        syncExpandedButtons();
    });
};

const initializeDismissButtons = () => {
    document.querySelectorAll('[data-dismiss-alert]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('[data-alert]')?.remove();
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initializeAppShell();
    initializeDismissButtons();
});
