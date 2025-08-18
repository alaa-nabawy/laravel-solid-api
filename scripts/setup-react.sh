#!/bin/bash

# Laravel React + Inertia.js Setup Script
# This script sets up React with Inertia.js in a Laravel application
# and removes any existing Vue implementation

# WARNING: This script will remove existing Vue/Inertia implementation
echo "⚠️  WARNING: This script will remove any existing Vue/Inertia implementation!"
echo ""
echo "The following will be deleted/replaced:"
echo "  • All Vue components and pages"
echo "  • Vue-specific dependencies from package.json"
echo "  • Vue configuration files (vite.config.js)"
echo "  • Vue-specific routes and layouts"
echo "  • Existing frontend build files"
echo ""
echo "This action cannot be undone!"
echo ""
read -p "To proceed, type 'delete-current' (without quotes): " confirmation

if [ "$confirmation" != "delete-current" ]; then
    echo "Setup cancelled. No changes were made."
    exit 0
fi

echo "Proceeding with React setup..."
echo ""

set -e

echo "🚀 Setting up Laravel with React + Inertia.js..."

# Function to check if we're in a Docker environment
check_docker() {
    if [ -f "/.dockerenv" ] || grep -q 'docker\|lxc' /proc/1/cgroup 2>/dev/null; then
        return 0
    else
        return 1
    fi
}

# Function to run artisan commands
run_artisan() {
    if check_docker; then
        php artisan "$@"
    else
        if command -v sail &> /dev/null && [ -f "docker-compose.yml" ]; then
            ./vendor/bin/sail artisan "$@"
        else
            php artisan "$@"
        fi
    fi
}

# Function to run composer commands
run_composer() {
    if check_docker; then
        composer "$@"
    else
        if command -v sail &> /dev/null && [ -f "docker-compose.yml" ]; then
            ./vendor/bin/sail composer "$@"
        else
            composer "$@"
        fi
    fi
}

# Function to run npm commands
run_npm() {
    if check_docker; then
        npm "$@"
    else
        if command -v sail &> /dev/null && [ -f "docker-compose.yml" ]; then
            ./vendor/bin/sail npm "$@"
        else
            npm "$@"
        fi
    fi
}

# Step 1: Install Inertia.js Laravel adapter
echo "📦 Step 1: Installing Inertia.js Laravel adapter..."
run_composer require inertiajs/inertia-laravel

# Step 1.5: Install Ziggy for route generation
echo "📦 Step 1.5: Installing Ziggy for Laravel route generation..."
run_composer require tightenco/ziggy

# Step 2: Publish and configure Inertia.js middleware
echo "⚙️  Step 2: Publishing Inertia.js middleware..."
run_artisan inertia:middleware

# Step 3: Update HandleInertiaRequests middleware
echo "🔧 Step 3: Updating HandleInertiaRequests middleware..."
if [ -f "app/Http/Middleware/HandleInertiaRequests.php" ]; then
    # Update the share method to include flash messages and auth user
    cat > app/Http/Middleware/HandleInertiaRequests.php << 'EOF'
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'url' => $request->url(),
        ]);
    }
}
EOF
fi

# Step 4: Register Inertia middleware
echo "🔗 Step 4: Registering Inertia middleware..."
if [ -f "bootstrap/app.php" ]; then
    # Laravel 11+ approach
    if ! grep -q "HandleInertiaRequests" bootstrap/app.php; then
        # Add the middleware to the web group
        sed -i.bak '/->withMiddleware/,/})/{/web:/a\
            \\App\\Http\\Middleware\\HandleInertiaRequests::class,
}' bootstrap/app.php
    fi
elif [ -f "app/Http/Kernel.php" ]; then
    # Laravel 10 approach
    if ! grep -q "HandleInertiaRequests" app/Http/Kernel.php; then
        sed -i.bak "s/'web' => \[/'web' => [\n        \\App\\Http\\Middleware\\HandleInertiaRequests::class,/" app/Http/Kernel.php
    fi
