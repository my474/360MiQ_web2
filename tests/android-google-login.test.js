'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const webClientId = '735181786268-s7n2c9fdg268labp2estg8au267c3m0r.apps.googleusercontent.com';
const androidClientId = '735181786268-hm8qm7v6mml4ctthtm2pdfpmp1d2k396.apps.googleusercontent.com';

const accountPage = read('account.php');
const auth = read('account/auth.php');
const config = read('account/config.php');
const endpoint = read('account_android_google.php');
const accountScript = read('assets/js/account.js');
const accountStyles = read('assets/css/account.css');

assert(config.includes(webClientId));
assert(!config.includes(androidClientId), 'The Android OAuth client ID must not be a website audience');
assert.match(config, /MIQ_NATIVE_GOOGLE_CHALLENGE_TTL/);
assert.match(auth, /function miq_account_issue_native_google_challenge/);
assert.match(auth, /'nonce_hash'\s*=>\s*hash\('sha256', \$nonce\)/);
assert.match(auth, /unset\(\$_SESSION\['miq_native_google_challenge'\]\)/);
assert.match(auth, /hash_equals\(\(string\) \$challenge\['nonce_hash'\], hash\('sha256', \$nonce\)\)/);
assert.match(auth, /accounts\.google\.com.*https:\/\/accounts\.google\.com/s);
assert.match(auth, /hash_equals\(\$expected_nonce, \$returned_nonce\)/);
assert.match(auth, /CURLOPT_POST\s*=>\s*true/);
assert.match(auth, /CURLOPT_POSTFIELDS\s*=>\s*http_build_query/);
assert.doesNotMatch(auth, /google_tokeninfo_url'\]\s*\.\s*'\?id_token='/);
assert.match(endpoint, /\$request_method !== 'POST'/);
assert.match(endpoint, /Cache-Control: no-store, private/);
assert.match(endpoint, /Referrer-Policy: no-referrer/);
assert.match(endpoint, /miq_account_require_rate_limit\('login_ip'/);
assert.match(endpoint, /miq_account_consume_native_google_challenge/);
assert.match(endpoint, /miq_account_process_google_login[^;]+\$challenge\['nonce'\]/s);
assert.doesNotMatch(endpoint, /\$_GET\['credential'\]/);
assert.match(accountPage, /data-native-google-login/);
assert.match(accountPage, /account_android_google\.php\?state=/);
assert.match(accountStyles, /\.miq-native-google-login\[hidden\]\s*\{[^}]*display:\s*none\s*!important/s);
assert.match(accountStyles, /\.miq-native-google-login\s*\{[^}]*background:\s*#fff;[^}]*color:\s*#1f1f1f/s);
assert.match(accountStyles, /html\[data-theme="dark"\]\s+\.miq-native-google-login\s*\{[^}]*background:\s*#0b57d0;[^}]*color:\s*#fff/s);

function createElement(initiallyHidden) {
    const attributes = new Map();
    if (initiallyHidden) attributes.set('hidden', 'hidden');
    return {
        get hidden() { return attributes.has('hidden'); },
        getAttribute(name) { return attributes.has(name) ? attributes.get(name) : null; },
        setAttribute(name, value) { attributes.set(name, String(value)); },
        removeAttribute(name) { attributes.delete(name); },
        addEventListener() {},
        querySelector() { return null; }
    };
}

function createRuntime(userAgent, initialTheme) {
    const webForm = createElement(false);
    const nativeLink = createElement(true);
    const htmlAttributes = new Map([['data-theme', initialTheme]]);
    let readyHandler = null;
    let themeHandler = null;

    const documentElement = {
        getAttribute(name) { return htmlAttributes.has(name) ? htmlAttributes.get(name) : null; },
        setAttribute(name, value) { htmlAttributes.set(name, String(value)); },
        addEventListener(type, handler) { if (type === 'themechange') themeHandler = handler; }
    };
    const document = {
        documentElement,
        querySelector() { return null; },
        querySelectorAll(selector) {
            if (selector === '.miq-google-form') return [webForm];
            if (selector === '[data-native-google-login]') return [nativeLink];
            return [];
        },
        getElementById() { return null; },
        addEventListener(type, handler) { if (type === 'DOMContentLoaded') readyHandler = handler; }
    };
    const window = {
        __MIQ_ACCOUNT__: { loggedIn: false },
        location: { search: '', pathname: '/account.php' },
        navigator: { userAgent },
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

    return {
        webForm,
        nativeLink,
        surface() { return htmlAttributes.get('data-miq-google-surface'); },
        setTheme(theme) { htmlAttributes.set('data-theme', theme); },
        dispatchTheme(theme) {
            assert.strictEqual(typeof themeHandler, 'function');
            themeHandler({ detail: { theme } });
        }
    };
}

const webViewUa = 'Mozilla/5.0 (Linux; Android 14; Pixel 8 Pro Build/AP1A; wv) AppleWebKit/537.36 Version/4.0 Chrome/126 Mobile Safari/537.36';
const mobileBrowserUa = 'Mozilla/5.0 (Linux; Android 14; Pixel 8 Pro) AppleWebKit/537.36 Chrome/126 Mobile Safari/537.36';
const officialAppUa = webViewUa + ' 360MiQAndroid/1.05';

const initialLight = createRuntime(officialAppUa, 'light');
assert.strictEqual(initialLight.webForm.hidden, true);
assert.strictEqual(initialLight.nativeLink.hidden, false);
assert.strictEqual(initialLight.surface(), 'native');

const initialDark = createRuntime(officialAppUa, 'dark');
assert.strictEqual(initialDark.webForm.hidden, true);
assert.strictEqual(initialDark.nativeLink.hidden, false);
assert.strictEqual(initialDark.surface(), 'native');

initialLight.setTheme('dark');
initialLight.dispatchTheme('dark');
assert.strictEqual(initialLight.nativeLink.hidden, false);
assert.strictEqual(initialLight.surface(), 'native');
initialLight.setTheme('light');
initialLight.dispatchTheme('light');
assert.strictEqual(initialLight.nativeLink.hidden, false);
assert.strictEqual(initialLight.surface(), 'native');

const genericWebView = createRuntime(webViewUa, 'light');
assert.strictEqual(genericWebView.webForm.hidden, false);
assert.strictEqual(genericWebView.nativeLink.hidden, true);
assert.strictEqual(genericWebView.surface(), 'web');

const mobileBrowser = createRuntime(mobileBrowserUa, 'light');
assert.strictEqual(mobileBrowser.webForm.hidden, false);
assert.strictEqual(mobileBrowser.nativeLink.hidden, true);
assert.strictEqual(mobileBrowser.surface(), 'web');

const customWrapper = createRuntime(mobileBrowserUa + ' 360MiQAndroid/1.0', 'light');
assert.strictEqual(customWrapper.webForm.hidden, true);
assert.strictEqual(customWrapper.nativeLink.hidden, false);
assert.strictEqual(customWrapper.surface(), 'native');

const unversionedWrapper = createRuntime(mobileBrowserUa + ' 360MiQAndroid', 'light');
assert.strictEqual(unversionedWrapper.webForm.hidden, false);
assert.strictEqual(unversionedWrapper.nativeLink.hidden, true);
assert.strictEqual(unversionedWrapper.surface(), 'web');

const lookalikeWrapper = createRuntime(mobileBrowserUa + ' Not360MiQAndroid/1.05', 'light');
assert.strictEqual(lookalikeWrapper.webForm.hidden, false);
assert.strictEqual(lookalikeWrapper.nativeLink.hidden, true);
assert.strictEqual(lookalikeWrapper.surface(), 'web');

console.log('Android Google login handoff regression checks passed.');
