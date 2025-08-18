# Vue.js Setup Guide

This guide covers the automated Vue 3 + Inertia.js setup for Laravel API SOLID, providing a modern full-stack development experience.

## Overview

The `make setup-vue` command automates the complete setup of Vue 3 with Inertia.js, transforming your Laravel API into a modern SPA-like application while maintaining server-side routing benefits.

## Prerequisites

- Development environment must be running (`make dev`)
- Docker containers must be healthy
- Node.js and npm available in workspace container

## Quick Setup

```bash
# Start development environment
make dev

# Setup Vue 3 + Inertia.js
make setup-vue

# Access workspace for development
make shell
```

## What Gets Installed

### Core Dependencies

- **Vue 3**: Latest Vue.js with Composition API
- **@inertiajs/vue3**: Vue 3 adapter for Inertia.js
- **@inertiajs/inertia-laravel**: Laravel server-side adapter
- **@vitejs/plugin-vue**: Vite plugin for Vue SFC support
- **@vue/compiler-sfc**: Vue Single File Component compiler

### Routing & State Management

- **vue-router**: Client-side routing
- **pinia**: Modern state management for Vue 3

### UI & Styling

- **@headlessui/vue**: Accessible UI components
- **@heroicons/vue**: Beautiful SVG icons
- **@tailwindcss/forms**: Form styling utilities

### Testing Framework

- **@vue/test-utils**: Vue component testing utilities
- **vitest**: Fast unit testing framework
- **jsdom**: DOM implementation for testing
- **@vitest/ui**: Visual testing interface
- **happy-dom**: Lightweight DOM implementation

## Configuration Files

The setup process creates/updates several configuration files:

### package.json

Updated with Vue-specific dependencies and scripts:

```json
{
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview",
        "test": "vitest",
        "test:ui": "vitest --ui",
        "test:coverage": "vitest --coverage",
        "lint": "eslint resources/js --ext .vue,.js,.ts",
        "lint:fix": "eslint resources/js --ext .vue,.js,.ts --fix",
        "type-check": "vue-tsc --noEmit"
    }
}
```

### vite.config.js

Configured for Vue 3 with Laravel integration:

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
            "@components": resolve(__dirname, "resources/js/Components"),
            "@layouts": resolve(__dirname, "resources/js/Layouts"),
            "@pages": resolve(__dirname, "resources/js/Pages"),
            "@stores": resolve(__dirname, "resources/js/Stores"),
            "@composables": resolve(__dirname, "resources/js/Composables"),
        },
    },
    test: {
        globals: true,
        environment: "jsdom",
        setupFiles: ["tests/Vue/setup.js"],
    },
});
```

### resources/js/app.js

Configured for Vue 3 + Inertia.js:

```javascript
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { InertiaProgress } from "@inertiajs/progress";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: "#4F46E5",
    },
});

InertiaProgress.init({ color: "#4F46E5" });
```

## Directory Structure

The setup creates the following directory structure:

```
resources/js/
├── Components/          # Reusable Vue components
│   ├── AppLayout.vue   # Main application layout
│   ├── NavLink.vue     # Navigation link component
│   ├── Dropdown.vue    # Dropdown menu component
│   └── DropdownLink.vue # Dropdown menu item
├── Layouts/            # Page layouts
├── Pages/              # Inertia.js pages
│   └── Welcome.vue     # Demo welcome page
├── Stores/             # Pinia stores
│   └── counter.js      # Example counter store
├── Composables/        # Vue composables
└── app.js             # Main application entry

tests/Vue/
├── setup.js           # Vitest setup file
└── Welcome.test.js    # Example component test
```

## Sample Components

### AppLayout.vue

Main application layout with:

- Responsive navigation
- User authentication handling
- Flash message display
- Dropdown user menu
- Footer

### Welcome.vue

Demo page showcasing:

- Vue 3 Composition API
- Inertia.js integration
- Pinia state management
- API data fetching
- Component interaction

### Navigation Components

- **NavLink.vue**: Active state navigation links
- **Dropdown.vue**: Accessible dropdown menus
- **DropdownLink.vue**: Dropdown menu items

## State Management

### Counter Store Example

```javascript
import { defineStore } from "pinia";

export const useCounterStore = defineStore("counter", {
    state: () => ({
        count: 0,
        history: [],
    }),

    getters: {
        doubleCount: (state) => state.count * 2,
        isEven: (state) => state.count % 2 === 0,
        lastAction: (state) => state.history[state.history.length - 1],
    },

    actions: {
        increment() {
            this.count++;
            this.history.push({ action: "increment", timestamp: Date.now() });
        },

        decrement() {
            this.count--;
            this.history.push({ action: "decrement", timestamp: Date.now() });
        },

        reset() {
            this.count = 0;
            this.history.push({ action: "reset", timestamp: Date.now() });
        },
    },
});
```

## Testing

### Running Tests

```bash
# Inside workspace container
npm run test              # Run all tests
npm run test:ui           # Run with visual interface
npm run test:coverage     # Run with coverage report
```

### Test Setup

Vitest is configured with:

- Global test utilities
- JSDOM environment
- Inertia.js mocks
- Vue Test Utils

### Example Component Test

```javascript
import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import Welcome from "@/Pages/Welcome.vue";

describe("Welcome Component", () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it("renders welcome message", () => {
        const wrapper = mount(Welcome, {
            props: {
                appName: "Test App",
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        expect(wrapper.text()).toContain("Welcome to Test App");
    });
});
```

## Development Workflow

### Starting Development

```bash
# Start Laravel development environment
make dev

# Access workspace
make shell

# Start Vite dev server (inside workspace)
npm run dev
```

### Building for Production

```bash
# Inside workspace
npm run build

# Or from host
make shell -c "npm run build"
```

### Code Quality

```bash
# Lint Vue files
npm run lint

# Fix linting issues
npm run lint:fix

# Type checking (if using TypeScript)
npm run type-check
```

## Integration with Laravel

### Blade Template Updates

The setup automatically updates your Blade templates to use Vite with Vue:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

### Controller Updates

Use Inertia responses in your controllers:

```php
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'appName' => config('app.name'),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
```

## Troubleshooting

### Common Issues

1. **Vite not starting**: Ensure development environment is running
2. **Module not found**: Check path aliases in `vite.config.js`
3. **Tests failing**: Verify test setup file is properly configured
4. **Build errors**: Check for TypeScript errors if using TS

### Useful Commands

```bash
# Check container status
make status

# Restart development environment
make restart

# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json && npm install
```

## Next Steps

1. **Customize Components**: Modify the sample components to fit your design
2. **Add Pages**: Create new Vue pages in `resources/js/Pages/`
3. **Extend Stores**: Add more Pinia stores for your application state
4. **Write Tests**: Add comprehensive tests for your components
5. **Configure TypeScript**: Add TypeScript support for better development experience

## Related Documentation

- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Vite Documentation](https://vitejs.dev/)
- [Vitest Documentation](https://vitest.dev/)