fi

# Step 5: Remove existing Vue dependencies and install React dependencies
echo "🗑️  Step 5: Removing Vue dependencies and installing React dependencies..."

# Remove Vue-specific dependencies
run_npm uninstall vue @vitejs/plugin-vue @vue/compiler-sfc @inertiajs/vue3 vue-router pinia @headlessui/vue @heroicons/vue @vue/test-utils || true

# Install React and related frontend dependencies
echo "📦 Installing React and Inertia.js dependencies..."
run_npm install react@^19.0.0 react-dom@^19.0.0 @inertiajs/react@^2.0.0

# Install additional React packages
echo "📦 Installing additional React packages..."
run_npm install @headlessui/react @heroicons/react axios clsx tailwind-merge ziggy-js

# Install development dependencies
echo "📦 Installing development dependencies..."
run_npm install --save-dev @vitejs/plugin-react @types/react @types/react-dom typescript @testing-library/react @testing-library/jest-dom @testing-library/user-event vitest jsdom @vitest/ui happy-dom eslint @typescript-eslint/eslint-plugin @typescript-eslint/parser eslint-plugin-react eslint-plugin-react-hooks eslint-plugin-react-refresh

# Step 6: Create necessary directories
echo "📁 Step 6: Creating directory structure..."
mkdir -p resources/js/Components
mkdir -p resources/js/Pages
mkdir -p resources/js/Stores
mkdir -p resources/js/Types
mkdir -p tests/React

# Step 7: Update package.json with React-specific scripts
echo "📝 Step 7: Updating package.json..."
cp stubs/package-react.json package.json

# Step 8: Update Vite configuration for React
echo "⚙️  Step 8: Updating Vite configuration..."
cp stubs/vite-react.config.js vite.config.js

# Step 9: Update app.js for React + Inertia
echo "🔧 Step 9: Updating app.js for React + Inertia..."
cp stubs/app-react.jsx resources/js/app.jsx
rm -f resources/js/app.js

# Step 10: Create Vitest setup file
echo "🧪 Step 10: Creating Vitest setup file..."
cat > vitest.setup.js << 'EOF'
import { expect, afterEach } from 'vitest';
import { cleanup } from '@testing-library/react';
import * as matchers from '@testing-library/jest-dom/matchers';

// Extend Vitest's expect with jest-dom matchers
expect.extend(matchers);

// Cleanup after each test case
afterEach(() => {
  cleanup();
});
EOF

# Step 11: Update web routes for Inertia
echo "🛣️  Step 11: Updating web routes..."
cp stubs/web-react.php routes/web.php

# Step 12: Update the main Blade layout
echo "🎨 Step 12: Updating main Blade layout..."
cp stubs/app.blade.php resources/views/app.blade.php

