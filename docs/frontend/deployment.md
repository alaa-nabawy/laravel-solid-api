# Frontend Deployment Guide

<p align="center">
<img src="https://img.shields.io/badge/Docker-Production-2496ED?style=flat&logo=docker&logoColor=white" alt="Docker">
<img src="https://img.shields.io/badge/Nginx-Optimized-009639?style=flat&logo=nginx&logoColor=white" alt="Nginx">
<img src="https://img.shields.io/badge/CDN-Ready-FF6B35?style=flat&logo=cloudflare&logoColor=white" alt="CDN">
</p>

## Overview

This guide covers deployment strategies for your Laravel application with Vue.js or React frontend, including production builds, server configurations, CDN setup, and performance optimizations.

## Table of Contents

- [Production Build Process](#production-build-process)
- [Docker Production Setup](#docker-production-setup)
- [Server Configuration](#server-configuration)
- [CDN Integration](#cdn-integration)
- [Performance Optimization](#performance-optimization)
- [Security Considerations](#security-considerations)
- [Monitoring and Analytics](#monitoring-and-analytics)
- [CI/CD Pipeline](#cicd-pipeline)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

## Production Build Process

### Building for Production

```bash
# Build frontend assets for production
npm run build

# Or using the Makefile
make build-production

# Build with specific environment
NODE_ENV=production npm run build

# Build with analysis
npm run build:analyze
```

### Build Scripts Configuration

```json
{
    "scripts": {
        "build": "vite build",
        "build:production": "NODE_ENV=production vite build --mode production",
        "build:staging": "NODE_ENV=staging vite build --mode staging",
        "build:analyze": "npm run build && npx vite-bundle-analyzer public/build",
        "build:clean": "rm -rf public/build && npm run build",
        "prebuild": "npm run lint && npm run test",
        "postbuild": "npm run build:verify",
        "build:verify": "node scripts/verify-build.js"
    }
}
```

### Build Verification Script

```javascript
// scripts/verify-build.js
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const buildDir = path.join(__dirname, "../public/build");

const verifyBuild = () => {
    console.log("🔍 Verifying production build...");

    // Check if build directory exists
    if (!fs.existsSync(buildDir)) {
        console.error("❌ Build directory not found!");
        process.exit(1);
    }

    // Check for manifest file
    const manifestPath = path.join(buildDir, "manifest.json");
    if (!fs.existsSync(manifestPath)) {
        console.error("❌ Manifest file not found!");
        process.exit(1);
    }

    // Read and validate manifest
    const manifest = JSON.parse(fs.readFileSync(manifestPath, "utf8"));
    const requiredEntries = ["resources/js/app.js", "resources/css/app.css"];

    for (const entry of requiredEntries) {
        if (!manifest[entry]) {
            console.error(`❌ Missing entry in manifest: ${entry}`);
            process.exit(1);
        }
    }

    // Check file sizes
    const stats = fs.statSync(buildDir);
    const sizeInMB = (stats.size / 1024 / 1024).toFixed(2);

    console.log("✅ Build verification passed!");
    console.log(`📦 Build size: ${sizeInMB} MB`);
    console.log(`📁 Build location: ${buildDir}`);

    // Check for large files
    const files = fs.readdirSync(path.join(buildDir, "assets"));
    const largeFiles = files.filter((file) => {
        const filePath = path.join(buildDir, "assets", file);
        const fileStats = fs.statSync(filePath);
        return fileStats.size > 500 * 1024; // 500KB
    });

    if (largeFiles.length > 0) {
        console.warn("⚠️ Large files detected:");
        largeFiles.forEach((file) => {
            const filePath = path.join(buildDir, "assets", file);
            const fileStats = fs.statSync(filePath);
            const fileSizeKB = (fileStats.size / 1024).toFixed(2);
            console.warn(`  - ${file}: ${fileSizeKB} KB`);
        });
    }
};

verifyBuild();
```

## Docker Production Setup

### Production Dockerfile

```dockerfile
# docker/production/Dockerfile
# Multi-stage build for production

# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Install dependencies
RUN npm ci --only=production=false

# Copy source files
COPY resources/ ./resources/
COPY public/ ./public/

# Build frontend assets
RUN npm run build

# Stage 2: PHP production image
FROM php:8.2-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .
COPY --from=frontend-builder /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy configuration files
COPY docker/production/nginx.conf /etc/nginx/nginx.conf
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/production/php.ini /usr/local/etc/php/php.ini

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Production Docker Compose

```yaml
# docker-compose.production.yml
version: "3.8"

services:
    app:
        build:
            context: .
            dockerfile: docker/production/Dockerfile
        container_name: "${APP_NAME}-app"
        restart: unless-stopped
        environment:
            - APP_ENV=production
            - APP_DEBUG=false
            - APP_KEY=${APP_KEY}
            - DB_HOST=database
            - DB_DATABASE=${DB_DATABASE}
            - DB_USERNAME=${DB_USERNAME}
            - DB_PASSWORD=${DB_PASSWORD}
            - REDIS_HOST=redis
        volumes:
            - storage_data:/var/www/storage
            - ./docker/production/nginx.conf:/etc/nginx/nginx.conf:ro
        networks:
            - app-network
        depends_on:
            - database
            - redis

    nginx:
        image: nginx:alpine
        container_name: "${APP_NAME}-nginx"
        restart: unless-stopped
        ports:
            - "80:80"
            - "443:443"
        volumes:
            - ./public:/var/www/public:ro
            - ./docker/production/nginx-proxy.conf:/etc/nginx/conf.d/default.conf:ro
            - ./docker/ssl:/etc/nginx/ssl:ro
        networks:
            - app-network
        depends_on:
            - app

    database:
        image: mysql:8.0
        container_name: "${APP_NAME}-database"
        restart: unless-stopped
        environment:
            - MYSQL_DATABASE=${DB_DATABASE}
            - MYSQL_USER=${DB_USERNAME}
            - MYSQL_PASSWORD=${DB_PASSWORD}
            - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
        volumes:
            - database_data:/var/lib/mysql
        networks:
            - app-network

    redis:
        image: redis:alpine
        container_name: "${APP_NAME}-redis"
        restart: unless-stopped
        volumes:
            - redis_data:/data
        networks:
            - app-network

volumes:
    database_data:
        driver: local
    redis_data:
        driver: local
    storage_data:
        driver: local

networks:
    app-network:
        driver: bridge
```

## Server Configuration

### Nginx Production Configuration

```nginx
# docker/production/nginx-proxy.conf
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    # SSL Configuration
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Root directory
    root /var/www/public;
    index index.php index.html;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json
        image/svg+xml;

    # Static Assets with Long Cache
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";

        # Enable Brotli if available
        location ~* \.(js|css|svg)$ {
            gzip_static on;
            brotli_static on;
        }
    }

    # Vite Build Assets
    location /build/ {
        alias /var/www/public/build/;
        expires 1y;
        add_header Cache-Control "public, immutable";

        # Precompressed files
        location ~* \.(js|css)$ {
            gzip_static on;
            brotli_static on;
        }
    }

    # API Routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;

        # Rate limiting
        limit_req zone=api burst=20 nodelay;

        # CORS Headers
        add_header Access-Control-Allow-Origin "*";
        add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
        add_header Access-Control-Allow-Headers "Content-Type, Authorization";

        if ($request_method = 'OPTIONS') {
            return 204;
        }
    }

    # Laravel Application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;

        # Security
        fastcgi_param HTTP_PROXY "";

        # Timeouts
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(vendor|storage|bootstrap/cache) {
        deny all;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}

# Rate Limiting
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;
}
```

### Supervisor Configuration

```ini
# docker/production/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/worker.log
stopwaitsecs=3600

[program:laravel-schedule]
command=php /var/www/artisan schedule:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/schedule.log
```

## CDN Integration

### CDN Configuration

```javascript
// vite.config.js - CDN Configuration
export default defineConfig(({ mode }) => {
    const isProduction = mode === "production";
    const cdnUrl = process.env.VITE_CDN_URL;

    return {
        base: isProduction && cdnUrl ? cdnUrl : "/",
        build: {
            rollupOptions: {
                output: {
                    assetFileNames: (assetInfo) => {
                        const info = assetInfo.name.split(".");
                        const ext = info[info.length - 1];

                        if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(ext)) {
                            return `images/[name]-[hash][extname]`;
                        }

                        if (/css/i.test(ext)) {
                            return `css/[name]-[hash][extname]`;
                        }

                        return `assets/[name]-[hash][extname]`;
                    },
                    chunkFileNames: "js/[name]-[hash].js",
                    entryFileNames: "js/[name]-[hash].js",
                },
            },
        },
    };
});
```

### Asset Upload Script

```javascript
// scripts/upload-assets.js
import AWS from "aws-sdk";
import fs from "fs";
import path from "path";
import { glob } from "glob";
import mime from "mime-types";

const s3 = new AWS.S3({
    accessKeyId: process.env.AWS_ACCESS_KEY_ID,
    secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY,
    region: process.env.AWS_REGION,
});

const uploadAssets = async () => {
    const buildDir = "public/build";
    const bucket = process.env.AWS_S3_BUCKET;

    console.log("📤 Uploading assets to CDN...");

    const files = await glob(`${buildDir}/**/*`, { nodir: true });

    for (const file of files) {
        const key = file.replace(`${buildDir}/`, "");
        const contentType = mime.lookup(file) || "application/octet-stream";

        const uploadParams = {
            Bucket: bucket,
            Key: key,
            Body: fs.readFileSync(file),
            ContentType: contentType,
            CacheControl: "public, max-age=31536000, immutable",
        };

        // Add compression headers for text files
        if (
            contentType.startsWith("text/") ||
            contentType.includes("javascript") ||
            contentType.includes("json")
        ) {
            uploadParams.ContentEncoding = "gzip";
        }

        try {
            await s3.upload(uploadParams).promise();
            console.log(`✅ Uploaded: ${key}`);
        } catch (error) {
            console.error(`❌ Failed to upload ${key}:`, error);
        }
    }

    console.log("🎉 Asset upload completed!");
};

uploadAssets();
```

### CloudFront Configuration

```json
{
    "Comment": "Laravel Frontend Assets Distribution",
    "DefaultCacheBehavior": {
        "TargetOriginId": "S3-laravel-assets",
        "ViewerProtocolPolicy": "redirect-to-https",
        "CachePolicyId": "4135ea2d-6df8-44a3-9df3-4b5a84be39ad",
        "OriginRequestPolicyId": "88a5eaf4-2fd4-4709-b370-b4c650ea3fcf",
        "Compress": true
    },
    "CacheBehaviors": [
        {
            "PathPattern": "*.js",
            "TargetOriginId": "S3-laravel-assets",
            "ViewerProtocolPolicy": "redirect-to-https",
            "CachePolicyId": "4135ea2d-6df8-44a3-9df3-4b5a84be39ad",
            "Compress": true,
            "TTL": 31536000
        },
        {
            "PathPattern": "*.css",
            "TargetOriginId": "S3-laravel-assets",
            "ViewerProtocolPolicy": "redirect-to-https",
            "CachePolicyId": "4135ea2d-6df8-44a3-9df3-4b5a84be39ad",
            "Compress": true,
            "TTL": 31536000
        },
        {
            "PathPattern": "images/*",
            "TargetOriginId": "S3-laravel-assets",
            "ViewerProtocolPolicy": "redirect-to-https",
            "CachePolicyId": "4135ea2d-6df8-44a3-9df3-4b5a84be39ad",
            "TTL": 31536000
        }
    ]
}
```

## Performance Optimization

### Asset Compression

```javascript
// vite/plugins/compression.js
import { defineConfig } from "vite";
import { compression } from "vite-plugin-compression";

export const compressionPlugin = () => {
    return [
        // Gzip compression
        compression({
            algorithm: "gzip",
            ext: ".gz",
            threshold: 1024,
            deleteOriginFile: false,
        }),

        // Brotli compression
        compression({
            algorithm: "brotliCompress",
            ext: ".br",
            threshold: 1024,
            deleteOriginFile: false,
        }),
    ];
};
```

### Service Worker for Caching

```javascript
// public/sw.js
const CACHE_NAME = "laravel-app-v1";
const urlsToCache = ["/", "/build/assets/app.css", "/build/assets/app.js"];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)),
    );
});

