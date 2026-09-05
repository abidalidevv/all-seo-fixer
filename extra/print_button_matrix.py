import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('scratch/button_audit_matrix.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

print(f"{'#':<3} | {'VIEW':<25} | {'BUTTON TEXT':<35} | {'HANDLING':<45}")
print("-" * 115)
for i, b in enumerate(data, 1):
    txt = b['text'][:32]
    vw = b['view']
    h = b['handling'][:43]
    print(f"{i:<3} | {vw:<25} | {txt:<35} | {h:<45}")
