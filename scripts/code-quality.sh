#!/bin/bash

# Code Quality Enhancement Script for Laravel Project
# This script provides tools for code analysis, testing, and quality improvements

set -e

echo "🔍 Laravel Code Quality Tools"
echo "============================="

# Function to check if we're in a Laravel project
check_laravel_project() {
    if [ ! -f "artisan" ]; then
        echo "❌ Error: Not in a Laravel project directory"
        exit 1
    fi
}

# Function to install development dependencies
install_dev_deps() {
    echo "📦 Installing development dependencies..."

    # Install PHP development tools via Composer
    docker compose -f compose.dev.yaml exec workspace composer require --dev \
        phpstan/phpstan \
        psalm/plugin-laravel \
        vimeo/psalm \
        rector/rector \
        phpunit/phpunit \
        mockery/mockery \
        fakerphp/faker \
        nunomaduro/collision \
        spatie/laravel-ignition \
        --no-interaction

    echo "✅ Development dependencies installed"
}

# Function to run static analysis with PHPStan
run_phpstan() {
    echo "🔍 Running PHPStan static analysis..."

    # Create PHPStan config if it doesn't exist
    if [ ! -f "phpstan.neon" ]; then
        cat > phpstan.neon << EOF
parameters:
    level: 5
    paths:
        - app
        - config
        - database
        - routes
    excludePaths:
        - app/Console/Kernel.php
        - app/Http/Kernel.php
    ignoreErrors:
        - '#Unsafe usage of new static#'
    checkMissingIterableValueType: false
EOF
        echo "📝 Created phpstan.neon configuration"
    fi

    docker compose -f compose.dev.yaml exec workspace ./vendor/bin/phpstan analyse --memory-limit=1G
}

# Function to run Psalm analysis
run_psalm() {
    echo "🔍 Running Psalm static analysis..."

    # Initialize Psalm if config doesn't exist
    if [ ! -f "psalm.xml" ]; then
        docker compose -f compose.dev.yaml exec workspace ./vendor/bin/psalm --init
        echo "📝 Initialized Psalm configuration"
    fi

    docker compose -f compose.dev.yaml exec workspace ./vendor/bin/psalm
}

# Function to run code style fixes
run_code_style() {
    echo "🎨 Running code style fixes..."

    # Run Laravel Pint for code formatting
    docker compose -f compose.dev.yaml exec workspace ./vendor/bin/pint

    echo "✅ Code style fixes applied"
}

# Function to run tests with coverage
run_tests_with_coverage() {
    echo "🧪 Running tests with coverage..."

    # Create phpunit.xml if it doesn't exist or update it for coverage
    if [ ! -f "phpunit.xml" ]; then
        echo "❌ phpunit.xml not found. Please ensure PHPUnit is properly configured."
        return 1
    fi

    # Run tests with coverage
    docker compose -f compose.dev.yaml exec workspace php artisan test --coverage --min=2
}

# Function to run security audit
run_security_audit() {
    echo "🔒 Running security audit..."

    # Check for known vulnerabilities in dependencies
    docker compose -f compose.dev.yaml exec workspace composer audit

    # Check for outdated packages
    echo "\n📊 Checking for outdated packages:"
    docker compose -f compose.dev.yaml exec workspace composer outdated --direct
}

# Function to optimize application
optimize_app() {
    echo "⚡ Optimizing application..."

    docker compose -f compose.dev.yaml exec workspace php artisan config:cache
    docker compose -f compose.dev.yaml exec workspace php artisan route:cache
    docker compose -f compose.dev.yaml exec workspace php artisan view:cache
    docker compose -f compose.dev.yaml exec workspace php artisan event:cache

    echo "✅ Application optimized"
}

# Function to clear all caches
clear_caches() {
    echo "🧹 Clearing all caches..."

    docker compose -f compose.dev.yaml exec workspace php artisan config:clear
    docker compose -f compose.dev.yaml exec workspace php artisan route:clear
    docker compose -f compose.dev.yaml exec workspace php artisan view:clear
    docker compose -f compose.dev.yaml exec workspace php artisan event:clear
    docker compose -f compose.dev.yaml exec workspace php artisan cache:clear

    echo "✅ All caches cleared"
}

