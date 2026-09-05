import os
import zipfile

root_dir = r"c:\Users\Ali\Music\orbix-seo-fixer\all-seo-fixer"
zip_path = os.path.join(root_dir, "all-seo-fixer.zip")

# Allowed root files and directories for production WordPress plugin
allowed_top = [
    "all-seo-fixer.php",
    "readme.txt",
    "README.md",
    "DOCUMENTATION.md",
    "documentation.html",
    "admin",
    "includes",
    "assets"
]

print(f"Creating clean production ZIP: {zip_path}")

with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for item in allowed_top:
        item_path = os.path.join(root_dir, item)
        if not os.path.exists(item_path):
            continue
        if os.path.isfile(item_path):
            archive_name = os.path.join("all-seo-fixer", item)
            zipf.write(item_path, archive_name)
            print(f"Added file: {archive_name}")
        elif os.path.isdir(item_path):
            for foldername, subfolders, filenames in os.walk(item_path):
                # skip any hidden or cache dirs
                subfolders[:] = [d for d in subfolders if not d.startswith('.')]
                for filename in filenames:
                    if filename.startswith('.'):
                        continue
                    filepath = os.path.join(foldername, filename)
                    rel_path = os.path.relpath(filepath, root_dir)
                    archive_name = os.path.join("all-seo-fixer", rel_path)
                    zipf.write(filepath, archive_name)
            print(f"Added directory: {item}")

size_kb = os.path.getsize(zip_path) / 1024
print(f"ZIP build complete! File size: {size_kb:.1f} KB")
