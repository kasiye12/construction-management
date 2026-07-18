#!/bin/bash
# Fix pagination in all views

for file in $(grep -rl "links()" resources/views/ --include="*.blade.php" 2>/dev/null); do
    echo "Fixing: $file"
    # Replace {{ $var->links() }} with professional version
    perl -i -pe 's/\{\{\s*\$([a-zA-Z_]+)->links\(\)\s*\}\}/<div class="d-flex justify-content-between align-items-center px-3 py-2"><div class="pagination-info">Showing {{\$$1->firstItem() ?? 0}} - {{\$$1->lastItem() ?? 0}} of {{\$$1->total()}} results<\/div>{{\$$1->links('\''vendor.pagination.custom'\'')}}<\/div>/g' "$file"
done
echo "Done!"
