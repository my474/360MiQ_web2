'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const accountPage = read('account.php');
const accountScript = read('assets/js/account.js');
const accountStyles = read('assets/css/account.css');
const workspaceStyles = read('assets/css/workspace.css');
const themeStyles = read('assets/css/theme.css');
const toolPage = read('tool.php');
const themeScript = read('assets/js/theme.js');
const passwordIds = ['register_password', 'confirm_password', 'reset_password', 'login_password'];

passwordIds.forEach((id) => {
    assert.match(accountPage, new RegExp(`data-password-toggle="${id}"`));
    assert.match(accountPage, new RegExp(`aria-controls="${id}"`));
});
assert.strictEqual((accountPage.match(/data-password-toggle=/g) || []).length, passwordIds.length);

assert.match(accountStyles, /\.miq-password-field\s*>\s*\.form-control\s*\{[^}]*padding-right:\s*46px/s);
assert.match(accountStyles, /\.miq-password-toggle\s*\{[^}]*color:\s*#667085/s);
assert.match(accountStyles, /html\[data-theme="dark"\]\s+\.miq-password-toggle\s*\{[^}]*color:\s*#c5c9d6/s);

[accountStyles, workspaceStyles, themeStyles, toolPage].forEach((styles) => {
    assert.match(styles, /:-webkit-autofill/);
    assert.match(styles, /:autofill/);
    assert.match(styles, /-webkit-text-fill-color:\s*[^;]+!important/);
    assert.match(styles, /box-shadow:\s*0 0 0 1000px [^;]+ inset !important/);
});
assert.match(themeStyles, /color-scheme:\s*dark/);
assert.match(themeScript, /htmlEl\.setAttribute\('data-theme', theme\)/);

function createThemeRuntime(initialTheme) {
    let theme = initialTheme;
    const events = [];
    const themeDocumentElement = {
        getAttribute(name) { return name === 'data-theme' ? theme : null; },
        setAttribute(name, value) { if (name === 'data-theme') theme = value; },
        dispatchEvent(event) { events.push(event); }
    };
    const themeDocument = {
        documentElement: themeDocumentElement,
        readyState: 'complete',
        querySelector() { return null; },
        getElementById() { return null; }
    };
    const themeStorage = {
        getItem() { return null; },
        setItem() {}
    };
    const themeWindow = { document: themeDocument, localStorage: themeStorage };

    class ThemeEvent {
        constructor(type, options) {
            this.type = type;
            this.detail = options.detail;
        }
    }

    vm.runInNewContext(themeScript, {
        window: themeWindow,
        document: themeDocument,
        localStorage: themeStorage,
        CustomEvent: ThemeEvent
    });

    return {
        window: themeWindow,
        events,
        currentTheme() { return theme; }
    };
}

const initialLight = createThemeRuntime('light');
assert.strictEqual(initialLight.window.ThemeController.isDark(), false);

const initialDark = createThemeRuntime('dark');
assert.strictEqual(initialDark.window.ThemeController.isDark(), true);

initialLight.window.ThemeController.toggle();
assert.strictEqual(initialLight.currentTheme(), 'dark');
initialLight.window.ThemeController.toggle();
assert.strictEqual(initialLight.currentTheme(), 'light');
assert.deepStrictEqual(
    initialLight.events.map((event) => event.detail.theme),
    ['dark', 'light']
);

let readyHandler = null;
let clickHandler = null;
const attributes = {
    'data-password-toggle': 'login_password',
    'aria-controls': 'login_password',
    'aria-label': 'Show password',
    'aria-pressed': 'false'
};
const iconClasses = new Set(['fas', 'fa-eye-slash']);
const icon = {
    classList: {
        toggle(name, force) {
            if (force) iconClasses.add(name);
            else iconClasses.delete(name);
        }
    }
};
const input = { type: 'password' };
const button = {
    title: 'Show password',
    getAttribute(name) { return attributes[name] || null; },
    setAttribute(name, value) { attributes[name] = String(value); },
    addEventListener(type, handler) { if (type === 'click') clickHandler = handler; },
    querySelector(selector) { return selector === 'i' ? icon : null; }
};
const documentElement = {
    addEventListener() {},
    getAttribute(name) { return name === 'data-theme' ? 'light' : null; }
};
const document = {
    documentElement,
    querySelector() { return null; },
    querySelectorAll(selector) { return selector === '[data-password-toggle]' ? [button] : []; },
    getElementById(id) { return id === 'login_password' ? input : null; },
    addEventListener(type, handler) { if (type === 'DOMContentLoaded') readyHandler = handler; }
};
const window = {
    __MIQ_ACCOUNT__: { loggedIn: false },
    location: { search: '', pathname: '/account.php' },
    localStorage: { getItem() { return null; }, setItem() {}, removeItem() {} },
    document,
    console: { info() {} },
    clearTimeout() {},
    setTimeout() {}
};

vm.runInNewContext(accountScript, {
    window,
    document,
    URLSearchParams,
    Uint8Array,
    Date,
    Math,
    Promise,
    Array,
    Object,
    String,
    Number,
    JSON,
    encodeURIComponent
});

assert.strictEqual(typeof readyHandler, 'function');
readyHandler();
assert.strictEqual(typeof clickHandler, 'function');

clickHandler();
assert.strictEqual(input.type, 'text');
assert.strictEqual(attributes['aria-pressed'], 'true');
assert.strictEqual(attributes['aria-label'], 'Hide password');
assert.strictEqual(button.title, 'Hide password');
assert(iconClasses.has('fa-eye'));
assert(!iconClasses.has('fa-eye-slash'));

clickHandler();
assert.strictEqual(input.type, 'password');
assert.strictEqual(attributes['aria-pressed'], 'false');
assert.strictEqual(attributes['aria-label'], 'Show password');
assert.strictEqual(button.title, 'Show password');
assert(!iconClasses.has('fa-eye'));
assert(iconClasses.has('fa-eye-slash'));

console.log('Password visibility regression checks passed.');
