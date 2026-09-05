import re

js_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\assets\js\admin.js"
includes_path = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\includes"

with open(js_path, "r", encoding="utf-8") as f:
    js = f.read()

# Find all click bindings: $(document).on('click', '...', ...) or $('...').on('click', ...) or $('...').click(...)
click_handlers = re.findall(r"(?:\$\(document\)\.on\(\s*['\"]click['\"]\s*,\s*['\"]([^'\"]+)['\"]|\$\(['\"]([^'\"]+)['\"]\)\.(?:on\(\s*['\"]click['\"]|click))", js)

flat_selectors = []
for h1, h2 in click_handlers:
    sel = h1 or h2
    flat_selectors.append(sel)

print(f"Total Click Event Listeners in admin.js: {len(flat_selectors)}")
print("\nUnique Click Listeners Registered in JS:")
for sel in sorted(set(flat_selectors)):
    print(f"  - {sel}")
