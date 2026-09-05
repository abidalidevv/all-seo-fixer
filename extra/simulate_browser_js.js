const fs = require('fs');

// Simple DOM & jQuery mock
class ElementMock {
    constructor(sel) {
        this.sel = sel;
        this.length = (typeof sel === 'string' && (sel.includes('asf') || sel === 'body')) ? 1 : 1;
    }
    on(event, selector, handler) {
        if (typeof selector === 'function') {
            handler = selector;
            selector = null;
        }
        // console.log(`Registered event: ${event} on ${selector || this.sel}`);
        return this;
    }
    ready(fn) { fn(); return this; }
    click(handler) { return this.on('click', handler); }
    trigger(event) {
        // console.log(`Triggered event: ${event} on ${this.sel}`);
        return this;
    }
    find(s) { return new ElementMock(s); }
    html(h) { return this; }
    text(t) { return this; }
    val(v) { return v !== undefined ? this : ''; }
    prop(p, v) { return v !== undefined ? this : true; }
    is(s) { return true; }
    addClass(c) { return this; }
    removeClass(c) { return this; }
    show() { return this; }
    hide() { return this; }
    empty() { return this; }
    append(h) { return this; }
    data(k) { return 'general'; }
    css() { return this; }
}

const $ = function(sel) {
    if (typeof sel === 'function') {
        // $(document).ready callback
        setTimeout(sel, 0);
        return;
    }
    return new ElementMock(sel);
};

$.extend = Object.assign;
$.ajax = function(opts) {
    return {
        done: function(cb) { return this; },
        fail: function(cb) { return this; },
        always: function(cb) { return this; }
    };
};

global.window = {
    location: { origin: 'http://localhost', hash: '#tab-general' },
    asfData: {
        ajax: 'http://localhost/wp-admin/admin-ajax.php',
        nonce: '12345',
        adminUrl: 'http://localhost/wp-admin/',
        siteUrl: 'http://localhost',
        hasPsiKey: '1',
        lastAudit: null
    }
};
global.document = new ElementMock('document');
global.jQuery = $;
global.$ = $;
global.navigator = { clipboard: { writeText: () => Promise.resolve() } };

console.log("Loading assets/js/admin.js in simulated browser environment...");

try {
    const code = fs.readFileSync('assets/js/admin.js', 'utf8');
    eval(code);
    console.log("admin.js evaluated successfully without throwing errors!");
} catch (e) {
    console.error("FATAL ERROR in admin.js during execution:", e);
}
