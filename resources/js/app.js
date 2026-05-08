import './bootstrap.js';

const evaluateExpression = (state, helpers, expression, wrapReturn = true) => {
    const body = wrapReturn ? `return (${expression});` : expression;
    return new Function('state', 'helpers', `with (state) { with (helpers) { ${body} } }`)(state, helpers);
};

const isInsideCurrentRoot = (root, element) => element.closest('[x-data]') === root;

const initializeMiniAlpine = () => {
    const roots = Array.from(document.querySelectorAll('[x-data]')).filter((root) => !root.parentElement?.closest('[x-data]'));

    roots.forEach((root) => {
        const rawState = root.getAttribute('x-data')?.trim() ? evaluateExpression({}, { window, document, navigator, console }, root.getAttribute('x-data')) : {};
        const watchers = new Map();
        const refs = {};
        const bindings = [];

        root.querySelectorAll('[x-ref]').forEach((element) => {
            if (!isInsideCurrentRoot(root, element)) {
                return;
            }

            refs[element.getAttribute('x-ref')] = element;
        });

        let state;
        const helpers = {
            window,
            document,
            navigator,
            console,
            Math,
            Date,
            setTimeout,
            clearTimeout,
            setInterval,
            clearInterval,
            alert: window.alert.bind(window),
            confirm: window.confirm.bind(window),
            get $refs() {
                return refs;
            },
            $watch(property, callback) {
                if (!watchers.has(property)) {
                    watchers.set(property, []);
                }

                watchers.get(property).push(callback);
            },
        };

        const render = () => {
            bindings.forEach((binding) => binding());
        };

        state = new Proxy(rawState ?? {}, {
            set(target, property, value) {
                const previous = target[property];
                target[property] = value;

                if (previous !== value) {
                    (watchers.get(property) ?? []).forEach((callback) => callback(value, previous));
                    render();
                }

                return true;
            },
        });

        const read = (expression, locals = {}) =>
            evaluateExpression(state, { ...helpers, ...locals }, expression);

        const execute = (expression, locals = {}) =>
            evaluateExpression(state, { ...helpers, ...locals }, expression, false);

        root.querySelectorAll('*').forEach((element) => {
            if (!isInsideCurrentRoot(root, element)) {
                return;
            }

            const xText = element.getAttribute('x-text');
            if (xText) {
                bindings.push(() => {
                    const value = read(xText, { $el: element });
                    element.textContent = value ?? '';
                });
            }

            const xShow = element.getAttribute('x-show');
            if (xShow) {
                const initialDisplay = getComputedStyle(element).display === 'none' ? '' : getComputedStyle(element).display;
                bindings.push(() => {
                    const visible = Boolean(read(xShow, { $el: element }));
                    element.style.display = visible ? initialDisplay : 'none';
                    element.hidden = !visible;
                });
            }

            const classExpression = element.getAttribute(':class') ?? element.getAttribute('x-bind:class');
            if (classExpression) {
                const baseClassName = element.getAttribute('class') ?? '';
                bindings.push(() => {
                    const value = read(classExpression, { $el: element });
                    let dynamicClassName = '';

                    if (typeof value === 'string') {
                        dynamicClassName = value;
                    } else if (value && typeof value === 'object') {
                        dynamicClassName = Object.entries(value)
                            .filter(([, enabled]) => Boolean(enabled))
                            .map(([className]) => className)
                            .join(' ');
                    }

                    element.className = [baseClassName, dynamicClassName].filter(Boolean).join(' ').trim();
                });
            }

            const disabledExpression = element.getAttribute(':disabled') ?? element.getAttribute('x-bind:disabled');
            if (disabledExpression) {
                bindings.push(() => {
                    element.disabled = Boolean(read(disabledExpression, { $el: element }));
                });
            }

            const xModel = element.getAttribute('x-model');
            if (xModel) {
                bindings.push(() => {
                    if (element.type === 'checkbox') {
                        element.checked = Boolean(state[xModel]);
                    } else {
                        element.value = state[xModel] ?? '';
                    }
                });

                const syncEvent = element.tagName === 'SELECT' || element.type === 'checkbox' ? 'change' : 'input';
                element.addEventListener(syncEvent, () => {
                    state[xModel] = element.type === 'checkbox' ? element.checked : element.value;
                });
            }

            Array.from(element.attributes)
                .filter((attribute) => attribute.name.startsWith('@'))
                .forEach((attribute) => {
                    const [eventName, ...modifiers] = attribute.name.slice(1).split('.');
                    element.addEventListener(eventName, (event) => {
                        if (modifiers.includes('prevent')) {
                            event.preventDefault();
                        }

                        execute(attribute.value, { $event: event, $el: element });
                        render();
                    });
                });
        });

        const initExpression = root.getAttribute('x-init');
        if (initExpression) {
            execute(initExpression, { $el: root });
        }

        render();
    });
};

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
    initializeMiniAlpine();
    initializeAppShell();
    initializeDismissButtons();
});
