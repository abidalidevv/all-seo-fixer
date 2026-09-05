import os
import re

js_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\assets\js\admin.js"
includes_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\includes"

with open(js_path, "r", encoding="utf-8") as f:
    js_content = f.read()

# Find all ASF.request('...') and ASF.post('...') and $.post(ajaxurl, {action: '...'})
js_requests = set(re.findall(r"ASF\.(?:request|post)\(\s*['\"]([^'\"]+)['\"]", js_content))
js_requests.update(re.findall(r"action:\s*['\"]([^'\"]+)['\"]", js_content))

# Now find all add_action( 'wp_ajax_...', ... ) in all includes files
backend_actions = set()
for fname in os.listdir(includes_dir):
    if not fname.endswith(".php"): continue
    with open(os.path.join(includes_dir, fname), "r", encoding="utf-8") as f:
        content = f.read()
    actions = re.findall(r"wp_ajax_([a-zA-Z0-9_-]+)", content)
    backend_actions.update(actions)

print(f"Total Unique AJAX requests in JS: {len(js_requests)}")
print(f"Total Registered wp_ajax_ actions in PHP: {len(backend_actions)}")

missing_in_php = []
for req in sorted(js_requests):
    # Some requests might be dynamic or standard wp actions
    if req not in backend_actions:
        missing_in_php.append(req)

print(f"\nRequests in JS that have NO matching wp_ajax_ in PHP ({len(missing_in_php)}):")
for req in missing_in_php:
    print(f"  - {req}")