self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return cached version or fetch from network
            return response || fetch(event.request);
        }),
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                }),
            );
        }),
    );
});
```

### Resource Hints

```php
<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.your-domain.com">

    <!-- Preconnect to critical origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://cdn.your-domain.com" crossorigin>

    <!-- Preload critical resources -->
    <link rel="preload" href="{{ Vite::asset('resources/css/app.css') }}" as="style">
    <link rel="preload" href="{{ Vite::asset('resources/js/app.js') }}" as="script">

    <!-- Critical CSS inline -->
    <style>
        /* Critical above-the-fold CSS */
        body { margin: 0; font-family: system-ui, sans-serif; }
        .loading { display: flex; justify-content: center; align-items: center; height: 100vh; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    <div id="app" data-page="{{ json_encode($page) }}"></div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>
```

## Security Considerations

### Environment Variables Security

```bash
# .env.production - Secure configuration
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-32-character-secret-key

# Database
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=laravel_production
DB_USERNAME=laravel_user
DB_PASSWORD=secure-database-password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=secure-redis-password
REDIS_PORT=6379

# Frontend
VITE_APP_NAME="Laravel API SOLID"
VITE_APP_ENV=production
VITE_APP_URL=https://your-domain.com
VITE_API_URL=https://your-domain.com/api
VITE_CDN_URL=https://cdn.your-domain.com

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### Content Security Policy

```php
// app/Http/Middleware/ContentSecurityPolicy.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-eval' https://cdn.your-domain.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.your-domain.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com https://cdn.your-domain.com",
            "connect-src 'self' https://api.your-domain.com wss://your-domain.com",
            "media-src 'self' https://cdn.your-domain.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
```

## Monitoring and Analytics

### Performance Monitoring

```javascript
// resources/js/utils/performance-monitor.js
export class PerformanceMonitor {
    static init() {
        this.measurePageLoad();
        this.measureResourceTiming();
        this.measureUserTiming();
        this.setupErrorTracking();
    }

    static measurePageLoad() {
        window.addEventListener("load", () => {
            const navigation = performance.getEntriesByType("navigation")[0];

            const metrics = {
                dns: navigation.domainLookupEnd - navigation.domainLookupStart,
                tcp: navigation.connectEnd - navigation.connectStart,
                ssl: navigation.connectEnd - navigation.secureConnectionStart,
                ttfb: navigation.responseStart - navigation.requestStart,
                download: navigation.responseEnd - navigation.responseStart,
                dom:
                    navigation.domContentLoadedEventEnd -
                    navigation.navigationStart,
                load: navigation.loadEventEnd - navigation.navigationStart,
            };

            this.sendMetrics("page_load", metrics);
        });
    }

    static measureResourceTiming() {
        const observer = new PerformanceObserver((list) => {
            list.getEntries().forEach((entry) => {
                if (
                    entry.initiatorType === "script" ||
                    entry.initiatorType === "css"
                ) {
                    this.sendMetrics("resource_timing", {
                        name: entry.name,
                        type: entry.initiatorType,
                        duration: entry.duration,
                        size: entry.transferSize,
                    });
                }
            });
        });

        observer.observe({ entryTypes: ["resource"] });
    }

    static measureUserTiming() {
        // Custom timing measurements
        performance.mark("app-start");

        // Measure component render times
        window.addEventListener("app-ready", () => {
            performance.mark("app-ready");
            performance.measure("app-initialization", "app-start", "app-ready");

            const measure =
                performance.getEntriesByName("app-initialization")[0];
            this.sendMetrics("app_timing", {
                initialization: measure.duration,
            });
        });
    }

    static setupErrorTracking() {
        window.addEventListener("error", (event) => {
            this.sendError({
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                stack: event.error?.stack,
            });
        });

        window.addEventListener("unhandledrejection", (event) => {
            this.sendError({
                message: "Unhandled Promise Rejection",
                reason: event.reason,
            });
        });
    }

    static sendMetrics(type, data) {
        if (import.meta.env.PROD) {
            fetch("/api/metrics", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ type, data, timestamp: Date.now() }),
            }).catch(() => {});
        }
    }

    static sendError(error) {
        if (import.meta.env.PROD) {
            fetch("/api/errors", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ ...error, timestamp: Date.now() }),
            }).catch(() => {});
        }
    }
}
```

### Analytics Integration

```javascript
// resources/js/utils/analytics.js
export class Analytics {
    static init() {
        if (import.meta.env.VITE_FEATURE_ANALYTICS === "true") {
            this.initGoogleAnalytics();
            this.trackPageViews();
            this.trackUserInteractions();
        }
    }

    static initGoogleAnalytics() {
        const script = document.createElement("script");
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${import.meta.env.VITE_GA_ID}`;
        document.head.appendChild(script);

        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag("js", new Date());
        gtag("config", import.meta.env.VITE_GA_ID);

        window.gtag = gtag;
    }

    static trackPageViews() {
        // Track SPA navigation
        const observer = new MutationObserver(() => {
            if (window.gtag) {
                gtag("config", import.meta.env.VITE_GA_ID, {
                    page_path: window.location.pathname,
                });
            }
        });

        observer.observe(document.querySelector("#app"), {
            childList: true,
            subtree: true,
        });
    }

    static trackUserInteractions() {
        // Track button clicks
        document.addEventListener("click", (event) => {
            if (event.target.matches("[data-track]")) {
                const action = event.target.dataset.track;
                this.trackEvent("click", action);
            }
        });

        // Track form submissions
        document.addEventListener("submit", (event) => {
            if (event.target.matches("[data-track-form]")) {
                const form = event.target.dataset.trackForm;
                this.trackEvent("form_submit", form);
            }
        });
    }

    static trackEvent(action, label, value = null) {
        if (window.gtag) {
            gtag("event", action, {
                event_category: "engagement",
                event_label: label,
                value: value,
            });
        }
    }
}
```

## CI/CD Pipeline

### GitHub Actions Workflow

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
    push:
        branches: [main]
    pull_request:
        branches: [main]

jobs:
    test:
        runs-on: ubuntu-latest

        steps:
            - uses: actions/checkout@v3

            - name: Setup Node.js
              uses: actions/setup-node@v3
              with:
                  node-version: "20"
                  cache: "npm"

            - name: Install dependencies
              run: npm ci

            - name: Run linting
              run: npm run lint

            - name: Run tests
              run: npm run test

            - name: Build frontend
              run: npm run build
              env:
                  VITE_APP_ENV: production
                  VITE_CDN_URL: ${{ secrets.CDN_URL }}

            - name: Upload build artifacts
              uses: actions/upload-artifact@v3
              with:
                  name: build-files
                  path: public/build/

    deploy:
        needs: test
        runs-on: ubuntu-latest
        if: github.ref == 'refs/heads/main'

        steps:
            - uses: actions/checkout@v3

            - name: Download build artifacts
              uses: actions/download-artifact@v3
              with:
                  name: build-files
                  path: public/build/

            - name: Deploy to server
              uses: appleboy/ssh-action@v0.1.5
              with:
                  host: ${{ secrets.HOST }}
                  username: ${{ secrets.USERNAME }}
                  key: ${{ secrets.SSH_KEY }}
                  script: |
                      cd /var/www/laravel-api-solid
                      git pull origin main
                      docker-compose -f docker-compose.production.yml down
                      docker-compose -f docker-compose.production.yml up -d --build
                      docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force
                      docker-compose -f docker-compose.production.yml exec -T app php artisan config:cache
                      docker-compose -f docker-compose.production.yml exec -T app php artisan route:cache
                      docker-compose -f docker-compose.production.yml exec -T app php artisan view:cache

            - name: Upload assets to CDN
              run: |
                  npm install -g aws-cli
                  aws s3 sync public/build/ s3://${{ secrets.S3_BUCKET }}/build/ --delete
                  aws cloudfront create-invalidation --distribution-id ${{ secrets.CLOUDFRONT_ID }} --paths "/build/*"
              env:
                  AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
                  AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
                  AWS_DEFAULT_REGION: ${{ secrets.AWS_REGION }}
```