# Step 13: Remove old Vue files and install new dependencies
echo "🧹 Step 13: Cleaning up old Vue files..."
# Remove Vue-specific files
rm -rf resources/js/Components/*.vue
rm -rf resources/js/Pages/*.vue
rm -rf resources/js/Stores/*.js
rm -f resources/js/app.vue.js

# Step 14: Install new dependencies
echo "📦 Step 14: Installing dependencies..."
run_npm install

# Step 15: Create sample React components and pages
echo "🎨 Step 15: Creating sample React components and pages..."

# Copy React components
cp stubs/AppLayout.jsx resources/js/Layouts/AppLayout.jsx
cp stubs/NavLink.jsx resources/js/Components/NavLink.jsx
cp stubs/Dropdown.jsx resources/js/Components/Dropdown.jsx
cp stubs/DropdownLink.jsx resources/js/Components/DropdownLink.jsx

# Copy React pages
cp stubs/Welcome.jsx resources/js/Pages/Welcome.jsx

# Create a simple counter store example using Zustand
echo "🏪 Step 16: Creating sample store..."
run_npm install zustand

cat > resources/js/Stores/counter.js << 'EOF'
import { create } from 'zustand';

export const useCounterStore = create((set) => ({
  count: 0,
  increment: () => set((state) => ({ count: state.count + 1 })),
  decrement: () => set((state) => ({ count: state.count - 1 })),
  reset: () => set({ count: 0 }),
}));
EOF

# Step 17: Create sample tests
echo "🧪 Step 17: Creating sample tests..."
cat > tests/React/Welcome.test.jsx << 'EOF'
import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Welcome from '../../resources/js/Pages/Welcome';

describe('Welcome Component', () => {
  it('renders welcome message', () => {
    render(<Welcome laravelVersion="11.x" phpVersion="8.2" />);

    expect(screen.getByText(/Modern API Development/i)).toBeInTheDocument();
    expect(screen.getByText(/Laravel API Boilerplate/i)).toBeInTheDocument();
  });

  it('displays Laravel version', () => {
    render(<Welcome laravelVersion="11.x" phpVersion="8.2" />);

    expect(screen.getByText(/Laravel 11.x/i)).toBeInTheDocument();
  });
});
EOF

# Step 18: Create TypeScript configuration
echo "📝 Step 18: Creating TypeScript configuration..."
cat > tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  },
  "include": ["resources/js/**/*", "tests/React/**/*"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
EOF

cat > tsconfig.node.json << 'EOF'
{
  "compilerOptions": {
    "composite": true,
    "skipLibCheck": true,
    "module": "ESNext",
    "moduleResolution": "bundler",
    "allowSyntheticDefaultImports": true
  },
  "include": ["vite.config.js"]
}
EOF

# Step 19: Create ESLint configuration
echo "📝 Step 19: Creating ESLint configuration..."
cat > .eslintrc.cjs << 'EOF'
module.exports = {
  root: true,
  env: { browser: true, es2020: true },
  extends: [
    'eslint:recommended',
    '@typescript-eslint/recommended',
    'plugin:react-hooks/recommended',
    'plugin:react/recommended',
    'plugin:react/jsx-runtime',
  ],
  ignorePatterns: ['dist', '.eslintrc.cjs'],
  parser: '@typescript-eslint/parser',
  plugins: ['react-refresh'],
  rules: {
    'react-refresh/only-export-components': [
      'warn',
      { allowConstantExport: true },
    ],
    'react/prop-types': 'off',
  },
  settings: {
    react: {
      version: 'detect',
    },
  },
};
EOF

echo ""
echo "✅ React + Inertia.js setup completed successfully!"
echo ""
echo "📋 NEXT STEPS:"
echo ""
echo "1. 🐳 If using Docker, run the following to remove Vite and install React images:"
echo "   make dev"
echo ""
echo "2. 🔧 If not using Docker, you can start the development server with:"
echo "   npm run dev"
echo ""
echo "3. 🧪 Run tests with:"
echo "   npm run test"
echo ""
echo "4. 🏗️  Build for production with:"
echo "   npm run build"
echo ""
echo "5. 🔍 Check code quality with:"
echo "   npm run lint"
echo "   npm run type-check"
echo ""
echo "📁 Created files:"
echo "   • resources/js/app.jsx (React + Inertia entry point)"
echo "   • resources/js/Pages/Welcome.jsx (Sample React page)"
echo "   • resources/js/Layouts/AppLayout.jsx (Main layout component)"
echo "   • resources/js/Components/ (React UI components)"
echo "   • resources/js/Stores/counter.js (Zustand store example)"
echo "   • tests/React/ (React component tests)"
echo "   • package.json (Updated with React dependencies)"
echo "   • vite.config.js (React + Laravel configuration)"
echo "   • tsconfig.json (TypeScript configuration)"
echo "   • .eslintrc.js (ESLint configuration)"
echo ""
echo "🎉 Your Laravel application is now configured with React + Inertia.js!"
echo "   Visit your application to see the new React-powered interface."
echo ""
