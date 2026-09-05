const fs = require('fs');
const vm = require('vm');

// Create a DOM environment simulating WordPress Admin with settings.php or geo-hub.php
function createEnv(pageSlug) {
    const listeners = [];
    const elements = {};

    function getEl(sel) {
        if (!elements[sel]) {
            elements[sel] = {
                sel: sel,
                length: (
                    (typeof sel === 'string' && pageSlug === 'asf-settings' && (sel.includes('tab') || sel.includes('settings') || sel === '#asf_active_tab')) ||
                    (typeof sel === 'string' && pageSlug === 'asf-geo' && (sel.includes('geo') || sel.includes('llms'))) ||
                    sel === 'document' || sel === 'body'
                ) ? 1 : 0,
                on: function(evt, s, fn) {
                    if (typeof s === 'function') { fn = s; s = null; }
                    listeners.push({ evt, sel: s || sel, fn });
                    return this;
                },
                click: function(fn) { return this.on('click', fn); },
                ready: function(fn) { fn(); return this; },
                trigger: function(evt) {
                    // find listener
                    console.log(`[TRIGGER] Event '${evt}' triggered on '${sel}'`);
                    listeners.filter(l => l.evt === evt && (l.sel === sel || (sel && sel.includes(l.sel)))).forEach(l => {
                        try {
                            l.fn.call(this, { preventDefault: () => {} });
                        } catch(err) {
                            console.error(`[ERROR IN LISTENER] on '${l.sel}':`, err);
                        }
                    });
                    return this;
                },
                find: function(s) { return getEl(s); },
                html: function(h) { return this; },
                text: function(t) { return this; },
                val: function(v) { return v !== undefined ? this : ''; },
                prop: function(p, v) { return v !== undefined ? this : true; },
                is: function(s) { return true; },
                addClass: function(c) { return this; },
                removeClass: function(c) { return this; },
                show: function() { return this; },
                hide: function() { return this; },
                empty: function() { return this; },
                append: function(h) { return this; },
                data: function(k) { return k === 'tab' ? 'ai' : ''; },
                css: function() { return this; },
                fadeOut: function() { return this; }
            };
        }
        return elements[sel];
    }

    const $ = function(sel) {
        if (typeof sel === 'function') {
            sel();
            return;
        }
        return getEl(sel);
    };
    $.extend = Object.assign;
    $.ajax = function(opts) {
        console.log(`[AJAX CALL] action: ${opts.data ? opts.data.action : 'none'}, url: ${opts.url}`);
        return {
            done: function(cb) {
                // simulate successful response
                setTimeout(() => cb({ success: true, data: { score: 95, grade: 'A', checks: [] }, message: 'Saved successfully' }), 10);
                return this;
            },
            fail: function(cb) { return this; },
            always: function(cb) { return this; }
        };
    };

    const sandbox = {
        console: console,
        setTimeout: setTimeout,
        clearTimeout: clearTimeout,
        window: {
            location: { origin: 'http://localhost', hash: '', replaceState: () => {} },
            ajaxurl: 'http://localhost/wp-admin/admin-ajax.php',
            asfData: {
                ajax: 'http://localhost/wp-admin/admin-ajax.php',
                nonce: 'test_nonce',
                adminUrl: 'http://localhost/wp-admin/',
                siteUrl: 'http://localhost',
                hasPsiKey: '0',
                lastAudit: null
            }
        },
        document: getEl('document'),
        jQuery: $,
        $: $,
        navigator: { clipboard: { writeText: () => Promise.resolve() } },
        confirm: () => true
    };
    sandbox.window.document = sandbox.document;

    return { sandbox, listeners, getEl };
}

const jsCode = fs.readFileSync('assets/js/admin.js', 'utf8');

console.log("=== SIMULATING SETTINGS PAGE ===");
const settingsEnv = createEnv('asf-settings');
try {
    vm.runInNewContext(jsCode, settingsEnv.sandbox);
    console.log(`Settings page loaded! Total listeners registered: ${settingsEnv.listeners.length}`);
    
    // Test clicking a settings tab (.asf-tab-nav)
    console.log("Testing click on .asf-tab-nav...");
    const tabNav = settingsEnv.getEl('.asf-tab-nav');
    const tabListeners = settingsEnv.listeners.filter(l => l.sel && l.sel.includes('asf-tab-nav'));
    console.log(`Found ${tabListeners.length} tab listeners.`);
    if (tabListeners.length > 0) {
        tabListeners[0].fn.call({ sel: '.asf-tab-nav', data: () => 'ai' }, { preventDefault: () => {} });
        console.log("Tab click handled successfully!");
    } else {
        console.error("NO TAB LISTENER REGISTERED!");
    }
} catch (e) {
    console.error("ERROR on Settings page:", e);
}

console.log("\n=== SIMULATING GEO HUB PAGE ===");
const geoEnv = createEnv('asf-geo');
try {
    vm.runInNewContext(jsCode, geoEnv.sandbox);
    console.log(`GEO page loaded! Total listeners registered: ${geoEnv.listeners.length}`);
    
    // Test clicking #asf-geo-audit-btn
    console.log("Testing click on #asf-geo-audit-btn...");
    const geoListeners = geoEnv.listeners.filter(l => l.sel && l.sel.includes('asf-geo-audit-btn'));
    console.log(`Found ${geoListeners.length} geo audit listeners.`);
    if (geoListeners.length > 0) {
        geoListeners[0].fn.call({ sel: '#asf-geo-audit-btn' }, { preventDefault: () => {} });
        console.log("GEO audit click handled successfully!");
    } else {
        console.error("NO GEO AUDIT LISTENER REGISTERED!");
    }

    // Test clicking #asf-save-geo-bots-btn
    console.log("Testing click on #asf-save-geo-bots-btn...");
    const botListeners = geoEnv.listeners.filter(l => l.sel && l.sel.includes('asf-save-geo-bots-btn'));
    console.log(`Found ${botListeners.length} geo bot listeners.`);
    if (botListeners.length > 0) {
        botListeners[0].fn.call({ sel: '#asf-save-geo-bots-btn' }, { preventDefault: () => {} });
        console.log("GEO bots click handled successfully!");
    } else {
        console.error("NO GEO BOTS LISTENER REGISTERED!");
    }
} catch (e) {
    console.error("ERROR on GEO page:", e);
}