# Function to generate IDE helper files
generate_ide_helpers() {
    echo "💡 Generating IDE helper files..."

    # Install IDE helper if not present
    docker compose -f compose.dev.yaml exec workspace composer require --dev barryvdh/laravel-ide-helper --no-interaction

    # Generate helper files
    docker compose -f compose.dev.yaml exec workspace php artisan ide-helper:generate
    docker compose -f compose.dev.yaml exec workspace php artisan ide-helper:models --nowrite
    docker compose -f compose.dev.yaml exec workspace php artisan ide-helper:meta

    echo "✅ IDE helper files generated"
}

# Function to run database analysis
analyze_database() {
    echo "🗄️ Analyzing database..."

    # Check migration status
    echo "Migration status:"
    docker compose -f compose.dev.yaml exec workspace php artisan migrate:status

    # Show database size and table info
    echo "\nDatabase information:"
    docker compose -f compose.dev.yaml exec postgres psql -U laravel -d laravel_dev -c "
        SELECT
            schemaname,
            tablename,
            attname,
            typename,
            char_length
        FROM pg_tables t
        LEFT JOIN pg_attribute a ON a.attrelid = t.tablename::regclass
        LEFT JOIN pg_type ty ON ty.oid = a.atttypid
        WHERE schemaname = 'public'
        ORDER BY tablename, attname;
    "
}

# Function to run full quality check
run_full_check() {
    echo "🚀 Running full code quality check..."

    check_laravel_project

    echo "\n1. Installing/updating development dependencies..."
    install_dev_deps

    echo "\n2. Running static analysis..."
    run_phpstan || echo "⚠️ PHPStan found issues"

    echo "\n3. Running code style fixes..."
    run_code_style

    echo "\n4. Running tests..."
    run_tests_with_coverage || echo "⚠️ Some tests failed or coverage is low"

    echo "\n5. Running security audit..."
    run_security_audit || echo "⚠️ Security issues found"

    echo "\n6. Generating IDE helpers..."
    generate_ide_helpers

    echo "\n✅ Full quality check completed!"
}

# Function to show help
show_help() {
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  install-deps     Install development dependencies"
    echo "  phpstan          Run PHPStan static analysis"
    echo "  psalm            Run Psalm static analysis"
    echo "  style            Fix code style with Laravel Pint"
    echo "  test             Run tests with coverage"
    echo "  security         Run security audit"
    echo "  optimize         Optimize application (cache configs)"
    echo "  clear            Clear all caches"
    echo "  ide-helpers      Generate IDE helper files"
    echo "  db-analyze       Analyze database structure"
    echo "  full-check       Run complete quality check"
    echo "  help             Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 full-check   # Run complete quality analysis"
    echo "  $0 style        # Fix code style issues"
    echo "  $0 test         # Run tests with coverage"
}

# Main script logic
case "${1:-help}" in
    "install-deps")
        check_laravel_project
        install_dev_deps
        ;;
    "phpstan")
        check_laravel_project
        run_phpstan
        ;;
    "psalm")
        check_laravel_project
        run_psalm
        ;;
    "style")
        check_laravel_project
        run_code_style
        ;;
    "test")
        check_laravel_project
        run_tests_with_coverage
        ;;
    "security")
        check_laravel_project
        run_security_audit
        ;;
    "optimize")
        check_laravel_project
        optimize_app
        ;;
    "clear")
        check_laravel_project
        clear_caches
        ;;
    "ide-helpers")
        check_laravel_project
        generate_ide_helpers
        ;;
    "db-analyze")
        check_laravel_project
        analyze_database
        ;;
    "full-check")
        run_full_check
        ;;
    "help")
        show_help
        ;;
    *)
        echo "❌ Unknown command: $1"
        show_help
        exit 1
        ;;
esac
