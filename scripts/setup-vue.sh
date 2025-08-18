#!/bin/bash

# Laravel Vue 3 + Inertia.js Setup Script
# This script sets up Vue 3 with Inertia.js for Laravel following best practices

set -e  # Exit on any error

# Warning about existing implementation
echo "⚠️  WARNING: This script will remove any existing Vue/Inertia implementation!"
echo "📝 This includes:"
echo "   - Existing Vue components and pages"
echo "   - Current package.json dependencies"
echo "   - Vite configuration"
echo "   - Routes and layouts"
echo ""
echo "💡 If you want to delete the current implementation and proceed, type 'delete-current'"
echo "❌ Otherwise, press Ctrl+C to cancel"
echo ""
read -p "Type 'delete-current' to continue: " confirmation

if [ "$confirmation" != "delete-current" ]; then
    echo "❌ Setup cancelled. No changes were made."
    exit 1
fi

echo "🚀 Setting up Laravel with Vue 3 + Inertia.js..."

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

# Function to run composer commands
run_composer() {
    if check_docker; then
        composer "$@"
    else
        docker compose -f compose.dev.yaml exec -T workspace composer "$@"
    fi
}

# Function to run npm commands
run_npm() {
    if check_docker; then
        npm "$@"
    else
        docker compose -f compose.dev.yaml exec -T workspace npm "$@"
    fi
}

# Step 1: Install Laravel dependencies
echo "📦 Installing Laravel Inertia.js server-side adapter..."
run_composer require inertiajs/inertia-laravel

# Step 2: Install and configure Inertia.js middleware
echo "🔧 Publishing and configuring Inertia.js middleware..."
run_artisan inertia:middleware

# Update the root view in HandleInertiaRequests middleware
if [ -f "app/Http/Middleware/HandleInertiaRequests.php" ]; then
    echo "📝 Updating Inertia root view to layouts.app..."
    sed -i.bak "s/protected \$rootView = 'app';/protected \$rootView = 'layouts.app';/g" app/Http/Middleware/HandleInertiaRequests.php
fi

# Step 3: Add middleware to Kernel (we'll create a backup and modify)
echo "⚙️  Configuring middleware..."
if [ -f "bootstrap/app.php" ]; then
    # Laravel 11+ style
    if ! grep -q "HandleInertiaRequests" bootstrap/app.php; then
        echo "📝 Adding Inertia middleware to bootstrap/app.php..."
        cp bootstrap/app.php bootstrap/app.php.backup
        # Add Inertia middleware to the web middleware group
        sed -i.bak '/->withMiddleware(function (Middleware \$middleware) {/a\
        \$middleware->web(append: [\\\\\
            \\App\\Http\\Middleware\\HandleInertiaRequests::class,\\\\\
        ]);
' bootstrap/app.php
    fi
else
    # Laravel 10 style
    if [ -f "app/Http/Kernel.php" ] && ! grep -q "HandleInertiaRequests" app/Http/Kernel.php; then
        echo "📝 Adding Inertia middleware to Kernel.php..."
        cp app/Http/Kernel.php app/Http/Kernel.php.backup
        # Add to web middleware group
        sed -i.bak "/protected \$middlewareGroups = \[/,/\];/s/'web' => \[/&\n            \\App\\Http\\Middleware\\HandleInertiaRequests::class,/" app/Http/Kernel.php
    fi
fi

# Step 4: Remove existing React dependencies and install Vue 3 dependencies
echo "🗑️  Removing React dependencies and installing Vue 3 dependencies..."

# Remove React-specific dependencies
run_npm uninstall react react-dom @inertiajs/react @vitejs/plugin-react @types/react @types/react-dom typescript @testing-library/react @testing-library/jest-dom @testing-library/user-event @headlessui/react @heroicons/react zustand eslint @typescript-eslint/eslint-plugin @typescript-eslint/parser eslint-plugin-react eslint-plugin-react-hooks eslint-plugin-react-refresh || true

# Install Vue 3 and related frontend dependencies
echo "🎨 Installing Vue 3 and frontend dependencies..."
run_npm install vue@^3.4.0 @inertiajs/vue3@^1.0.0 @vitejs/plugin-vue@^6.0.0
run_npm install --save-dev @vue/compiler-sfc@^3.4.0

# Step 5: Install additional recommended packages
echo "📚 Installing additional Vue ecosystem packages..."
run_npm install vue-router@^4.2.0 pinia@^2.1.0 @headlessui/vue@^1.7.0 @heroicons/vue@^2.0.0

# Step 6: Install development and testing dependencies
echo "🧪 Installing development and testing dependencies..."
run_npm install --save-dev @vue/test-utils@^2.4.0 vitest@^1.1.0 jsdom@^23.0.0 @vitest/ui@^1.1.0 happy-dom@^12.10.0

