import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('assets/js/admin.js', 'r', encoding='utf-8') as f:
    content = f.read()

lines = content.splitlines()

# We want to trace the ready block: line 204 to line 3807
ready_lines = lines[203:3807]

immediate_code = []
for idx, line in enumerate(ready_lines, 204):
    if line.startswith('\t\t') and not line.startswith('\t\t\t'):
        s = line.strip()
        if not s or s.startswith('//') or s.startswith('/*') or s.startswith('*'):
            continue
        if not re.match(r'^\$\(document\)\.on\(', s) and not re.match(r'^\$\([\'"][^)]+\)\.on\(', s):
            immediate_code.append((idx, s))

print(f"Total immediate statements in ready(): {len(immediate_code)}")
for idx, code in immediate_code:
    print(f"{idx}: {code[:100]}")
