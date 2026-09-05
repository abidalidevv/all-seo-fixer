import os
import re

base_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer"
js_path = os.path.join(base_dir, "assets", "js", "admin.js")
includes_dir = os.path.join(base_dir, "includes")

with open(js_path, "r", encoding="utf-8") as f:
    js = f.read()

# 1. Registered PHP actions
php_actions = set()
for fname in os.listdir(includes_dir):
    if not fname.endswith(".php"): continue
    with open(os.path.join(includes_dir, fname), "r", encoding="utf-8") as f:
        content = f.read()
    for m in re.finditer(r"add_action\(\s*['\"]wp_ajax_([a-zA-Z0-9_-]+)['\"]", content):
        php_actions.add(m.group(1))

# 2. JS requested actions
# Find ASF.request('action', ...) or action: '...'
js_actions = set()
for m in re.finditer(r"ASF\.request\(\s*['\"]([^'\"]+)['\"]", js):
    js_actions.add(m.group(1))

for m in re.finditer(r"['\"]?action['\"]?\s*:\s*['\"]([^'\"]+)['\"]", js):
    act = m.group(1)
    if act.startswith("asf_"):
        js_actions.add(act)

print(f"Total Unique AJAX Actions Called in JS: {len(js_actions)}")
print(f"Total Unique wp_ajax_ Actions Registered in PHP: {len(php_actions)}")

missing_in_php = js_actions - php_actions
print(f"Actions in JS but MISSING in PHP: {len(missing_in_php)}")
for a in sorted(missing_in_php):
    print(f"  ❌ Missing: {a}")

unused_in_js = php_actions - js_actions
print(f"\nActions registered in PHP but not called directly by name in JS: {len(unused_in_js)}")
for a in sorted(unused_in_js):
    print(f"  - Server-only / hook / fallback: {a}")
