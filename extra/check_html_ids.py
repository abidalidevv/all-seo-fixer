import re

with open(r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer\documentation.html", "r", encoding="utf-8") as f:
    html = f.read()

# find sidebar hrefs
sidebar_match = re.search(r'<aside class="sidebar">(.*?)</aside>', html, re.DOTALL)
if sidebar_match:
    sidebar_html = sidebar_match.group(1)
    sidebar_hrefs = re.findall(r'href="#([^"]+)"', sidebar_html)
else:
    sidebar_hrefs = re.findall(r'href="#([^"]+)"', html)

all_ids = set(re.findall(r'id="([^"]+)"', html))

print(f"Total sidebar links: {len(sidebar_hrefs)}")
print(f"Total element IDs in document: {len(all_ids)}")

print("\nLink Target Verification:")
missing = []
for h in sidebar_hrefs:
    found = h in all_ids
    if not found:
        missing.append(h)
    print(f"  #{h} -> Found: {found}")

print(f"\nMissing IDs ({len(missing)}):")
for m in missing:
    print(f"  - #{m}")
