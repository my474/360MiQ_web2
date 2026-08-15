'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const mainThemeScript = read('assets/js/theme.js');
const blogThemeScript = read('blog/wp-content/mu-plugins/360miq-theme-sync.js');
const metaSource = read('meta.php');
const accountApiSource = read('account_api.php');

assert.match(mainThemeScript, /window\.addEventListener\('storage'/);
assert.match(blogThemeScript, /preferences\.action = 'save_preferences'/);
assert.match(blogThemeScript, /preferences\.theme_mode/);
assert.match(accountApiSource, /'preferences' => \$viewer \? miq_account_user_preferences/);
assert.match(metaSource, /else if\(loggedIn\)\{dark=!!\(window\.matchMedia/);

function createMainRuntime(initialTheme, prefersDark) {
    let theme = initialTheme;
    const events = [];
    const windowListeners = {};
    const html = {
        getAttribute(name) { return name === 'data-theme' ? theme : null; },
        setAttribute(name, value) { if (name === 'data-theme') theme = value; },
        dispatchEvent(event) { events.push(event); }
    };
    const document = {
        documentElement: html,
        readyState: 'complete',
        querySelector() { return null; },
        getElementById() { return null; }
    };
    const storage = {
        getItem() { return null; },
        setItem() {}
    };
    const window = {
        document,
        localStorage: storage,
        matchMedia() { return { matches: !!prefersDark }; },
        addEventListener(type, listener) {
            windowListeners[type] = listener;
        }
    };

    class ThemeEvent {
        constructor(type, options) {
            this.type = type;
            this.detail = options.detail;
        }
    }

    vm.runInNewContext(mainThemeScript, {
        window,
        document,
        localStorage: storage,
        CustomEvent: ThemeEvent
    });

    return {
        window,
        events,
        currentTheme() { return theme; },
        dispatchStorage(newValue) {
            windowListeners.storage({ key: '360miq-dark-mode', newValue });
        }
    };
}

const initialLight = createMainRuntime('light', false);
assert.strictEqual(initialLight.window.ThemeController.isDark(), false);
initialLight.dispatchStorage('true');
assert.strictEqual(initialLight.currentTheme(), 'dark');
initialLight.dispatchStorage('false');
assert.strictEqual(initialLight.currentTheme(), 'light');
assert.deepStrictEqual(
    initialLight.events.map((event) => event.detail.theme),
    ['dark', 'light']
);

const initialDark = createMainRuntime('dark', false);
assert.strictEqual(initialDark.window.ThemeController.isDark(), true);
initialDark.dispatchStorage(null);
assert.strictEqual(initialDark.currentTheme(), 'light', 'clearing the shared key falls back to the current device preference');

console.log('Theme synchronization regression checks passed.');