# Step 7: Clean up React files and create Vue directories
echo "🧹 Cleaning up React files..."
# Remove React-specific files
rm -rf resources/js/Components/*.jsx
rm -rf resources/js/Pages/*.jsx
rm -rf resources/js/Layouts/*.jsx
rm -f resources/js/app.jsx
rm -f resources/views/app.blade.php
rm -f tsconfig.json
rm -f tsconfig.node.json
rm -f .eslintrc.js
rm -f .eslintrc.cjs
rm -f vitest.setup.js
rm -rf tests/React

echo "📝 Creating Vue ESLint configuration..."
cat > .eslintrc.cjs << 'EOF'
module.exports = {
  root: true,
  env: {
    node: true,
    browser: true,
    es2021: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-essential',
    'plugin:vue/vue3-strongly-recommended',
    'plugin:vue/vue3-recommended',
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  plugins: ['vue'],
  rules: {
    'vue/multi-word-component-names': 'off',
    'vue/no-unused-vars': 'error',
  },
  ignorePatterns: ['dist', '.eslintrc.cjs'],
};
EOF

echo "📁 Creating Vue component directories..."
mkdir -p resources/js/Components
mkdir -p resources/js/Layouts
mkdir -p resources/js/Pages
mkdir -p resources/js/Stores
mkdir -p resources/js/Composables
mkdir -p tests/Vue

echo "🎉 Vue 3 + Inertia.js dependencies installed successfully!"
echo ""
echo "📋 Next steps:"
echo "   1. Update vite.config.js to include Vue plugin"
echo "   2. Update resources/js/app.js for Inertia.js + Vue 3"
echo "   3. Create your first Vue components and pages"
echo "   4. Update your Blade layout to include Inertia root div"
echo "   5. Run 'npm run dev' to start the development server"
echo ""
# Step 8: Update configuration files
echo "🔧 Updating configuration files..."

# Update package.json with Vue dependencies
if [ -f "stubs/package-vue.json" ]; then
    echo "📦 Updating package.json with Vue dependencies..."
    cp package.json package.json.backup
    cp stubs/package-vue.json package.json
fi

# Update vite.config.js for Vue
if [ -f "stubs/vite-vue.config.js" ]; then
    echo "⚙️  Updating vite.config.js for Vue support..."
    cp vite.config.js vite.config.js.backup
    cp stubs/vite-vue.config.js vite.config.js
fi

# Update app.js for Inertia + Vue
if [ -f "stubs/app-vue.js" ]; then
    echo "🎨 Creating resources/js/app.js for Vue + Inertia..."
    # Backup existing app.js if it exists
    if [ -f "resources/js/app.js" ]; then
        cp resources/js/app.js resources/js/app.js.backup
    fi
    cp stubs/app-vue.js resources/js/app.js
fi

# Create Vitest setup file
if [ -f "stubs/vitest-setup.js" ]; then
    echo "🧪 Creating Vitest setup file..."
    cp stubs/vitest-setup.js tests/Vue/setup.js
fi

# Update web routes for Inertia
if [ -f "stubs/web-vue.php" ]; then
    echo "🌐 Updating web routes for Inertia.js..."
    cp routes/web.php routes/web.php.backup
    cp stubs/web-vue.php routes/web.php
fi

# Create/update main blade layout for Inertia
if [ -f "stubs/app-vue.blade.php" ]; then
    echo "📄 Creating app.blade.php layout for Inertia.js..."
    mkdir -p resources/views/layouts
    cp stubs/app-vue.blade.php resources/views/layouts/app.blade.php
fi

# Install the new dependencies
echo "📥 Installing updated dependencies..."
run_npm install

# Step 9: Create sample Vue components and files
echo "🎨 Creating sample Vue components..."

# Create sample layout
if [ -f "stubs/AppLayout.vue" ]; then
    echo "📄 Creating AppLayout.vue..."
    cp stubs/AppLayout.vue resources/js/Layouts/AppLayout.vue
fi

# Create sample components
if [ -f "stubs/NavLink.vue" ]; then
    echo "📄 Creating NavLink.vue..."
    cp stubs/NavLink.vue resources/js/Components/NavLink.vue
fi

if [ -f "stubs/Dropdown.vue" ]; then
    echo "📄 Creating Dropdown.vue..."
    cp stubs/Dropdown.vue resources/js/Components/Dropdown.vue
fi

if [ -f "stubs/DropdownLink.vue" ]; then
    echo "📄 Creating DropdownLink.vue..."
    cp stubs/DropdownLink.vue resources/js/Components/DropdownLink.vue
fi

# Create sample page
if [ -f "stubs/Welcome.vue" ]; then
    echo "📄 Creating Welcome.vue page..."
    cp stubs/Welcome.vue resources/js/Pages/Welcome.vue
fi

# Create sample store
if [ -f "stubs/counter.js" ]; then
    echo "📄 Creating counter store..."
    cp stubs/counter.js resources/js/Stores/counter.js
fi

# Create sample test
if [ -f "stubs/Welcome.test.js" ]; then
    echo "📄 Creating sample test..."
    cp stubs/Welcome.test.js tests/Vue/Welcome.test.js
fi

echo "🔧 Configuration files updated successfully!"
echo "🎨 Sample components created successfully!"
