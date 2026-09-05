import os
import re

views_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\admin\views"
js_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\assets\js\admin.js"

with open(js_path, "r", encoding="utf-8") as f:
    js_content = f.read()

generic_classes = {'button', 'button-primary', 'button-secondary', 'button-small', 'asf-btn-xs', 'asf-btn-danger', 'asf-select', 'asf-input', 'regular-text', 'large-text'}

for fname in sorted(os.listdir(views_dir)):
    if not fname.endswith(".php"): continue
    with open(os.path.join(views_dir, fname), "r", encoding="utf-8") as f:
        content = f.read()
    
    buttons = re.findall(r'<button\s+([^>]+)>', content, re.I)
    print(f"\n=== View: {fname} (Buttons: {len(buttons)}) ===")
    for b in buttons:
        id_match = re.search(r'id=["\']([^"\']+)["\']', b)
        class_match = re.search(r'class=["\']([^"\']+)["\']', b)
        btn_id = id_match.group(1) if id_match else None
        btn_class = class_match.group(1) if class_match else ""
        
        specific_classes = [c for c in btn_class.split() if c not in generic_classes]
        
        found = False
        if btn_id and (f"#{btn_id}" in js_content or f"'{btn_id}'" in js_content or f'"{btn_id}"' in js_content or btn_id in js_content):
            found = True
        if not found and specific_classes:
            for cls in specific_classes:
                if f".{cls}" in js_content or f"'{cls}'" in js_content or f'"{cls}"' in js_content or cls in js_content:
                    found = True
                    break
        
        if not found:
            print(f"  [NOT WIRED] id={btn_id} specific_classes={specific_classes}")
        else:
            print(f"  [OK] id={btn_id} specific_classes={specific_classes}")
