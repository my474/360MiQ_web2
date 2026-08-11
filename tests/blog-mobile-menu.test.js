'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const menuScript = read('blog/wp-content/mu-plugins/360miq-theme-sync.js');
const menuStyles = read('blog/wp-content/mu-plugins/360miq-theme-sync.css');
const plugin = read('blog/wp-content/mu-plugins/360miq-theme-sync.php');

assert.match(plugin, /Version:\s*1\.4\.0/);
assert.match(menuStyles, /#menu-primary-container\.open\s*\{[^}]*max-height:\s*calc\(100dvh - 4\.5rem\)\s*!important/s);
assert.match(menuStyles, /#menu-primary-container\.open\s*\{[^}]*overflow-y:\s*auto/s);
assert.match(menuStyles, /#menu-primary-container\.open\s*\{[^}]*overscroll-behavior-y:\s*contain/s);
assert.match(menuStyles, /#menu-primary-container\.open\s*\{[^}]*touch-action:\s*pan-y pinch-zoom/s);
assert.match(menuStyles, /\.miq360-account-menu\s*\{[^}]*display:\s*none/s);
assert.match(menuStyles, /\.miq360-account-item\.is-authenticated\.is-open\s*>\s*\.miq360-account-menu\s*\{[^}]*display:\s*block/s);
assert.match(menuStyles, /html\.miq360-blog-menu-ready\s+\.menu-primary-items li\.menu-item-has-children\s*>\s*ul,[\s\S]*?\{\s*display:\s*none/);
assert.match(menuStyles, /\.menu-item-has-children\.miq360-mobile-submenu-open\s*>\s*ul,[\s\S]*?\{\s*display:\s*block/);
assert.match(menuStyles, /@media all and \(min-width:\s*50em\)[\s\S]*\.menu-primary-items\s*>\s*li\.menu-item-has-children:hover/);
assert.match(menuStyles, /html\[data-theme="dark"\]\s+\.miq360-submenu-toggle\s*\{[^}]*color:\s*#bbbbbb/s);

class FakeClassList {
    constructor(element, initial) {
        this.element = element;
        this.values = new Set(initial || []);
    }

    add(...names) { names.forEach((name) => this.values.add(name)); }
    remove(...names) { names.forEach((name) => this.values.delete(name)); }
    contains(name) { return this.values.has(name); }
    toggle(name, force) {
        const next = force === undefined ? !this.values.has(name) : !!force;
        if (next) this.values.add(name);
        else this.values.delete(name);
        return next;
    }
    toString() { return Array.from(this.values).join(' '); }
}

class FakeElement {
    constructor(tagName, options = {}) {
        this.tagName = String(tagName || 'div').toUpperCase();
        this.children = [];
        this.parentNode = null;
        this.attributes = {};
        this.listeners = {};
        this.hidden = false;
        this.id = options.id || '';
        this.textContent = options.textContent || '';
        this.href = options.href || '';
        this.focused = false;
        this.classList = new FakeClassList(this, options.classes || []);

        Object.defineProperty(this, 'className', {
            get: () => this.classList.toString(),
            set: (value) => {
                this.classList.values = new Set(String(value).split(/\s+/).filter(Boolean));
            }
        });
    }

    appendChild(child) {
        child.parentNode = this;
        this.children.push(child);
        return child;
    }

    insertBefore(child, reference) {
        const index = this.children.indexOf(reference);
        child.parentNode = this;
        if (index === -1) this.children.push(child);
        else this.children.splice(index, 0, child);
        return child;
    }

    contains(target) {
        if (target === this) return true;
        return this.children.some((child) => child.contains(target));
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
        if (name === 'href') this.href = String(value);
    }
    getAttribute(name) { return Object.prototype.hasOwnProperty.call(this.attributes, name) ? this.attributes[name] : null; }
    addEventListener(type, listener) {
        if (!this.listeners[type]) this.listeners[type] = [];
        this.listeners[type].push(listener);
    }
    dispatch(type, event = {}) {
        event.target = event.target || this;
        event.preventDefault = event.preventDefault || function() {};
        event.stopPropagation = event.stopPropagation || function() {};
        (this.listeners[type] || []).forEach((listener) => listener(event));
    }
    dispatchEvent(event) { this.dispatch(event.type, event); }
    focus() { this.focused = true; }

    querySelectorAll(selector) {
        const matches = [];
        const visit = (node) => {
            node.children.forEach((child) => {
                if (
                    selector === 'li.menu-item-has-children' &&
                    child.tagName === 'LI' &&
                    child.classList.contains('menu-item-has-children')
                ) {
                    matches.push(child);
                }
                if (/^\[.+\]$/.test(selector)) {
                    const attribute = selector.slice(1, -1);
                    if (child.getAttribute(attribute) !== null) matches.push(child);
                }
                visit(child);
            });
        };
        visit(this);
        return matches;
    }

    querySelector(selector) { return this.querySelectorAll(selector)[0] || null; }
}

function createRuntime(options = {}) {
    const mobileMedia = {
        matches: options.mobile !== false,
        listeners: [],
        addEventListener(type, listener) { if (type === 'change') this.listeners.push(listener); },
        setMatches(matches) {
            this.matches = matches;
            this.listeners.forEach((listener) => listener({ matches }));
        }
    };
    const darkMedia = { matches: !!options.prefersDark };
    const documentListeners = {};
    const windowListeners = {};
    const savedValues = {};
    if (options.savedTheme !== undefined) savedValues['360miq-dark-mode'] = options.savedTheme;

    const html = new FakeElement('html');
    const body = new FakeElement('body');
    const menuContainer = new FakeElement('div', { id: 'menu-primary-container', classes: ['open'] });
    const menuRoot = new FakeElement('ul', { id: 'menu-primary-items', classes: ['menu-primary-items'] });
    menuContainer.appendChild(menuRoot);

    function submenuItem(label) {
        const item = new FakeElement('li', { classes: ['menu-item', 'menu-item-has-children'] });
        const link = new FakeElement('a', { textContent: label });
        const submenu = new FakeElement('ul');
        submenu.appendChild(new FakeElement('li', { classes: ['menu-item'] }));
        item.appendChild(link);
        item.appendChild(submenu);
        menuRoot.appendChild(item);
        return { item, link, submenu };
    }

    const market = submenuItem('Market');
    const econ = submenuItem('Econ');
    const themeToggle = new FakeElement('a', { id: 'theme-toggle' });
    const accountItem = new FakeElement('li', { classes: ['menu-item', 'miq360-account-item', 'is-guest'] });
    accountItem.setAttribute('data-miq-blog-account-shell', '');
    accountItem.setAttribute('data-login-url', 'https://360miq.com/account.php?view=login');
    const accountTrigger = new FakeElement('a', {
        id: 'miq360-blog-account-toggle',
        classes: ['is-guest'],
        href: 'https://360miq.com/account.php?view=login'
    });
    const accountMenu = new FakeElement('div', { id: 'miq360-blog-account-menu', classes: ['miq360-account-menu'] });
    const accountBadge = new FakeElement('span');
    accountBadge.setAttribute('data-miq-account-unread-badge', '');
    const accountChevron = new FakeElement('span');
    accountChevron.setAttribute('data-miq-account-chevron', '');
    const accountName = new FakeElement('strong');
    accountName.setAttribute('data-miq-account-display-name', '');
    const menuBadge = new FakeElement('span');
    menuBadge.setAttribute('data-miq-account-unread-badge', '');
    accountTrigger.appendChild(accountBadge);
    accountTrigger.appendChild(accountChevron);
    accountMenu.appendChild(accountName);
    accountMenu.appendChild(menuBadge);
    accountItem.appendChild(accountTrigger);
    accountItem.appendChild(accountMenu);
    menuRoot.appendChild(themeToggle);
    menuRoot.appendChild(accountItem);
    const navigationToggle = new FakeElement('button', { id: 'toggle-navigation' });

    const byId = {
        'menu-primary-container': menuContainer,
        'menu-primary-items': menuRoot,
        'toggle-navigation': navigationToggle,
        'theme-toggle': themeToggle,
        'miq360-blog-account-toggle': accountTrigger,
        'miq360-blog-account-menu': accountMenu
    };

    const documentObject = {
        documentElement: html,
        body,
        readyState: 'complete',
        getElementById(id) { return byId[id] || null; },
        querySelector(selector) {
            if (selector === '[data-miq-blog-account-shell]' || selector === '.miq360-account-item') return accountItem;
            if (selector === '.miq360-account-item.is-authenticated') {
                return accountItem.classList.contains('is-authenticated') ? accountItem : null;
            }
            return null;
        },
        createElement(tagName) { return new FakeElement(tagName); },
        addEventListener(type, listener) {
            if (!documentListeners[type]) documentListeners[type] = [];
            documentListeners[type].push(listener);
        },
        dispatch(type, event) {
            (documentListeners[type] || []).forEach((listener) => listener(event));
        }
    };

    const storage = {
        getItem(key) { return Object.prototype.hasOwnProperty.call(savedValues, key) ? savedValues[key] : null; },
        setItem(key, value) { savedValues[key] = String(value); }
    };
    const windowObject = {
        innerWidth: options.mobile === false ? 1200 : 390,
        __MIQ_ACCOUNT__: {
            loggedIn: options.loggedIn !== false,
            displayName: 'Test User',
            unreadNotifications: options.unreadNotifications || 0
        },
        matchMedia(query) { return query === '(max-width: 49.99em)' ? mobileMedia : darkMedia; },
        setTimeout(callback) { callback(); },
        addEventListener(type, listener) {
            if (!windowListeners[type]) windowListeners[type] = [];
            windowListeners[type].push(listener);
        }
    };

    class ThemeEvent {
        constructor(type, init) {
            this.type = type;
            this.detail = init.detail;
        }
    }

    vm.runInNewContext(menuScript, {
        window: windowObject,
        document: documentObject,
        localStorage: storage,
        CustomEvent: ThemeEvent
    });

    return {
        html,
        body,
        menuContainer,
        market,
        econ,
        themeToggle,
        accountItem,
        accountTrigger,
        accountMenu,
        accountBadge,
        menuBadge,
        accountName,
        navigationToggle,
        mobileMedia,
        document: documentObject,
        window: windowObject,
        marketToggle: market.item.children[1],
        econToggle: econ.item.children[1]
    };
}

function click(element) {
    element.dispatch('click', {
        target: element,
        preventDefault() {},
        stopPropagation() {}
    });
}

function verifyMobileMenu(prefersDark) {
    const runtime = createRuntime({ mobile: true, prefersDark });
    assert.strictEqual(runtime.html.getAttribute('data-theme'), prefersDark ? 'dark' : 'light');
    assert(runtime.html.classList.contains('miq360-blog-menu-ready'));
    assert(runtime.marketToggle.classList.contains('miq360-submenu-toggle'));
    assert.strictEqual(runtime.marketToggle.getAttribute('aria-controls'), runtime.market.submenu.id);
    assert.strictEqual(runtime.marketToggle.getAttribute('aria-expanded'), 'false');
    assert.strictEqual(runtime.market.submenu.hidden, true);
    assert.strictEqual(runtime.econ.submenu.hidden, true);
    assert.strictEqual(runtime.accountMenu.hidden, true);

    click(runtime.marketToggle);
    assert(runtime.market.item.classList.contains('miq360-mobile-submenu-open'));
    assert.strictEqual(runtime.market.submenu.hidden, false);
    assert.strictEqual(runtime.marketToggle.getAttribute('aria-expanded'), 'true');

    click(runtime.econToggle);
    assert(!runtime.market.item.classList.contains('miq360-mobile-submenu-open'));
    assert.strictEqual(runtime.market.submenu.hidden, true);
    assert(runtime.econ.item.classList.contains('miq360-mobile-submenu-open'));

    click(runtime.accountTrigger);
    assert(!runtime.econ.item.classList.contains('miq360-mobile-submenu-open'));
    assert(runtime.accountItem.classList.contains('is-open'));
    assert.strictEqual(runtime.accountMenu.hidden, false);
    assert.strictEqual(runtime.accountTrigger.getAttribute('aria-expanded'), 'true');

    runtime.document.dispatch('click', { target: runtime.accountMenu });
    assert(runtime.accountItem.classList.contains('is-open'), 'Touching or scrolling inside the account panel must not close it.');

    runtime.document.dispatch('keydown', { key: 'Escape' });
    assert(!runtime.accountItem.classList.contains('is-open'));
    assert.strictEqual(runtime.accountMenu.hidden, true);
    assert.strictEqual(runtime.accountTrigger.focused, true);

    return runtime;
}

const initialLight = verifyMobileMenu(false);
click(initialLight.marketToggle);
click(initialLight.themeToggle);
initialLight.document.dispatch('click', { target: initialLight.themeToggle });
assert.strictEqual(initialLight.html.getAttribute('data-theme'), 'dark');
assert.strictEqual(initialLight.market.submenu.hidden, false, 'Live light-to-dark toggling must preserve the open submenu.');
click(initialLight.themeToggle);
initialLight.document.dispatch('click', { target: initialLight.themeToggle });
assert.strictEqual(initialLight.html.getAttribute('data-theme'), 'light');
assert.strictEqual(initialLight.market.submenu.hidden, false, 'Live dark-to-light toggling must preserve the open submenu.');

verifyMobileMenu(true);

const desktop = createRuntime({ mobile: false, prefersDark: false });
assert.strictEqual(desktop.market.submenu.hidden, false);
assert.strictEqual(desktop.econ.submenu.hidden, false);
assert.strictEqual(desktop.accountMenu.hidden, false);
click(desktop.marketToggle);
assert(!desktop.market.item.classList.contains('miq360-mobile-submenu-open'));
click(desktop.accountTrigger);
assert(desktop.accountItem.classList.contains('is-open'), 'Desktop account click behavior must remain available.');
desktop.mobileMedia.setMatches(true);
assert.strictEqual(desktop.market.submenu.hidden, true);
assert.strictEqual(desktop.accountMenu.hidden, true);

const guest = createRuntime({ mobile: true, loggedIn: false });
assert(guest.accountItem.classList.contains('is-guest'));
assert(!guest.accountItem.classList.contains('is-authenticated'));
assert.strictEqual(guest.accountMenu.hidden, true);
assert.strictEqual(guest.accountTrigger.getAttribute('aria-haspopup'), 'false');
click(guest.accountTrigger);
assert(!guest.accountItem.classList.contains('is-open'), 'A guest account icon must navigate to sign-in, not reveal private links.');
guest.window.MiqBlogAccountShell.applyState({ loggedIn: true, displayName: 'Authenticated User', unreadNotifications: 123 });
assert(guest.accountItem.classList.contains('is-authenticated'));
assert.strictEqual(guest.accountTrigger.getAttribute('aria-haspopup'), 'true');
assert.strictEqual(guest.accountName.textContent, 'Authenticated User');
assert.strictEqual(guest.accountBadge.textContent, '99+');
assert.strictEqual(guest.menuBadge.textContent, '99+');
assert.strictEqual(guest.accountBadge.hidden, false);
assert.strictEqual(guest.accountMenu.hidden, true, 'A hydrated mobile account menu stays closed until the user opens it.');
click(guest.accountTrigger);
assert(guest.accountItem.classList.contains('is-open'));
assert.strictEqual(guest.accountMenu.hidden, false);

console.log('Blog mobile menu regression checks passed.');
