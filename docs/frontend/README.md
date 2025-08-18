# Frontend Development Guide

<p align="center">
<img src="https://img.shields.io/badge/Vue.js-3.4+-4FC08D?style=flat&logo=vue.js&logoColor=white" alt="Vue.js Version">
<img src="https://img.shields.io/badge/React-19.0+-61DAFB?style=flat&logo=react&logoColor=white" alt="React Version">
<img src="https://img.shields.io/badge/Inertia.js-2.0+-9553E9?style=flat&logo=inertia&logoColor=white" alt="Inertia.js Version">
<img src="https://img.shields.io/badge/Vite-7.0+-646CFF?style=flat&logo=vite&logoColor=white" alt="Vite Version">
<img src="https://img.shields.io/badge/TypeScript-5.3+-3178C6?style=flat&logo=typescript&logoColor=white" alt="TypeScript Version">
</p>

## Overview

This Laravel API boilerplate provides seamless frontend integration with both **Vue.js** and **React** through **Inertia.js**. The setup includes modern tooling, testing frameworks, and production-ready configurations for building sophisticated single-page applications.

## Table of Contents

- [Quick Start](#quick-start)
- [Framework Options](#framework-options)
- [Architecture Overview](#architecture-overview)
- [Setup Guides](#setup-guides)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Production Deployment](#production-deployment)
- [Troubleshooting](#troubleshooting)

## Quick Start

### Prerequisites

- Docker and Docker Compose installed
- Laravel development environment running (`make dev`)
- Node.js 18+ (handled automatically in Docker)

### Choose Your Framework

```bash
# For Vue.js development
make setup-vue

# For React development
make setup-react
```

> **⚠️ Important**: Each setup command will remove the other framework's dependencies and configurations. You can switch between frameworks at any time, but only one can be active per project.

### Start Development

```bash
# Start the development environment with frontend
make dev

# Access the application
# Laravel: http://localhost:8000
# Vite Dev Server: http://localhost:5173
```

## Framework Options

### Vue.js Stack

**Core Technologies:**

- Vue 3.4+ with Composition API
- Inertia.js for seamless Laravel integration
- Pinia for state management
- Vue Router for client-side routing

**UI & Styling:**

- Tailwind CSS 4.0+ with Vite plugin
- Headless UI Vue components
- Heroicons for SVG icons

**Development Tools:**

- Vite 7.0+ for fast builds and HMR
- Vitest for unit testing
- ESLint with Vue plugin
- Vue TypeScript support

### React Stack

**Core Technologies:**

- React 19.0+ with modern hooks
- Inertia.js for seamless Laravel integration
- Zustand for state management (example included)
- React Router integration

**UI & Styling:**

- Tailwind CSS 4.0+ with Vite plugin
- Headless UI React components
- Heroicons for SVG icons
- Clsx and Tailwind Merge utilities

**Development Tools:**

- Vite 7.0+ with React plugin
- Vitest + React Testing Library
- ESLint with React plugins
- Full TypeScript support

## Architecture Overview

### Project Structure

```
resources/js/
├── Components/          # Reusable UI components
│   ├── NavLink.vue/jsx  # Navigation components
│   ├── Dropdown.vue/jsx # Dropdown menus
│   └── ...              # Custom components
├── Layouts/             # Page layouts
│   └── AppLayout.vue/jsx # Main application layout
├── Pages/               # Inertia.js pages
│   └── Welcome.vue/jsx  # Landing page example
├── Stores/              # State management
│   └── counter.js       # Example store
├── Composables/         # Vue composables (Vue only)
├── Hooks/               # React hooks (React only)
├── lib/                 # Utility functions (React only)
├── Types/               # TypeScript definitions
├── app.js/jsx           # Application entry point
└── bootstrap.js         # Laravel Echo, Axios setup

tests/
├── Vue/                 # Vue component tests
│   ├── Welcome.test.js  # Example test
│   └── setup.js         # Test configuration
└── React/               # React component tests
    ├── Welcome.test.jsx # Example test
    └── setup.js         # Test configuration
```

### Inertia.js Integration

Inertia.js provides the bridge between Laravel and your frontend framework:

- **Server-side routing**: Laravel handles all routing
- **Client-side navigation**: No page refreshes
- **Shared data**: Pass data from controllers to components
- **Form handling**: Built-in form helpers and validation
- **Progress indicators**: Loading states for navigation

### Docker Integration

The frontend development environment includes:

- **Vite Dev Server**: Hot module replacement on port 5173
- **Automatic Detection**: Docker Compose automatically detects Vue.js
- **Volume Mounting**: Real-time file synchronization
- **Network Integration**: Seamless communication with Laravel

## Setup Guides

- **[Vue.js Setup Guide](./vue-setup.md)** - Complete Vue 3 + Inertia.js setup
- **[React Setup Guide](./react-setup.md)** - Complete React + Inertia.js setup
- **[Switching Frameworks](./switching-frameworks.md)** - How to switch between Vue and React
- **[Docker Configuration](./docker-setup.md)** - Frontend Docker environment details

## Development Workflow

### Daily Development

```bash
# Start development environment
make dev

# Access workspace for frontend commands
make shell

# Inside workspace:
npm run dev        # Start Vite dev server
npm run build      # Build for production
npm run test       # Run tests
npm run test:ui    # Run tests with UI
npm run lint       # Lint code
npm run type-check # TypeScript checking
```

### Hot Module Replacement

Vite provides instant feedback during development:

- **Vue**: Component state preservation
- **React**: Fast refresh with state preservation
- **CSS**: Instant style updates
- **Assets**: Automatic reloading

### State Management

**Vue.js with Pinia:**

```javascript
// stores/counter.js
import { defineStore } from "pinia";

export const useCounterStore = defineStore("counter", {
    state: () => ({ count: 0 }),
    actions: {
        increment() {
            this.count++;
        },
    },
});
```

**React with Zustand:**

```javascript
// stores/counter.js
import { create } from "zustand";

export const useCounterStore = create((set) => ({
    count: 0,
    increment: () => set((state) => ({ count: state.count + 1 })),
}));
```

## Testing

### Test Framework: Vitest

Both Vue and React setups use Vitest for fast, modern testing:

```bash
# Run tests
npm run test

# Run tests with UI
npm run test:ui

# Run tests with coverage
npm run test:coverage

# Watch mode
npm run test -- --watch
```

### Testing Examples

**Vue Component Test:**

```javascript
import { mount } from "@vue/test-utils";
import { describe, it, expect } from "vitest";
import Welcome from "@/Pages/Welcome.vue";

describe("Welcome", () => {
    it("renders properly", () => {
        const wrapper = mount(Welcome);
        expect(wrapper.text()).toContain("Laravel API Boilerplate");
    });
});
```

**React Component Test:**

```javascript
import { render, screen } from "@testing-library/react";
import { describe, it, expect } from "vitest";
import Welcome from "@/Pages/Welcome";

describe("Welcome", () => {
    it("renders properly", () => {
        render(<Welcome />);
        expect(screen.getByText("Laravel API Boilerplate")).toBeInTheDocument();
    });
});
```

## Production Deployment

### Build Process

```bash
# Build for production
npm run build

# The build outputs to public/build/
# Laravel Mix manifest handles asset versioning
```

### Production Optimization

- **Code Splitting**: Automatic route-based splitting
- **Tree Shaking**: Dead code elimination
- **Asset Optimization**: Image and CSS optimization
- **Caching**: Long-term caching with versioned assets

### Environment Variables

```bash
# .env configuration
VITE_APP_NAME="Your App Name"
VITE_APP_URL="https://your-domain.com"

# Access in frontend:
const appName = import.meta.env.VITE_APP_NAME
```

## Troubleshooting

### Common Issues

**1. React Refresh Errors After Vue Setup**

```bash
# Clear browser cache and restart dev server
make dev-restart
```

**2. Node Modules Issues**

```bash
# Clear and reinstall dependencies
make shell
rm -rf node_modules package-lock.json
npm install
```

**3. Vite Dev Server Not Starting**

```bash
# Check if port 5173 is available
make logs
# Look for Vite container logs
```

**4. TypeScript Errors**

```bash
# Run type checking
npm run type-check

# Generate types for Laravel routes (React)
npm run ziggy:generate
```

### Getting Help

- **[Vue.js Documentation](https://vuejs.org/)**
- **[React Documentation](https://react.dev/)**
- **[Inertia.js Documentation](https://inertiajs.com/)**
- **[Vite Documentation](https://vitejs.dev/)**
- **[Vitest Documentation](https://vitest.dev/)**

---

**Next Steps:**

- Choose your framework: [Vue.js](./vue-setup.md) or [React](./react-setup.md)
- Learn about [switching between frameworks](./switching-frameworks.md)
- Explore [advanced configurations](./advanced-configuration.md)
