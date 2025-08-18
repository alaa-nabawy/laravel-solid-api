# Advanced Frontend Configuration

<p align="center">
<img src="https://img.shields.io/badge/Vite-5.0+-646CFF?style=flat&logo=vite&logoColor=white" alt="Vite">
<img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=flat&logo=docker&logoColor=white" alt="Docker">
<img src="https://img.shields.io/badge/Laravel-11+-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel">
</p>

## Overview

This guide covers advanced configuration options for your frontend development environment, including Vite optimization, Docker customization, build configurations, and deployment strategies.

## Table of Contents

- [Vite Advanced Configuration](#vite-advanced-configuration)
- [Docker Environment Customization](#docker-environment-customization)
- [Build Optimization](#build-optimization)
- [Development Tools](#development-tools)
- [Environment Variables](#environment-variables)
- [Asset Management](#asset-management)
- [Performance Optimization](#performance-optimization)
- [Security Configuration](#security-configuration)
- [Monitoring and Debugging](#monitoring-and-debugging)
- [Custom Plugins](#custom-plugins)

## Vite Advanced Configuration

### Custom Vite Configuration

```javascript
// vite.config.js - Advanced Configuration
import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue"; // or react
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";
import { visualizer } from "rollup-plugin-visualizer";
import { defineConfig as defineVitestConfig } from "vitest/config";

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const isProduction = mode === "production";
    const isDevelopment = mode === "development";

    return {
        plugins: [
            laravel({
                input: [
                    "resources/css/app.css",
                    "resources/js/app.js", // or app.jsx
                ],
                refresh: [
                    "resources/routes/**",
                    "routes/**",
                    "resources/views/**",
                ],
                detectTls: env.VITE_DEV_SERVER_KEY && env.VITE_DEV_SERVER_CERT,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
                script: {
                    defineModel: true,
                    propsDestructure: true,
                },
            }),
            tailwindcss(),
            // Bundle analyzer for production builds
            isProduction &&
                visualizer({
                    filename: "dist/stats.html",
                    open: true,
                    gzipSize: true,
                    brotliSize: true,
                }),
        ].filter(Boolean),

        resolve: {
            alias: {
                "@": resolve(__dirname, "resources/js"),
                "@components": resolve(__dirname, "resources/js/Components"),
                "@layouts": resolve(__dirname, "resources/js/Layouts"),
                "@pages": resolve(__dirname, "resources/js/Pages"),
                "@stores": resolve(__dirname, "resources/js/Stores"),
                "@composables": resolve(__dirname, "resources/js/Composables"),
                "@utils": resolve(__dirname, "resources/js/Utils"),
                "@assets": resolve(__dirname, "resources/assets"),
                "@styles": resolve(__dirname, "resources/css"),
            },
        },

        define: {
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: !isProduction,
            __DEV__: isDevelopment,
            __PROD__: isProduction,
        },

        server: {
            host: "0.0.0.0",
            port: parseInt(env.VITE_PORT) || 5173,
            strictPort: true,
            hmr: {
                host: env.VITE_HMR_HOST || "localhost",
                port: parseInt(env.VITE_HMR_PORT) || 5173,
            },
            watch: {
                usePolling: env.VITE_USE_POLLING === "true",
                interval: 1000,
            },
            cors: true,
            headers: {
                "Access-Control-Allow-Origin": "*",
                "Access-Control-Allow-Methods":
                    "GET, POST, PUT, DELETE, OPTIONS",
                "Access-Control-Allow-Headers": "Content-Type, Authorization",
            },
        },

        build: {
            outDir: "public/build",
            emptyOutDir: true,
            manifest: true,
            rollupOptions: {
                input: {
                    app: resolve(__dirname, "resources/js/app.js"),
                    // Add additional entry points if needed
                },
                output: {
                    manualChunks: {
                        vendor: ["vue", "axios"], // or ['react', 'react-dom', 'axios']
                        ui: ["@headlessui/vue", "@heroicons/vue"], // or ['@headlessui/react', '@heroicons/react']
                    },
                },
            },
            chunkSizeWarningLimit: 1000,
            sourcemap: !isProduction,
            minify: isProduction ? "terser" : false,
            terserOptions: {
                compress: {
                    drop_console: isProduction,
                    drop_debugger: isProduction,
                },
            },
        },

        css: {
            devSourcemap: isDevelopment,
            preprocessorOptions: {
                scss: {
                    additionalData: `@import "@styles/variables.scss";`,
                },
            },
        },

        optimizeDeps: {
            include: [
                "vue", // or 'react', 'react-dom'
                "axios",
                "@inertiajs/vue3", // or '@inertiajs/react'
                "pinia", // or '@reduxjs/toolkit', 'react-redux'
            ],
            exclude: ["@vite/client", "@vite/env"],
        },

        test: {
            globals: true,
            environment: "happy-dom",
            setupFiles: ["tests/Vue/setup.js"], // or "tests/React/setup.js"
            coverage: {
                provider: "v8",
                reporter: ["text", "json", "html"],
                exclude: ["node_modules/", "tests/", "**/*.d.ts"],
            },
        },

        esbuild: {
            drop: isProduction ? ["console", "debugger"] : [],
        },
    };
});
```

### Environment-Specific Configurations

```javascript
// vite.config.development.js
import { defineConfig } from "vite";
import baseConfig from "./vite.config.js";

export default defineConfig({
    ...baseConfig,
    server: {
        ...baseConfig.server,
        open: true,
        cors: true,
    },
    build: {
        ...baseConfig.build,
        sourcemap: true,
        minify: false,
    },
});

// vite.config.production.js
import { defineConfig } from "vite";
import baseConfig from "./vite.config.js";
import { compression } from "vite-plugin-compression";

export default defineConfig({
    ...baseConfig,
    plugins: [
        ...baseConfig.plugins,
        compression({
            algorithm: "gzip",
            ext: ".gz",
        }),
        compression({
            algorithm: "brotliCompress",
            ext: ".br",
        }),
    ],
    build: {
        ...baseConfig.build,
        sourcemap: false,
        minify: "terser",
        rollupOptions: {
            ...baseConfig.build.rollupOptions,
            external: ["fsevents"],
        },
    },
});
```

## Docker Environment Customization

### Custom Docker Compose Override

```yaml
# compose.frontend.yaml - Advanced Frontend Configuration
version: "3.8"

services:
    workspace:
        environment:
            - VITE_DEV_SERVER_HOST=0.0.0.0
            - VITE_DEV_SERVER_PORT=5173
            - VITE_HMR_HOST=localhost
            - VITE_HMR_PORT=5173
            - VITE_USE_POLLING=false
            - NODE_OPTIONS=--max-old-space-size=4096
        volumes:
            - ./:/var/www
            - frontend_node_modules:/var/www/node_modules
            - frontend_cache:/var/www/.vite
        working_dir: /var/www

    vite:
        build:
            context: ./docker/vite
            dockerfile: Dockerfile
        container_name: "${APP_NAME}-vite"
        ports:
            - "${VITE_PORT:-5173}:5173"
        volumes:
            - ./:/var/www
            - frontend_node_modules:/var/www/node_modules
            - frontend_cache:/var/www/.vite
        environment:
            - VITE_DEV_SERVER_HOST=0.0.0.0
            - VITE_DEV_SERVER_PORT=5173
            - CHOKIDAR_USEPOLLING=true
        command: npm run dev
        depends_on:
            - workspace
        networks:
            - laravel
        restart: unless-stopped

    nginx:
        volumes:
            - ./docker/nginx/frontend.conf:/etc/nginx/conf.d/frontend.conf

volumes:
    frontend_node_modules:
        driver: local
    frontend_cache:
        driver: local

networks:
    laravel:
        driver: bridge
```

### Custom Vite Dockerfile

```dockerfile
# docker/vite/Dockerfile
FROM node:20-alpine

# Install dependencies for native modules
RUN apk add --no-cache \
    python3 \
    make \
    g++ \
    git

# Set working directory
WORKDIR /var/www

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production=false

# Copy source code
COPY . .

# Expose port
EXPOSE 5173

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:5173 || exit 1

# Start development server
CMD ["npm", "run", "dev"]
```

### Nginx Configuration for Frontend

```nginx
# docker/nginx/frontend.conf
server {
    listen 80;
    server_name frontend.local;

    # Vite dev server proxy
    location / {
        proxy_pass http://vite:5173;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;

        # WebSocket support for HMR
        proxy_set_header Sec-WebSocket-Extensions $http_sec_websocket_extensions;
        proxy_set_header Sec-WebSocket-Key $http_sec_websocket_key;
        proxy_set_header Sec-WebSocket-Version $http_sec_websocket_version;
    }

    # Static assets
    location /build/ {
        alias /var/www/public/build/;
        expires 1y;
        add_header Cache-Control "public, immutable";
        gzip_static on;
    }

    # Error pages
    error_page 404 /404.html;
    error_page 500 502 503 504 /50x.html;
}
```

## Build Optimization

### Production Build Configuration

```javascript
// scripts/build-production.js
import { build } from "vite";
import { resolve } from "path";
import fs from "fs";

const buildProduction = async () => {
    console.log("🚀 Starting production build...");

    try {
        // Clean previous build
        if (fs.existsSync("public/build")) {
            fs.rmSync("public/build", { recursive: true });
        }

        // Build with production config
        await build({
            configFile: "vite.config.production.js",
            mode: "production",
        });

        // Generate build report
        const stats = fs.statSync("public/build");
        console.log("✅ Build completed successfully!");
        console.log(
            `📦 Build size: ${(stats.size / 1024 / 1024).toFixed(2)} MB`,
        );

        // Compress assets
        console.log("🗜️ Compressing assets...");
        // Add compression logic here
    } catch (error) {
        console.error("❌ Build failed:", error);
        process.exit(1);
    }
};

buildProduction();
```

### Bundle Analysis

```json
{
    "scripts": {
        "build:analyze": "npm run build && npx vite-bundle-analyzer public/build",
        "build:stats": "npm run build -- --mode=analyze",
        "build:size": "npm run build && bundlesize"
    },
    "bundlesize": [
        {
            "path": "public/build/assets/*.js",
            "maxSize": "250 kB"
        },
        {
            "path": "public/build/assets/*.css",
            "maxSize": "50 kB"
        }
    ]
}
```

## Development Tools

### ESLint Configuration

```javascript
// .eslintrc.js
module.exports = {
    root: true,
    env: {
        browser: true,
        es2021: true,
        node: true,
    },
    extends: [
        "eslint:recommended",
        "@vue/eslint-config-typescript", // or '@typescript-eslint/recommended'
        "plugin:vue/vue3-recommended", // or 'plugin:react/recommended'
        "plugin:prettier/recommended",
    ],
    parserOptions: {
        ecmaVersion: "latest",
        sourceType: "module",
        parser: "@typescript-eslint/parser",
    },
    plugins: [
        "vue", // or 'react'
        "@typescript-eslint",
        "prettier",
    ],
    rules: {
        "no-console": process.env.NODE_ENV === "production" ? "warn" : "off",
        "no-debugger": process.env.NODE_ENV === "production" ? "warn" : "off",
        "vue/multi-word-component-names": "off",
        "vue/no-v-html": "warn",
        "@typescript-eslint/no-unused-vars": "warn",
        "prettier/prettier": "error",
    },
    overrides: [
        {
            files: ["**/__tests__/**/*", "**/*.test.*"],
            env: {
                jest: true,
                vitest: true,
            },
        },
    ],
};
```

### Prettier Configuration

```json
{
    "semi": true,
    "singleQuote": true,
    "tabWidth": 2,
    "trailingComma": "es5",
    "printWidth": 80,
    "bracketSpacing": true,
    "arrowParens": "avoid",
    "endOfLine": "lf",
    "vueIndentScriptAndStyle": false,
    "overrides": [
        {
            "files": "*.vue",
            "options": {
                "parser": "vue"
            }
        },
        {
            "files": ["*.jsx", "*.tsx"],
            "options": {
                "parser": "typescript"
            }
        }
    ]
}
```

### TypeScript Configuration

```json
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
        "jsx": "preserve",
        "strict": true,
        "noUnusedLocals": true,
        "noUnusedParameters": true,
        "noFallthroughCasesInSwitch": true,
        "baseUrl": ".",
        "paths": {
            "@/*": ["resources/js/*"],
            "@components/*": ["resources/js/Components/*"],
            "@layouts/*": ["resources/js/Layouts/*"],
            "@pages/*": ["resources/js/Pages/*"],
            "@stores/*": ["resources/js/Stores/*"],
            "@composables/*": ["resources/js/Composables/*"],
            "@utils/*": ["resources/js/Utils/*"]
        },
        "types": ["vite/client", "vitest/globals"]
    },
    "include": [
        "resources/js/**/*.ts",
        "resources/js/**/*.tsx",
        "resources/js/**/*.vue",
        "tests/**/*.ts",
        "tests/**/*.tsx"
    ],
    "exclude": ["node_modules", "public", "vendor"]
}
```

## Environment Variables

### Environment Configuration

```bash
# .env.development
VITE_APP_NAME="Laravel API SOLID (Dev)"
VITE_APP_ENV=development
VITE_APP_DEBUG=true
VITE_APP_URL=http://localhost:8000
VITE_API_URL=http://localhost:8000/api
VITE_WS_URL=ws://localhost:6001

# Vite Dev Server
VITE_PORT=5173
VITE_HMR_HOST=localhost
VITE_HMR_PORT=5173
VITE_USE_POLLING=false

# Development Tools
VITE_DEVTOOLS=true
VITE_SOURCE_MAPS=true
VITE_HOT_RELOAD=true

# Feature Flags
VITE_FEATURE_ANALYTICS=false
VITE_FEATURE_CHAT=true
VITE_FEATURE_NOTIFICATIONS=true
```

```bash
# .env.production
VITE_APP_NAME="Laravel API SOLID"
VITE_APP_ENV=production
VITE_APP_DEBUG=false
VITE_APP_URL=https://your-domain.com
VITE_API_URL=https://your-domain.com/api
VITE_WS_URL=wss://your-domain.com:6001

# Production Optimizations
VITE_SOURCE_MAPS=false
VITE_MINIFY=true
VITE_TREE_SHAKING=true

# Feature Flags
VITE_FEATURE_ANALYTICS=true
VITE_FEATURE_CHAT=true
VITE_FEATURE_NOTIFICATIONS=true

# CDN Configuration
VITE_CDN_URL=https://cdn.your-domain.com
VITE_ASSETS_URL=https://assets.your-domain.com
```

### Environment Type Definitions

```typescript
// resources/js/types/env.d.ts
interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    readonly VITE_APP_ENV: string;
    readonly VITE_APP_DEBUG: string;
    readonly VITE_APP_URL: string;
    readonly VITE_API_URL: string;
    readonly VITE_WS_URL: string;
    readonly VITE_FEATURE_ANALYTICS: string;
    readonly VITE_FEATURE_CHAT: string;
    readonly VITE_FEATURE_NOTIFICATIONS: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
```

## Asset Management

### Asset Processing Pipeline

```javascript
// vite/plugins/assets.js
import { defineConfig } from "vite";
import { createSvgIconsPlugin } from "vite-plugin-svg-icons";
import { resolve } from "path";

export const assetsPlugin = () => {
    return [
        // SVG Icons
        createSvgIconsPlugin({
            iconDirs: [resolve(process.cwd(), "resources/assets/icons")],
            symbolId: "icon-[dir]-[name]",
            inject: "body-last",
            customDomId: "__svg__icons__dom__",
        }),

        // Image optimization
        {
            name: "image-optimization",
            generateBundle(options, bundle) {
                // Add image optimization logic
            },
        },
    ];
};
```

### Static Asset Organization

```
resources/assets/
├── images/
│   ├── logos/
│   ├── backgrounds/
│   ├── icons/
│   └── avatars/
├── fonts/
│   ├── inter/
│   └── roboto/
├── videos/
├── documents/
└── data/
    ├── locales/
    └── configs/
```

### Asset Helper Functions

```javascript
// resources/js/utils/assets.js
export const getAssetUrl = (path) => {
    const baseUrl = import.meta.env.VITE_ASSETS_URL || "";
    return `${baseUrl}/assets/${path}`;
};

export const getImageUrl = (filename, size = "original") => {
    const baseUrl = import.meta.env.VITE_CDN_URL || "";
    return `${baseUrl}/images/${size}/${filename}`;
};

export const preloadImage = (src) => {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
};

export const lazyLoadImage = (element, src) => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.src = src;
                observer.unobserve(entry.target);
            }
        });
    });

    observer.observe(element);
};
```

## Performance Optimization

### Code Splitting Strategy

```javascript
// resources/js/router/index.js (Vue)
import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        name: "Home",
        component: () => import("@pages/Home.vue"),
    },
    {
        path: "/dashboard",
        name: "Dashboard",
        component: () => import("@pages/Dashboard.vue"),
        meta: { requiresAuth: true },
    },
    {
        path: "/admin",
        name: "Admin",
        component: () => import("@pages/Admin.vue"),
        meta: { requiresAuth: true, requiresAdmin: true },
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
```

```jsx
// resources/js/router/index.jsx (React)
import { lazy, Suspense } from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import LoadingSpinner from "@components/LoadingSpinner";

const Home = lazy(() => import("@pages/Home"));
const Dashboard = lazy(() => import("@pages/Dashboard"));
const Admin = lazy(() => import("@pages/Admin"));

const AppRouter = () => {
    return (
        <BrowserRouter>
            <Suspense fallback={<LoadingSpinner />}>
                <Routes>
                    <Route path="/" element={<Home />} />
                    <Route path="/dashboard" element={<Dashboard />} />
                    <Route path="/admin" element={<Admin />} />
                </Routes>
            </Suspense>
        </BrowserRouter>
    );
};

export default AppRouter;
```

### Bundle Optimization

```javascript
// vite.config.js - Bundle optimization
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor chunks
                    "vendor-vue": ["vue", "@inertiajs/vue3", "pinia"],
                    "vendor-react": ["react", "react-dom", "@inertiajs/react"],
                    "vendor-ui": ["@headlessui/vue", "@heroicons/vue"],
                    "vendor-utils": ["axios", "lodash", "date-fns"],

                    // Feature chunks
                    "feature-auth": [
                        "./resources/js/Pages/Auth",
                        "./resources/js/Components/Auth",
                    ],
                    "feature-dashboard": [
                        "./resources/js/Pages/Dashboard",
                        "./resources/js/Components/Dashboard",
                    ],
                },
                chunkFileNames: (chunkInfo) => {
                    const facadeModuleId = chunkInfo.facadeModuleId
                        ? chunkInfo.facadeModuleId.split("/").pop()
                        : "chunk";
                    return `js/${facadeModuleId}-[hash].js`;
                },
            },
        },
    },
});
```

### Performance Monitoring

```javascript
// resources/js/utils/performance.js
export class PerformanceMonitor {
    static measurePageLoad() {
        window.addEventListener("load", () => {
            const navigation = performance.getEntriesByType("navigation")[0];
            const loadTime =
                navigation.loadEventEnd - navigation.loadEventStart;

            console.log(`Page load time: ${loadTime}ms`);

            // Send to analytics
            if (import.meta.env.VITE_FEATURE_ANALYTICS === "true") {
                this.sendMetric("page_load_time", loadTime);
            }
        });
    }

    static measureComponentRender(componentName, renderTime) {
        console.log(`${componentName} render time: ${renderTime}ms`);

        if (renderTime > 100) {
            console.warn(`Slow component render: ${componentName}`);
        }
    }

    static sendMetric(name, value) {
        // Implementation for sending metrics to analytics service
        fetch("/api/metrics", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ name, value, timestamp: Date.now() }),
        });
    }
}
```

## Security Configuration

### Content Security Policy

```javascript
// vite/plugins/security.js
export const securityPlugin = () => {
    return {
        name: "security-headers",
        configureServer(server) {
            server.middlewares.use((req, res, next) => {
                // CSP Header
                res.setHeader(
                    "Content-Security-Policy",
                    "default-src 'self'; " +
                        "script-src 'self' 'unsafe-eval' 'unsafe-inline'; " +
                        "style-src 'self' 'unsafe-inline'; " +
                        "img-src 'self' data: https:; " +
                        "font-src 'self' https:; " +
                        "connect-src 'self' ws: wss:;",
                );

                // Other security headers
                res.setHeader("X-Content-Type-Options", "nosniff");
                res.setHeader("X-Frame-Options", "DENY");
                res.setHeader("X-XSS-Protection", "1; mode=block");

                next();
            });
        },
    };
};
```

### Environment Variable Validation

```javascript
// resources/js/utils/env-validation.js
const requiredEnvVars = ["VITE_APP_NAME", "VITE_APP_URL", "VITE_API_URL"];

const validateEnvironment = () => {
    const missing = requiredEnvVars.filter(
        (varName) => !import.meta.env[varName],
    );

    if (missing.length > 0) {
        throw new Error(
            `Missing required environment variables: ${missing.join(", ")}`,
        );
    }
};

// Validate on app startup
if (import.meta.env.MODE !== "test") {
    validateEnvironment();
}
```

## Monitoring and Debugging

### Development Debugging Tools

```javascript
// resources/js/utils/debug.js
export class DebugTools {
    static enableVueDevtools() {
        if (import.meta.env.DEV && window.__VUE_DEVTOOLS_GLOBAL_HOOK__) {
            window.__VUE_DEVTOOLS_GLOBAL_HOOK__.Vue = Vue;
        }
    }

    static enableReactDevtools() {
        if (import.meta.env.DEV && window.__REACT_DEVTOOLS_GLOBAL_HOOK__) {
            // React DevTools configuration
        }
    }

    static logPerformance() {
        if (import.meta.env.DEV) {
            const observer = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    console.log(`${entry.name}: ${entry.duration}ms`);
                });
            });

            observer.observe({ entryTypes: ["measure", "navigation"] });
        }
    }

    static enableErrorBoundary() {
        window.addEventListener("error", (event) => {
            console.error("Global error:", event.error);

            if (import.meta.env.PROD) {
                // Send to error tracking service
                this.reportError(event.error);
            }
        });

        window.addEventListener("unhandledrejection", (event) => {
            console.error("Unhandled promise rejection:", event.reason);

            if (import.meta.env.PROD) {
                this.reportError(event.reason);
            }
        });
    }

    static reportError(error) {
        // Implementation for error reporting service
        fetch("/api/errors", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                message: error.message,
                stack: error.stack,
                url: window.location.href,
                timestamp: new Date().toISOString(),
            }),
        });
    }
}
```

### Production Monitoring

```javascript
// resources/js/utils/monitoring.js
export class ProductionMonitoring {
    static init() {
        if (import.meta.env.PROD) {
            this.setupErrorTracking();
            this.setupPerformanceTracking();
            this.setupUserTracking();
        }
    }

    static setupErrorTracking() {
        // Integration with error tracking services like Sentry
    }

    static setupPerformanceTracking() {
        // Integration with performance monitoring services
    }

    static setupUserTracking() {
        // Integration with analytics services
    }
}
```

## Custom Plugins

### Auto-Import Plugin

```javascript
// vite/plugins/auto-import.js
import { defineConfig } from "vite";

export const autoImportPlugin = () => {
    return {
        name: "auto-import",
        transform(code, id) {
            if (id.endsWith(".vue") || id.endsWith(".jsx")) {
                // Auto-import commonly used utilities
                const imports = [
                    "import { ref, reactive, computed, onMounted } from 'vue';",
                    "import { useRouter, useRoute } from 'vue-router';",
                    "import { useStore } from 'pinia';",
                ];

                return {
                    code: imports.join("\n") + "\n" + code,
                    map: null,
                };
            }
        },
    };
};
```

### Component Generator Plugin

```javascript
// vite/plugins/component-generator.js
import fs from "fs";
import path from "path";

export const componentGeneratorPlugin = () => {
    return {
        name: "component-generator",
        configureServer(server) {
            server.middlewares.use("/api/generate-component", (req, res) => {
                if (req.method === "POST") {
                    // Component generation logic
                    const { name, type } = JSON.parse(req.body);
                    const template = this.getTemplate(type);
                    const componentPath = path.join(
                        "resources/js/Components",
                        `${name}.${type === "vue" ? "vue" : "jsx"}`,
                    );

                    fs.writeFileSync(componentPath, template);
                    res.end(JSON.stringify({ success: true }));
                }
            });
        },

        getTemplate(type) {
            // Return component templates based on type
            return type === "vue" ? this.vueTemplate() : this.reactTemplate();
        },

        vueTemplate() {
            return `<template>
  <div class="component">
    <!-- Component content -->
  </div>
</template>

<script setup>
// Component logic
</script>

<style scoped>
.component {
  /* Component styles */
}
</style>`;
        },

        reactTemplate() {
            return `import React from 'react';

const Component = () => {
    return (
        <div className="component">
            {/* Component content */}
        </div>
    );
};

export default Component;`;
        },
    };
};
```

---

**Next Steps:**

- Review [Vue.js setup guide](./vue-setup.md)
- Review [React setup guide](./react-setup.md)
- Check [switching frameworks guide](./switching-frameworks.md)
- Explore [deployment strategies](./deployment.md)
