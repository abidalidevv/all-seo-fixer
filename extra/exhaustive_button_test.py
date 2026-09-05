import os
import re

views_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\admin\views"
js_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\assets\js\admin.js"
includes_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\includes"

with open(js_path, "r", encoding="utf-8") as f:
    js_text = f.read()

# Gather all registered wp_ajax_ actions from includes
all_ajax_actions = set()
for fname in os.listdir(includes_dir):
    if not fname.endswith(".php"): continue
    with open(os.path.join(includes_dir, fname), "r", encoding="utf-8") as f:
        content = f.read()
    actions = re.findall(r"wp_ajax_([a-zA-Z0-9_-]+)", content)
    all_ajax_actions.update(actions)

print(f"Total Registered wp_ajax_ actions in PHP: {len(all_ajax_actions)}")

button_report = []

for vname in sorted(os.listdir(views_dir)):
    if not vname.endswith(".php"): continue
    vpath = os.path.join(views_dir, vname)
    with open(vpath, "r", encoding="utf-8") as f:
        vcontent = f.read()
        
    # Find all <button>, <input type="submit">, and actionable <a> tags
    buttons = re.findall(r'(<(?:button|input\s+type=[\"\'](?:submit|button)[\"\']|a\s+[^>]*class=[\"\'][^\"\']*button[^\"\']*[\"\'])[^>]*>(?:.*?</(?:button|a)>)?)', vcontent, re.I | re.DOTALL)
    
    for raw_btn in buttons:
        # extract tag info
        id_match = re.search(r'id=[\"\']([^\"\']+)[\"\']', raw_btn, re.I)
        class_match = re.search(r'class=[\"\']([^\"\']+)[\"\']', raw_btn, re.I)
        href_match = re.search(r'href=[\"\']([^\"\']+)[\"\']', raw_btn, re.I)
        type_match = re.search(r'type=[\"\']([^\"\']+)[\"\']', raw_btn, re.I)
        name_match = re.search(r'name=[\"\']([^\"\']+)[\"\']', raw_btn, re.I)
        text_match = re.search(r'>([^<]+)<', raw_btn)
        
        btn_id = id_match.group(1) if id_match else None
        btn_class = class_match.group(1) if class_match else ""
        btn_href = href_match.group(1) if href_match else None
        btn_type = type_match.group(1) if type_match else "button"
        btn_name = name_match.group(1) if name_match else None
        btn_text = text_match.group(1).strip() if text_match else (btn_id or "Button")
        
        # Check how this button is handled:
        # 1. Direct href link (e.g. admin.php?page=... or external test link)
        is_direct_link = bool(btn_href and not btn_href.startswith('#') and 'javascript' not in btn_href)
        
        # 2. Form submit button
        is_form_submit = (btn_type.lower() == 'submit')
        
        # 3. JavaScript event listener
        js_wired = False
        js_action = None
        
        if btn_id:
            # check id in JS
            patterns = [
                rf"['\"]#{btn_id}['\"]",
                rf"['\"]\.{btn_id}['\"]",
                rf"#{btn_id}\b",
                rf"id\s*===\s*['\"]{btn_id}['\"]"
            ]
            for p in patterns:
                if re.search(p, js_text):
                    js_wired = True
                    break
                    
        if not js_wired and btn_class:
            for cls in btn_class.split():
                if cls in ['button', 'button-primary', 'button-secondary', 'button-small', 'asf-btn-xs', 'asf-btn-danger', 'widefat']:
                    continue
                if re.search(rf"['\"]\b\.{cls}\b['\"]", js_text) or re.search(rf"\.{cls}\b", js_text):
                    js_wired = True
                    break
        
        # Determine status
        status = "UNKNOWN"
        notes = ""
        
        if is_direct_link:
            status = "DIRECT_LINK"
            notes = f"Navigates to: {btn_href}"
        elif is_form_submit:
            status = "FORM_SUBMIT"
            notes = f"Submits POST form (name='{btn_name}')"
        elif js_wired:
            status = "JS_WIRED"
            notes = "Bound in assets/js/admin.js"
        else:
            status = "NOT_WIRED_OR_STATIC"
            notes = "No matching JS listener or form found!"
            
        button_report.append({
            "view": vname,
            "id": btn_id,
            "class": btn_class,
            "text": btn_text,
            "status": status,
            "notes": notes,
            "raw": raw_btn[:120].replace('\n', ' ')
        })

print(f"Total Actionable Buttons Found across all views: {len(button_report)}")

unwired = [b for b in button_report if b['status'] == 'NOT_WIRED_OR_STATIC']
print(f"Potentially Unwired / Broken Buttons: {len(unwired)}")

for u in unwired:
    print(f"\n[UNWIRED] View: {u['view']}")
    print(f"  Text: {u['text']}")
    print(f"  ID: {u['id']}, Class: {u['class']}")
    print(f"  Raw: {u['raw']}")
