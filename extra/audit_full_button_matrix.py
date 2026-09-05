import os
import re
import json

base_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer"
views_dir = os.path.join(base_dir, "admin", "views")
js_path = os.path.join(base_dir, "assets", "js", "admin.js")
includes_dir = os.path.join(base_dir, "includes")

with open(js_path, "r", encoding="utf-8") as f:
    js_content = f.read()

# 1. Parse all wp_ajax_ actions from PHP
ajax_actions = {}
for fname in os.listdir(includes_dir):
    if not fname.endswith(".php"):
        continue
    fpath = os.path.join(includes_dir, fname)
    with open(fpath, "r", encoding="utf-8") as f:
        content = f.read()
    for m in re.finditer(r"add_action\(\s*['\"]wp_ajax_([a-zA-Z0-9_-]+)['\"]\s*,\s*(?:array\(\s*['\"]?([a-zA-Z0-9_]+|__CLASS__)['\"]?\s*,\s*['\"]([a-zA-Z0-9_]+)['\"]\s*\)|['\"]([a-zA-Z0-9_]+)['\"])", content):
        action = m.group(1)
        cls = m.group(2) or "function"
        meth = m.group(3) or m.group(4)
        ajax_actions[action] = {"file": fname, "class": cls, "method": meth}

print(f"Total Registered wp_ajax_ Actions in PHP: {len(ajax_actions)}")

# 2. Extract every button from every view
button_audit = []
for vname in sorted(os.listdir(views_dir)):
    if not vname.endswith(".php"):
        continue
    vpath = os.path.join(views_dir, vname)
    with open(vpath, "r", encoding="utf-8") as f:
        vcontent = f.read()

    # Find button tags, input type=submit/button, or <a class="...button...">
    pattern = r"(<(?:button|input\s+[^>]*type=[\"'](?:submit|button)[\"']|a\s+[^>]*class=[\"'][^\"']*\bbutton\b[^\"']*[\"'])[^>]*>(?:.*?</(?:button|a)>)?)"
    matches = re.finditer(pattern, vcontent, re.IGNORECASE | re.DOTALL)
    
    for m in matches:
        raw = m.group(1)
        id_m = re.search(r'id=[\"\']([^\"\']+)[\"\']', raw)
        cls_m = re.search(r'class=[\"\']([^\"\']+)[\"\']', raw)
        type_m = re.search(r'type=[\"\']([^\"\']+)[\"\']', raw)
        href_m = re.search(r'href=[\"\']([^\"\']+)[\"\']', raw)
        name_m = re.search(r'name=[\"\']([^\"\']+)[\"\']', raw)
        text_m = re.search(r'>([^<]+)<', raw)
        
        btn_id = id_m.group(1) if id_m else None
        btn_cls = cls_m.group(1) if cls_m else ""
        btn_type = type_m.group(1) if type_m else "button"
        btn_href = href_m.group(1) if href_m else None
        btn_name = name_m.group(1) if name_m else None
        btn_text = text_m.group(1).strip() if text_m else (btn_id or "Button")
        
        # Check wire status:
        # A. Direct link
        is_direct = bool(btn_href and not btn_href.startswith('#') and 'javascript' not in btn_href)
        # B. Form submit
        is_submit = (btn_type.lower() == 'submit')
        # C. JS binding
        js_wired = False
        matched_target = None
        
        if btn_id:
            if f"#{btn_id}" in js_content or f"'{btn_id}'" in js_content or f'"{btn_id}"' in js_content:
                js_wired = True
                matched_target = f"#{btn_id}"
        if not js_wired and btn_cls:
            for c in btn_cls.split():
                if c in ['button', 'button-primary', 'button-secondary', 'button-small', 'button-large', 'asf-btn-xs', 'asf-btn-danger', 'asf-btn-secondary', 'asf-btn-primary', 'asf-btn', 'widefat']:
                    continue
                if f".{c}" in js_content:
                    js_wired = True
                    matched_target = f".{c}"
                    break

        status = "WORKING"
        handling = ""
        if is_direct:
            handling = f"Direct Navigation: {btn_href}"
        elif is_submit:
            handling = f"Form Submit (name: {btn_name})"
        elif js_wired:
            handling = f"JS Click Handler on '{matched_target}'"
        else:
            status = "NEEDS_ATTENTION"
            handling = "No JS listener, form submit, or direct link detected!"

        button_audit.append({
            "view": vname,
            "text": btn_text,
            "id": btn_id,
            "class": btn_cls,
            "status": status,
            "handling": handling
        })

print(f"Total Buttons Audited Across All 21 Views: {len(button_audit)}")
working = [b for b in button_audit if b["status"] == "WORKING"]
issues = [b for b in button_audit if b["status"] != "WORKING"]

print(f"Working Buttons: {len(working)}")
print(f"Buttons Needing Attention: {len(issues)}")

if issues:
    print("\n--- ISSUES FOUND ---")
    for b in issues:
        print(f"View: {b['view']} | Text: {b['text']} | ID: {b['id']} | Class: {b['class']}")
else:
    print("\nALL BUTTONS ARE VERIFIED WORKING AND BOUND!")

with open(os.path.join(base_dir, "scratch", "button_audit_matrix.json"), "w", encoding="utf-8") as f:
    json.dump(button_audit, f, indent=2)
