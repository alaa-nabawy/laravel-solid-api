# Vue.js Setup Guide

<p align="center">
<img src="https://img.shields.io/badge/Vue.js-3.4+-4FC08D?style=flat&logo=vue.js&logoColor=white" alt="Vue.js Version">
<img src="https://img.shields.io/badge/Inertia.js-1.0+-9553E9?style=flat&logo=inertia&logoColor=white" alt="Inertia.js Version">
<img src="https://img.shields.io/badge/Pinia-2.1+-FFD859?style=flat&logo=pinia&logoColor=white" alt="Pinia Version">
</p>

## Overview

This guide covers the complete setup and usage of Vue.js 3 with Inertia.js in your Laravel application. The setup provides a modern, reactive frontend with server-side routing and seamless Laravel integration.

## Table of Contents

- [Quick Setup](#quick-setup)
- [What Gets Installed](#what-gets-installed)
- [Project Structure](#project-structure)
- [Configuration Files](#configuration-files)
- [Development Workflow](#development-workflow)
- [Component Development](#component-development)
- [State Management](#state-management)
- [Testing](#testing)
- [Advanced Usage](#advanced-usage)
- [Troubleshooting](#troubleshooting)

## Quick Setup

### Prerequisites

```bash
# Ensure Laravel development environment is running
make dev

# Verify Docker containers are up
make status
```

### Installation

```bash
# Run the Vue.js setup script
make setup-vue
```

**What happens during setup:**

1. ⚠️ Removes any existing React dependencies and configurations
2. 📦 Installs Vue 3 ecosystem packages
3. 🔧 Configures Vite for Vue development
4. 📁 Creates component directory structure
5. 🎨 Installs sample components and layouts
6. 🧪 Sets up testing environment
7. 🚀 Configures development server

### Verification

```bash
# Start development environment
make dev

# Visit your application
# Laravel: http://localhost:8000
# Vite Dev Server: http://localhost:5173
```

You should see the Vue.js welcome page with interactive components.

## What Gets Installed

### Core Dependencies

```json
{
    "dependencies": {
        "vue": "^3.4.0",
        "@inertiajs/vue3": "^1.0.0",
        "vue-router": "^4.2.0",
        "pinia": "^2.1.0",
        "@headlessui/vue": "^1.7.0",
        "@heroicons/vue": "^2.0.0",
        "axios": "^1.8.2"
    }
}
```

### Development Dependencies

```json
{
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "@vitejs/plugin-vue": "^6.0.0",
        "@vue/compiler-sfc": "^3.4.0",
        "@vue/test-utils": "^2.4.0",
        "eslint": "^8.57.0",
        "eslint-plugin-vue": "^9.20.0",
        "happy-dom": "^12.10.0",
        "jsdom": "^23.0.0",
        "vitest": "^1.1.0",
        "@vitest/ui": "^1.1.0",
        "vue-tsc": "^1.8.0"
    }
}
```

### Package Scripts

```json
{
    "scripts": {
        "lint": "eslint resources/js --ext .js,.vue --fix",
        "lint:check": "eslint resources/js --ext .js,.vue",
        "build": "vite build",
        "dev": "vite",
        "test": "vitest",
        "test:ui": "vitest --ui",
        "test:coverage": "vitest --coverage",
        "type-check": "vue-tsc --noEmit"
    }
}
```

## Project Structure

### Directory Layout

```
resources/js/
├── Components/              # Reusable Vue components
│   ├── NavLink.vue         # Navigation link component
│   ├── Dropdown.vue        # Dropdown menu component
│   ├── DropdownLink.vue    # Dropdown item component
│   └── ...                 # Your custom components
├── Layouts/                # Page layouts
│   └── AppLayout.vue       # Main application layout
├── Pages/                  # Inertia.js pages
│   └── Welcome.vue         # Demo welcome page
├── Stores/                 # Pinia stores
│   └── counter.js          # Example counter store
├── Composables/            # Vue composables
│   └── ...                 # Your composables
├── Types/                  # TypeScript definitions
│   └── ...                 # Type definitions
├── app.js                  # Application entry point
└── bootstrap.js            # Laravel Echo, Axios setup

tests/Vue/                  # Vue-specific tests
├── Welcome.test.js         # Component test example
└── setup.js               # Test environment setup

resources/views/layouts/
└── app.blade.php          # Main Blade layout for Inertia
```

### File Naming Conventions

- **Components**: PascalCase (e.g., `UserProfile.vue`)
- **Pages**: PascalCase (e.g., `UserDashboard.vue`)
- **Composables**: camelCase with `use` prefix (e.g., `useUserData.js`)
- **Stores**: camelCase (e.g., `userStore.js`)

## Configuration Files

### Vite Configuration (`vite.config.js`)

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
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
        tailwindcss(),
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
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
    },
    server: {
        host: "0.0.0.0",
        port: 5173,
        hmr: {
            host: "localhost",
        },
    },
    test: {
        globals: true,
        environment: "happy-dom",
        setupFiles: ["tests/Vue/setup.js"],
    },
});
```

### Application Entry Point (`resources/js/app.js`)

```javascript
import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createPinia } from "pinia";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: "#4F46E5",
        showSpinner: true,
    },
});
```

### Blade Layout (`resources/views/layouts/app.blade.php`)

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
            rel="stylesheet"
        />

        <!-- Scripts -->
        @routes @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
```

## Development Workflow

### Daily Development Commands

```bash
# Start development environment
make dev

# Access workspace for frontend commands
make shell

# Inside workspace:
npm run dev        # Start Vite dev server with HMR
npm run build      # Build for production
npm run test       # Run tests
npm run test:ui    # Run tests with UI interface
npm run lint       # Lint and fix Vue files
npm run type-check # TypeScript type checking
```

### Hot Module Replacement (HMR)

Vite provides instant feedback during development:

- **Component Updates**: Preserves component state
- **Style Changes**: Instant CSS updates
- **Template Changes**: Fast re-rendering
- **Script Changes**: Automatic reloading

### Path Aliases

Use configured aliases for clean imports:

```javascript
// Instead of relative paths
import UserCard from "../../../Components/UserCard.vue";

// Use aliases
import UserCard from "@components/UserCard.vue";
import { useUserStore } from "@stores/userStore";
import useAuth from "@composables/useAuth";
```

## Component Development

### Basic Component Structure

```vue
<template>
    <div class="user-card">
        <h3>{{ user.name }}</h3>
        <p>{{ user.email }}</p>
        <button @click="handleClick" :disabled="loading">
            {{ loading ? "Loading..." : "Action" }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useUserStore } from "@stores/userStore";

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Emits
const emit = defineEmits(["user-updated"]);

// State
const loading = ref(false);

// Store
const userStore = useUserStore();

// Computed
const isActive = computed(() => props.user.status === "active");

// Methods
const handleClick = async () => {
    loading.value = true;
    try {
        await userStore.updateUser(props.user.id);
        emit("user-updated", props.user);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.user-card {
    @apply bg-white rounded-lg shadow-md p-6;
}
</style>
```

### Inertia.js Page Component

```vue
<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Welcome, {{ user.name }}!
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";

// Props from Laravel controller
defineProps({
    user: Object,
});
</script>
```

### Using Inertia.js Features

```vue
<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const form = ref({
    name: "",
    email: "",
});

const errors = ref({});
const processing = ref(false);

// Navigate to another page
const goToProfile = () => {
    router.visit("/profile");
};

// Submit form with Inertia
const submit = () => {
    router.post("/users", form.value, {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        onError: (formErrors) => (errors.value = formErrors),
        onSuccess: () => {
            form.value = { name: "", email: "" };
            errors.value = {};
        },
    });
};
</script>
```

## State Management

### Pinia Store Example

```javascript
// stores/userStore.js
import { defineStore } from "pinia";
import axios from "axios";

export const useUserStore = defineStore("user", {
    state: () => ({
        users: [],
        currentUser: null,
        loading: false,
        error: null,
    }),

    getters: {
        activeUsers: (state) => state.users.filter((user) => user.active),
        userCount: (state) => state.users.length,
    },

    actions: {
        async fetchUsers() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/users");
                this.users = response.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async updateUser(userId, data) {
            try {
                const response = await axios.put(`/api/users/${userId}`, data);
                const index = this.users.findIndex(
                    (user) => user.id === userId,
                );
                if (index !== -1) {
                    this.users[index] = response.data;
                }
                return response.data;
            } catch (error) {
                this.error = error.message;
                throw error;
            }
        },
    },
});
```

### Using Store in Components

```vue
<script setup>
import { useUserStore } from "@stores/userStore";
import { computed, onMounted } from "vue";

const userStore = useUserStore();

// Reactive state
const users = computed(() => userStore.users);
const loading = computed(() => userStore.loading);
const activeUsers = computed(() => userStore.activeUsers);

// Actions
const fetchUsers = () => userStore.fetchUsers();

// Lifecycle
onMounted(() => {
    fetchUsers();
});
</script>
```

### Composables for Reusable Logic

```javascript
// composables/useAuth.js
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";

export default function useAuth() {
    const user = ref(null);
    const loading = ref(false);

    const isAuthenticated = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === "admin");

    const login = async (credentials) => {
        loading.value = true;
        try {
            await router.post("/login", credentials);
        } finally {
            loading.value = false;
        }
    };

    const logout = () => {
        router.post("/logout");
    };

    return {
        user,
        loading,
        isAuthenticated,
        isAdmin,
        login,
        logout,
    };
}
```

## Testing

### Test Setup (`tests/Vue/setup.js`)

```javascript
import { vi } from "vitest";
import { config } from "@vue/test-utils";

// Mock Inertia.js
vi.mock("@inertiajs/vue3", () => ({
    Head: { template: "<div></div>" },
    Link: { template: "<a><slot /></a>" },
    router: {
        visit: vi.fn(),
        post: vi.fn(),
        get: vi.fn(),
    },
}));

// Global test configuration
config.global.mocks = {
    route: vi.fn(),
};
```

### Component Testing

```javascript
// tests/Vue/Components/UserCard.test.js
import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import UserCard from "@/Components/UserCard.vue";

describe("UserCard", () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it("renders user information correctly", () => {
        const user = {
            id: 1,
            name: "John Doe",
            email: "john@example.com",
            status: "active",
        };

        const wrapper = mount(UserCard, {
            props: { user },
        });

        expect(wrapper.text()).toContain("John Doe");
        expect(wrapper.text()).toContain("john@example.com");
    });

    it("emits user-updated event when action is clicked", async () => {
        const user = { id: 1, name: "John Doe", email: "john@example.com" };
        const wrapper = mount(UserCard, {
            props: { user },
        });

        await wrapper.find("button").trigger("click");

        expect(wrapper.emitted("user-updated")).toBeTruthy();
        expect(wrapper.emitted("user-updated")[0]).toEqual([user]);
    });
});
```

### Store Testing

```javascript
// tests/Vue/Stores/userStore.test.js
import { describe, it, expect, vi, beforeEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useUserStore } from "@/Stores/userStore";
import axios from "axios";

vi.mock("axios");

describe("User Store", () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it("fetches users successfully", async () => {
        const mockUsers = [
            { id: 1, name: "John" },
            { id: 2, name: "Jane" },
        ];
        axios.get.mockResolvedValue({ data: mockUsers });

        const store = useUserStore();
        await store.fetchUsers();

        expect(store.users).toEqual(mockUsers);
        expect(store.loading).toBe(false);
        expect(store.error).toBe(null);
    });

    it("handles fetch errors", async () => {
        const errorMessage = "Network Error";
        axios.get.mockRejectedValue(new Error(errorMessage));

        const store = useUserStore();
        await store.fetchUsers();

        expect(store.users).toEqual([]);
        expect(store.loading).toBe(false);
        expect(store.error).toBe(errorMessage);
    });
});
```

## Advanced Usage

### Custom Directives

```javascript
// directives/focus.js
export const focus = {
    mounted(el) {
        el.focus();
    },
};

// Register in app.js
import { focus } from "./directives/focus";

app.directive("focus", focus);
```

### Plugins

```javascript
// plugins/api.js
export default {
    install(app, options) {
        app.config.globalProperties.$api = {
            get: (url) => axios.get(url),
            post: (url, data) => axios.post(url, data),
        };
    },
};

// Register in app.js
import apiPlugin from "./plugins/api";
app.use(apiPlugin);
```

### TypeScript Support

```typescript
// types/User.ts
export interface User {
  id: number
  name: string
  email: string
  status: 'active' | 'inactive'
  created_at: string
  updated_at: string
}

// Component with TypeScript
<script setup lang="ts">
import type { User } from '@/types/User'

interface Props {
  user: User
}

const props = defineProps<Props>()
</script>
```

## Troubleshooting

### Common Issues

**1. Vite Dev Server Not Starting**

```bash
# Check if port 5173 is available
lsof -i :5173

# Restart development environment
make dev-restart
```

**2. Component Not Found Errors**

```bash
# Verify file naming (PascalCase for components)
# Check import paths and aliases
# Ensure component is properly exported
```

**3. Pinia Store Issues**

```bash
# Ensure Pinia is properly installed
# Check store is properly defined with defineStore
# Verify store is imported correctly
```

**4. Hot Module Replacement Not Working**

```bash
# Clear browser cache
# Restart Vite dev server
npm run dev
```

**5. Build Errors**

```bash
# Check for TypeScript errors
npm run type-check

# Lint code
npm run lint

# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Performance Tips

1. **Use `v-memo` for expensive lists**
2. **Implement lazy loading for routes**
3. **Use `shallowRef` for large objects**
4. **Optimize bundle size with tree shaking**
5. **Use `defineAsyncComponent` for code splitting**

### Debugging

```javascript
// Enable Vue devtools in development
app.config.devtools = true;

// Debug reactive state
import { toRaw } from "vue";
console.log(toRaw(reactiveObject));

// Debug Pinia stores
const store = useUserStore();
console.log(store.$state);
```

---

**Next Steps:**

- Explore [React setup](./react-setup.md) for comparison
- Learn about [switching frameworks](./switching-frameworks.md)
- Check out [advanced configurations](./advanced-configuration.md)