### Deployment Script

```bash
#!/bin/bash
# scripts/deploy.sh

set -e

echo "🚀 Starting deployment..."

# Build frontend assets
echo "📦 Building frontend assets..."
npm run build

# Run tests
echo "🧪 Running tests..."
npm run test

# Deploy to server
echo "🌐 Deploying to server..."
docker-compose -f docker-compose.production.yml down
docker-compose -f docker-compose.production.yml up -d --build

# Run Laravel commands
echo "⚙️ Running Laravel commands..."
docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force
docker-compose -f docker-compose.production.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan view:cache

# Upload assets to CDN
if [ "$UPLOAD_TO_CDN" = "true" ]; then
    echo "☁️ Uploading assets to CDN..."
    node scripts/upload-assets.js
fi

# Health check
echo "🏥 Performing health check..."
sleep 10
if curl -f http://localhost/health; then
    echo "✅ Deployment successful!"
else
    echo "❌ Health check failed!"
    exit 1
fi

echo "🎉 Deployment completed successfully!"
```

## Troubleshooting

### Common Issues

#### Build Failures

```bash
# Clear build cache
rm -rf public/build
rm -rf node_modules/.vite
npm run build

# Check for missing dependencies
npm audit
npm install

# Verify environment variables
echo $VITE_APP_URL
echo $VITE_CDN_URL
```

