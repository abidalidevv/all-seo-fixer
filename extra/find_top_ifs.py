import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('assets/js/admin.js', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for idx, line in enumerate(lines, 1):
    if line.startswith('\t\tif (') or line.startswith('\t\tif('):
        print(f"{idx:4d}: {line.strip()[:90]}")
