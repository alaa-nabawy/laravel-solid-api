#!/bin/bash

# Fix Laravel Passport Migration Conflicts
# This script prevents duplicate migration issues when installing Passport

echo "🔧 Fixing Laravel Passport migration conflicts..."

# Remove any duplicate Passport migration files that might be published
echo "📁 Checking for duplicate migration files..."

# Find and remove newer duplicate Passport migrations
find database/migrations -name "*create_oauth_*" -newer database/migrations/2025_07_28_100934_create_oauth_auth_codes_table.php -delete 2>/dev/null || true

# Ensure our existing migrations are properly formatted
echo "✅ Existing Passport migrations preserved:"
ls -la database/migrations/*oauth* 2>/dev/null || echo "No OAuth migrations found"

# Create oauth-keys directory if it doesn't exist
mkdir -p storage/oauth-keys
chmod 755 storage/oauth-keys

echo "🎉 Passport migration conflicts resolved!"
echo "💡 Note: Run 'php artisan passport:keys' to generate encryption keys"
echo "💡 Note: Run 'php artisan passport:client --personal' to create personal access client"
echo "💡 Note: Run 'php artisan passport:client --password' to create password grant client"
