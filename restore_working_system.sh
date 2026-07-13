#!/bin/bash

echo "🔄 Restoring Working Construction Management System..."
echo "======================================================"

# 1. Keep only the essential migrations (remove complex ones)
rm -f database/migrations/2024_01_01_000020_*.php
rm -f database/migrations/2024_01_01_000021_*.php
rm -f database/migrations/2024_01_01_000022_*.php
rm -f database/migrations/2024_01_01_000023_*.php
rm -f database/migrations/2024_01_01_000024_*.php
rm -f database/migrations/2024_01_01_000025_*.php
rm -f database/migrations/2024_01_01_000026_*.php

# 2. Keep only essential models
rm -f app/Models/SubcontractorContract.php
rm -f app/Models/TakeoffSheet.php
rm -f app/Models/TakeoffDetail.php
rm -f app/Models/PaymentCertificate.php
rm -f app/Models/CertificateItem.php
rm -f app/Models/ActualCost.php
rm -f app/Models/Approval.php

# 3. Remove extra controllers
rm -f app/Http/Controllers/PaymentCertificateController.php
rm -f app/Http/Controllers/TakeoffController.php
rm -f app/Http/Controllers/DashboardController.php

echo "✅ Cleaned up - restoring working version..."
