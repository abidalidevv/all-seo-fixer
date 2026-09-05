import re

with open('assets/js/admin.js', 'r', encoding='utf-8') as f:
    code = f.read()

# Check for ES2020+ features that fail in older browsers or standard WP environments
checks = [
    (r'\?\.', 'Optional chaining (?.)'),
    (r'\?\?', 'Nullish coalescing (??)'),
    (r'`[^`]*`', 'Template literal (backticks)'),
    (r'=>', 'Arrow function (=>)'),
    (r'\bconst\b', 'const keyword'),
    (r'\blet\b', 'let keyword'),
    (r'\bclass\b', 'class keyword'),
    (r'#([a-zA-Z0-9_]+)\s*\(', 'Private method (#)'),
]

for pattern, name in checks:
    matches = list(re.finditer(pattern, code))
    print(f"{name}: {len(matches)} occurrences")
    if matches and len(matches) < 5:
        for m in matches:
            # find line number
            lnum = code[:m.start()].count('\n') + 1
            print(f"   Line {lnum}: {code[m.start():m.start()+40]}")
