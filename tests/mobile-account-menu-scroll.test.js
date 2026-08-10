const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');

const accountStyles = read('assets/css/account.css');
const footer = read('footer.php');
const header = read('header.php');

const mobileCollapseRule = accountStyles.match(
    /\.clean-navbar #navbarSupportedContent \{([\s\S]*?)\n    \}/
);
assert(mobileCollapseRule, 'The expanded mobile navbar must have a bounded scroll container.');
assert.match(mobileCollapseRule[1], /max-height:\s*calc\(100dvh - 74px\)/);
assert.match(mobileCollapseRule[1], /overflow-y:\s*auto/);
assert.match(mobileCollapseRule[1], /overscroll-behavior-y:\s*contain/);
assert.match(mobileCollapseRule[1], /-webkit-overflow-scrolling:\s*touch/);
assert.match(mobileCollapseRule[1], /touch-action:\s*pan-y pinch-zoom/);

const phoneCollapseRule = accountStyles.match(
    /@media \(max-width: 639px\) \{\s*\.clean-navbar #navbarSupportedContent \{([\s\S]*?)\n    \}/
);
assert(phoneCollapseRule, 'Phone navigation must reserve room for the fixed bottom navigation.');
assert.match(phoneCollapseRule[1], /100dvh - 134px - env\(safe-area-inset-bottom, 0px\)/);
assert.match(phoneCollapseRule[1], /scroll-padding-bottom:/);

assert.match(
    footer,
    /function mobileNavigationIsExpanded\(\)[\s\S]*?#navbarSupportedContent\.show[\s\S]*?\.miq-account-nav-item\.show/
);
assert.match(
    footer,
    /if \(mobileNavigationIsExpanded\(\)\) \{[\s\S]*?navbar\.classList\.remove\('nav-hidden'\)[\s\S]*?return;/
);
assert(
    footer.indexOf('if (mobileNavigationIsExpanded())') < footer.indexOf("navbar.classList.add('nav-hidden')"),
    'The open-menu guard must run before the global scroll-hide behavior.'
);

assert.match(header, /assets\/css\/account\.css\?v=20260811\.1/);

const scrollScriptMatch = footer.match(/\/\/ Scroll Animation Logic\s*([\s\S]*?)\n<\/script>/);
assert(scrollScriptMatch, 'The shared mobile navigation behavior must remain executable.');

function fakeElement() {
    const classes = new Set();
    return {
        classes,
        classList: {
            add(name) { classes.add(name); },
            remove(name) { classes.delete(name); },
            contains(name) { return classes.has(name); }
        },
        querySelector() { return null; }
    };
}

function verifyThemeScenario(initialTheme, liveThemes) {
    let accountMenuOpen = true;
    let closeSheetCalls = 0;
    const listeners = {};
    const navbar = fakeElement();
    const bottomNav = fakeElement();
    const chatbot = fakeElement();
    const topcontrol = fakeElement();
    navbar.querySelector = () => accountMenuOpen ? {} : null;

    const windowObject = {
        innerWidth: 390,
        pageYOffset: 180,
        addEventListener(type, listener) { listeners[type] = listener; }
    };
    const documentObject = {
        documentElement: { scrollTop: 0, dataset: { theme: initialTheme } },
        querySelector(selector) {
            if (selector === '.navbar') return navbar;
            if (selector === '.bottom-nav') return bottomNav;
            if (selector === '.chatbot') return chatbot;
            return null;
        },
        getElementById(id) { return id === 'topcontrol' ? topcontrol : null; }
    };

    vm.runInNewContext(scrollScriptMatch[1], {
        window: windowObject,
        document: documentObject,
        closeSheet() { closeSheetCalls += 1; }
    });

    [initialTheme].concat(liveThemes || []).forEach((theme, index) => {
        documentObject.documentElement.dataset.theme = theme;
        windowObject.pageYOffset = 180 + (index * 10);
        listeners.scroll();
        assert(!navbar.classes.has('nav-hidden'), `${theme} mode must keep an open account menu visible.`);
        assert.strictEqual(closeSheetCalls, 0, `${theme} mode must not close navigation during menu scrolling.`);
    });

    accountMenuOpen = false;
    windowObject.pageYOffset += 60;
    listeners.scroll();
    assert(navbar.classes.has('nav-hidden'), `${initialTheme} mode must preserve normal page-scroll hiding after the menu closes.`);
    assert.strictEqual(closeSheetCalls, 1);
}

// Initial light, initial dark, and live light -> dark -> light all execute the
// same theme-independent scrolling path without a page reload.
verifyThemeScenario('light', ['dark', 'light']);
verifyThemeScenario('dark', []);
assert(!/data-theme[^\n]*\.clean-navbar #navbarSupportedContent/.test(accountStyles));

console.log('Mobile account menu scrolling checks passed.');
