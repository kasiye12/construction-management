#!/bin/bash

echo "🔍 AUDITING ALL PAGES - PERMISSIONS & VALIDATION"
echo "================================================="

# Check if all controller files exist
echo ""
echo "📁 Checking Controllers..."
controllers=(
    "app/Http/Controllers/ProjectController.php"
    "app/Http/Controllers/BoqItemController.php"
    "app/Http/Controllers/IpcController.php"
    "app/Http/Controllers/SubcontractorController.php"
    "app/Http/Controllers/CostCategoryController.php"
    "app/Http/Controllers/ReportController.php"
    "app/Http/Controllers/Admin/UserController.php"
    "app/Http/Controllers/Admin/RoleController.php"
)

for c in "${controllers[@]}"; do
    if [ -f "$c" ]; then
        echo "  ✅ $c"
    else
        echo "  ❌ MISSING: $c"
    fi
done

# Check views
echo ""
echo "📁 Checking Views..."
views=(
    "resources/views/dashboard.blade.php"
    "resources/views/projects/index.blade.php"
    "resources/views/projects/create.blade.php"
    "resources/views/projects/edit.blade.php"
    "resources/views/projects/show.blade.php"
    "resources/views/boq-items/index.blade.php"
    "resources/views/boq-items/create.blade.php"
    "resources/views/boq-items/edit.blade.php"
    "resources/views/boq-items/show.blade.php"
    "resources/views/ipcs/index.blade.php"
    "resources/views/ipcs/create.blade.php"
    "resources/views/ipcs/edit.blade.php"
    "resources/views/ipcs/show.blade.php"
    "resources/views/subcontractors/index.blade.php"
    "resources/views/subcontractors/create.blade.php"
    "resources/views/subcontractors/edit.blade.php"
    "resources/views/subcontractors/show.blade.php"
    "resources/views/cost-categories/index.blade.php"
    "resources/views/cost-categories/create.blade.php"
    "resources/views/cost-categories/edit.blade.php"
    "resources/views/cost-categories/show.blade.php"
    "resources/views/reports/thirty-column.blade.php"
    "resources/views/admin/users/index.blade.php"
    "resources/views/admin/users/create.blade.php"
    "resources/views/admin/users/edit.blade.php"
    "resources/views/admin/users/show.blade.php"
    "resources/views/admin/roles/index.blade.php"
    "resources/views/admin/roles/create.blade.php"
    "resources/views/admin/roles/edit.blade.php"
    "resources/views/admin/roles/show.blade.php"
    "resources/views/layouts/app.blade.php"
    "resources/views/auth/login.blade.php"
)

for v in "${views[@]}"; do
    if [ -f "$v" ]; then
        echo "  ✅ $v"
    else
        echo "  ❌ MISSING: $v"
    fi
done

echo ""
echo "================================================="
echo "✅ Audit complete!"
