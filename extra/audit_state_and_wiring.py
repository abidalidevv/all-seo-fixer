import os
import re

views_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\admin\views"
js_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\assets\js\admin.js"
handlers_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\includes\class-asf-handlers.php"
core_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\includes\class-asf-core.php"

with open(js_path, "r", encoding="utf-8") as f:
    js_content = f.read()

with open(handlers_path, "r", encoding="utf-8") as f:
    handlers_content = f.read()

with open(core_path, "r", encoding="utf-8") as f:
    core_content = f.read()

print("================ AUDIT OF ALL VIEWS ================")
unwired_buttons = []
for fname in sorted(os.listdir(views_dir)):
    if not fname.endswith(".php"):
        continue
    fpath = os.path.join(views_dir, fname)
    with open(fpath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # check forms
    forms = re.findall(r'<form[^>]*>', content, re.I)
    btn_ids = re.findall(r'id=["\'](asf-[a-zA-Z0-9_-]+)["\']', content)
    btn_classes = re.findall(r'class=["\']([^"\']*asf-[a-zA-Z0-9_-]+[^"\']*)["\']', content)
    
    unwired_in_view = []
    for bid in btn_ids:
        # Check if ID is in JS or if it's an input/display element
        is_in_js = bid in js_content
        if not is_in_js:
            # check if it's a button or interactive element
            match = re.search(r'<(button|input|select|a)[^>]*id=["\']' + re.escape(bid) + r'["\']', content, re.I)
            if match:
                elem_tag = match.group(1)
                unwired_in_view.append((bid, elem_tag))
    
    print(f"\n--- View: {fname} ---")
    if forms:
        print(f"  [FORMS]: {len(forms)} HTML forms found")
    if unwired_in_view:
        print(f"  [POTENTIALLY UNWIRED INTERACTIVE ELEMENTS]: {len(unwired_in_view)}")
        for bid, tag in unwired_in_view:
            print(f"    - <{tag} id=\"{bid}\"> NOT FOUND in admin.js")
            unwired_buttons.append((fname, bid, tag))
    else:
        print("  All interactive elements referenced or view is static/display.")

print("\n================ TOTAL UNWIRED ================")
print(f"Total: {len(unwired_buttons)}")
for fname, bid, tag in unwired_buttons:
    print(f"  {fname} -> <{tag} id=\"{bid}\">")
