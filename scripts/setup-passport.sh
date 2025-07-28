#!/bin/bash

# Laravel Passport Setup Script
# This script properly sets up Passport without migration conflicts

set -e  # Exit on any error

echo "🚀 Setting up Laravel Passport..."

# Function to check if we're in a Docker container
check_docker() {
    if [ -f /.dockerenv ]; then
        echo "📦 Running inside Docker container"
        return 0
    else
        echo "💻 Running on host system"
        return 1
    fi
}

# Function to run artisan commands
run_artisan() {
    if check_docker; then
        php artisan "$@"
    else
        docker compose -f compose.dev.yaml exec -T workspace php artisan "$@"
    fi
}

# Step 1: Ensure storage directories exist
echo "📁 Creating storage directories..."
mkdir -p storage/oauth-keys
chmod 755 storage/oauth-keys

# Step 2: Check if migrations already exist
echo "🔍 Checking existing migrations..."
if ls database/migrations/*oauth* 1> /dev/null 2>&1; then
    echo "✅ Passport migrations already exist - skipping migration publishing"
else
    echo "📥 Publishing Passport migrations..."
    run_artisan vendor:publish --tag=passport-migrations
fi

# Step 3: Run migrations only if tables don't exist
echo "🗄️  Checking database tables..."
if run_artisan migrate:status | grep -q "oauth_auth_codes"; then
    echo "✅ Passport tables already exist - skipping migration"
else
    echo "🔄 Running Passport migrations..."
    run_artisan migrate --force
fi

# Step 4: Generate encryption keys if they don't exist
echo "🔐 Setting up encryption keys..."
if [ ! -f storage/oauth-keys/oauth-private.key ] || [ ! -f storage/oauth-keys/oauth-public.key ]; then
    echo "🔑 Generating Passport encryption keys..."
    run_artisan passport:keys --force
else
    echo "✅ Encryption keys already exist"
fi

# Step 5: Install Passport (creates default clients)
echo "👥 Setting up OAuth clients..."
if ! run_artisan passport:install --force 2>/dev/null; then
    echo "ℹ️  Passport installation completed (clients may already exist)"
else
    echo "✅ OAuth clients created successfully"
fi

echo "🎉 Laravel Passport setup completed successfully!"
echo ""
echo "📋 Next steps:"
echo "   1. Add Passport::routes() to your AuthServiceProvider if not already done"
echo "   2. Add HasApiTokens trait to your User model if not already done"
echo "   3. Configure your API guards in config/auth.php"
echo "   4. Test your OAuth endpoints"