#### Performance Issues

```bash
# Analyze bundle size
npm run build:analyze

# Check for large dependencies
npx webpack-bundle-analyzer public/build/stats.json

# Monitor runtime performance
node scripts/performance-audit.js
```

#### CDN Issues

```bash
# Verify CDN configuration
curl -I https://cdn.your-domain.com/build/assets/app.js

# Check CloudFront cache
aws cloudfront get-distribution --id YOUR_DISTRIBUTION_ID

# Invalidate cache
aws cloudfront create-invalidation --distribution-id YOUR_DISTRIBUTION_ID --paths "/build/*"
```

### Debugging Tools

```javascript
// resources/js/utils/debug.js
export class DebugTools {
    static enableProductionDebugging() {
        if (
            import.meta.env.PROD &&
            window.location.search.includes("debug=true")
        ) {
            // Enable debugging in production
            window.DEBUG = true;
            console.log("🐛 Production debugging enabled");

            // Show performance metrics
            this.showPerformanceMetrics();

            // Enable error details
            this.enableErrorDetails();
        }
    }

    static showPerformanceMetrics() {
        const metrics = performance.getEntriesByType("navigation")[0];
        console.table({
            "DNS Lookup": `${metrics.domainLookupEnd - metrics.domainLookupStart}ms`,
            "TCP Connect": `${metrics.connectEnd - metrics.connectStart}ms`,
            TTFB: `${metrics.responseStart - metrics.requestStart}ms`,
            "DOM Load": `${metrics.domContentLoadedEventEnd - metrics.navigationStart}ms`,
            "Page Load": `${metrics.loadEventEnd - metrics.navigationStart}ms`,
        });
    }

    static enableErrorDetails() {
        window.addEventListener("error", (event) => {
            console.group("🚨 Error Details");
            console.error("Message:", event.message);
            console.error("File:", event.filename);
            console.error("Line:", event.lineno);
            console.error("Column:", event.colno);
            console.error("Stack:", event.error?.stack);
            console.groupEnd();
        });
    }
}
```

## Best Practices

### 1. Asset Optimization

- Use appropriate image formats (WebP, AVIF)
- Implement lazy loading for images
- Minimize and compress CSS/JS files
- Use tree shaking to eliminate dead code

### 2. Caching Strategy

- Set appropriate cache headers
- Use content-based hashing for assets
- Implement service worker for offline support
- Configure CDN caching policies

### 3. Security

- Implement Content Security Policy
- Use HTTPS everywhere
- Validate and sanitize all inputs
- Keep dependencies updated

### 4. Monitoring

- Set up error tracking
- Monitor performance metrics
- Track user interactions
- Implement health checks

### 5. Deployment

- Use automated CI/CD pipelines
- Implement blue-green deployments
- Test in staging environment
- Have rollback procedures ready

---

**Next Steps:**

- Review [advanced configuration guide](./advanced-configuration.md)
- Check [Vue.js setup guide](./vue-setup.md)
- Check [React setup guide](./react-setup.md)
- Explore [switching frameworks guide](./switching-frameworks.md)
