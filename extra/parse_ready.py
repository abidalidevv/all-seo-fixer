with open('assets/js/admin.js', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Let's inspect all lines between 204 and 3807
# We want to identify lines that do something when the file is readied.
# Let's look for syntax errors, unhandled variables, or calls.

# Specifically, let's write a node script using acorn/esprima or native Function constructor to check every block!
