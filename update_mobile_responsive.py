import os
import re

# List of PHP files to update (excluding homepage, availablerooms, and adminpanel which are already done)
files_to_update = [
    'browsementors.php',
    'chat.php',
    'add-product.php',
    'lostandfound.php',
    'mentorpanel.php',
    'mybargains.php',
    'mydeals.php',
    'myjobs.php',
    'mymentors.php',
    'myselllist.php',
    'parttimejob.php',
    'postjob.php',
    'rentedrooms.php',
    'SellAndExchange.php',
    'settings.php',
    'shuttle_tracking_system.php',
    'useraccount.php',
    'contactmentor.php',
    'viewmentorprofile.php',
    'addnewmentor.php',
    'addnewroom.php'
]

base_path = r'c:\xampp\htdocs\UIU_Supplements_Live'

updated_count = 0
errors = []

for filename in files_to_update:
    filepath = os.path.join(base_path, filename)
    
    # Check if file exists
    if not os.path.exists(filepath):
        print(f"Skipping {filename} - file not found")
        continue
    
    try:
        # Read file content
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Check if responsive CSS is already added
        if 'responsive-mobile.css' in content:
            print(f"Skipping {filename} - already has responsive CSS")
            continue
        
        # Add mobile responsive CSS after index.css
        content = re.sub(
            r'(<link\s+rel="stylesheet"\s+href="assets/css/index\.css"\s*/?>)',
            r'\1\n    <link rel="stylesheet" href="assets/css/responsive-mobile.css" />',
            content,
            flags=re.MULTILINE
        )
        
        # Add mobile navigation JavaScript before closing body or html tag
        if 'mobile-nav.js' not in content:
            # Try to find existing script tag and add after it
            if '<script src="assets/js/index.js"></script>' in content:
                content = re.sub(
                    r'(<script\s+src="assets/js/index\.js"></script>)',
                    r'\1\n<script src="assets/js/mobile-nav.js"></script>',
                    content
                )
            elif '</body>' in content:
                content = re.sub(
                    r'(</body>)',
                    r'<script src="assets/js/mobile-nav.js"></script>\n\1',
                    content
                )
            elif '</html>' in content:
                content = re.sub(
                    r'(</html>)',
                    r'<script src="assets/js/mobile-nav.js"></script>\n\1',
                    content
                )
        
        # Only write if content changed
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✓ Updated {filename}")
            updated_count += 1
        else:
            print(f"⚠ No changes made to {filename}")
    
    except Exception as e:
        error_msg = f"Error updating {filename}: {str(e)}"
        print(f"✗ {error_msg}")
        errors.append(error_msg)

print(f"\n{'='*50}")
print(f"Summary:")
print(f"Updated: {updated_count} files")
print(f"Errors: {len(errors)} files")
if errors:
    print("\nErrors:")
    for error in errors:
        print(f"  - {error}")
