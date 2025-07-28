#!/bin/bash

# Pre-commit Setup Script for Laravel Project
# This script installs and configures pre-commit hooks

set -e

echo "🔧 Setting up Pre-commit Hooks"
echo "=============================="

# Function to check if we're in a Laravel project
check_laravel_project() {
    if [ ! -f "artisan" ]; then
        echo "❌ Error: Not in a Laravel project directory"
        exit 1
    fi
}

# Function to check if Docker is running
check_docker() {
    if ! docker --version > /dev/null 2>&1; then
        echo "❌ Docker is not installed or not running"
        exit 1
    fi
    echo "✅ Docker is available"
}

# Function to install pre-commit
install_pre_commit() {
    echo "📦 Installing pre-commit..."

    # Check if pre-commit is already installed
    if command -v pre-commit > /dev/null 2>&1; then
        echo "✅ pre-commit is already installed"
        pre-commit --version
        return 0
    fi

    # Try to install via pip
    if command -v pip3 > /dev/null 2>&1; then
        echo "Installing pre-commit via pip3..."
        pip3 install pre-commit
    elif command -v pip > /dev/null 2>&1; then
        echo "Installing pre-commit via pip..."
        pip install pre-commit
    elif command -v brew > /dev/null 2>&1; then
        echo "Installing pre-commit via Homebrew..."
        brew install pre-commit
    elif command -v apt-get > /dev/null 2>&1; then
        echo "Installing pre-commit via apt..."
        sudo apt-get update
        sudo apt-get install -y python3-pip
        pip3 install pre-commit
    else
        echo "❌ Could not find a package manager to install pre-commit"
        echo "Please install pre-commit manually: https://pre-commit.com/#installation"
        exit 1
    fi

    echo "✅ pre-commit installed successfully"
}

# Function to setup development environment
setup_dev_environment() {
    echo "🚀 Setting up development environment..."

    # Start development containers if not running
    if ! docker compose -f compose.dev.yaml ps | grep -q "Up"; then
        echo "Starting development containers..."
        make dev
        echo "Waiting for containers to be ready..."
        sleep 10
    fi

    # Install PHP dependencies if vendor directory doesn't exist
    if [ ! -d "vendor" ]; then
        echo "Installing PHP dependencies..."
        make composer-install
    fi

    # Install Node dependencies if node_modules doesn't exist
    if [ ! -d "node_modules" ]; then
        echo "Installing Node.js dependencies..."
        make npm-install
    fi

    echo "✅ Development environment ready"
}

# Function to install pre-commit hooks
install_hooks() {
    echo "🔗 Installing pre-commit hooks..."

    # Install the git hook scripts
    pre-commit install

    # Install hooks for commit-msg (optional)
    pre-commit install --hook-type commit-msg

    echo "✅ Pre-commit hooks installed"
}

# Function to create secrets baseline
setup_secrets_baseline() {
    echo "🔒 Setting up secrets detection baseline..."

    if [ ! -f ".secrets.baseline" ]; then
        # Create initial secrets baseline
        if command -v detect-secrets > /dev/null 2>&1; then
            detect-secrets scan --baseline .secrets.baseline
            echo "✅ Secrets baseline created"
        else
            echo "⚠️ detect-secrets not found, creating empty baseline"
            echo '{}' > .secrets.baseline
        fi
    else
        echo "✅ Secrets baseline already exists"
    fi
}

# Function to update package.json with lint scripts
setup_npm_scripts() {
    echo "📝 Setting up npm scripts for linting..."

    # Check if package.json exists
    if [ ! -f "package.json" ]; then
        echo "⚠️ package.json not found, skipping npm scripts setup"
        return 0
    fi

    # Add lint script if it doesn't exist
    if ! grep -q '"lint"' package.json; then
        echo "Adding lint script to package.json..."
        # This is a simple approach - in production you might want to use jq
        sed -i.bak 's/"scripts": {/"scripts": {\n    "lint": "eslint resources\/js --ext .js,.vue --fix",/' package.json
        rm -f package.json.bak
    fi

    echo "✅ npm scripts configured"
}

# Function to test pre-commit setup
test_pre_commit() {
    echo "🧪 Testing pre-commit setup..."

    # Run pre-commit on all files to test
    echo "Running pre-commit on all files (this may take a while)..."
    if pre-commit run --all-files; then
        echo "✅ All pre-commit hooks passed"
    else
        echo "⚠️ Some pre-commit hooks failed, but this is normal for initial setup"
        echo "The hooks will automatically fix issues when possible"
    fi
}

# Function to show usage instructions
show_usage_instructions() {
    echo ""
    echo "🎉 Pre-commit setup complete!"
    echo "============================="
    echo ""
    echo "📋 What happens now:"
    echo "• Code quality checks will run automatically before each commit"
    echo "• Laravel Pint will fix code style issues"
    echo "• PHPStan will check for potential bugs"
    echo "• Tests will run to ensure nothing is broken"
    echo "• Security audit will check for vulnerabilities"
    echo ""
    echo "🔧 Useful commands:"
    echo "• pre-commit run --all-files    # Run all hooks on all files"
    echo "• pre-commit run <hook-id>      # Run specific hook"
    echo "• pre-commit autoupdate         # Update hook versions"
    echo "• SKIP=<hook-id> git commit     # Skip specific hook for one commit"
    echo "• git commit --no-verify        # Skip all hooks (not recommended)"
    echo ""
    echo "📚 Hook configuration: .pre-commit-config.yaml"
    echo "🔒 Secrets baseline: .secrets.baseline"
    echo ""
    echo "💡 Tip: If a hook fails, it will often auto-fix the issue."
    echo "    Just add the changes and commit again!"
}

# Function to handle errors
handle_error() {
    echo "❌ Setup failed at step: $1"
    echo "Please check the error messages above and try again."
    echo "For help, see: https://pre-commit.com/"
    exit 1
}

# Main setup function
main() {
    echo "Starting pre-commit setup for Laravel project..."
    echo ""

    # Run setup steps
    check_laravel_project || handle_error "Laravel project check"
    check_docker || handle_error "Docker check"
    install_pre_commit || handle_error "pre-commit installation"
    setup_dev_environment || handle_error "development environment setup"
    setup_npm_scripts || handle_error "npm scripts setup"
    setup_secrets_baseline || handle_error "secrets baseline setup"
    install_hooks || handle_error "hooks installation"
    test_pre_commit || handle_error "pre-commit testing"

    show_usage_instructions
}

# Script options
case "${1:-setup}" in
    "setup")
        main
        ;;
    "install")
        install_pre_commit
        install_hooks
        ;;
    "test")
        test_pre_commit
        ;;
    "update")
        echo "🔄 Updating pre-commit hooks..."
        pre-commit autoupdate
        echo "✅ Hooks updated"
        ;;
    "help")
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  setup    # Full setup (default)"
        echo "  install  # Install pre-commit and hooks only"
        echo "  test     # Test current setup"
        echo "  update   # Update hook versions"
        echo "  help     # Show this help"
        ;;
    *)
        echo "❌ Unknown command: $1"
        echo "Use '$0 help' for usage information"
        exit 1
        ;;
esac
