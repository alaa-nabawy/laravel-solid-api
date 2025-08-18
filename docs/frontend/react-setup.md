# React Setup Guide

<p align="center">
<img src="https://img.shields.io/badge/React-18.2+-61DAFB?style=flat&logo=react&logoColor=white" alt="React Version">
<img src="https://img.shields.io/badge/Inertia.js-1.0+-9553E9?style=flat&logo=inertia&logoColor=white" alt="Inertia.js Version">
<img src="https://img.shields.io/badge/TypeScript-5.0+-3178C6?style=flat&logo=typescript&logoColor=white" alt="TypeScript Version">
</p>

## Overview

This guide covers the complete setup and usage of React 18 with Inertia.js in your Laravel application. The setup provides a modern, component-based frontend with server-side routing and seamless Laravel integration.

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
# Run the React setup script
make setup-react
```

**What happens during setup:**

1. ⚠️ Removes any existing Vue dependencies and configurations
2. 📦 Installs React 18 ecosystem packages
3. 🔧 Configures Vite for React development
4. 📁 Creates component directory structure
5. 🎨 Installs sample components and layouts
6. 🧪 Sets up testing environment with Jest and React Testing Library
7. 🚀 Configures development server with Fast Refresh

### Verification

```bash
# Start development environment
make dev

# Visit your application
# Laravel: http://localhost:8000
# Vite Dev Server: http://localhost:5173
```

You should see the React welcome page with interactive components and state management examples.

## What Gets Installed

### Core Dependencies

```json
{
    "dependencies": {
        "react": "^18.2.0",
        "react-dom": "^18.2.0",
        "@inertiajs/react": "^1.0.0",
        "react-router-dom": "^6.8.0",
        "@reduxjs/toolkit": "^2.0.0",
        "react-redux": "^9.0.0",
        "@headlessui/react": "^1.7.0",
        "@heroicons/react": "^2.0.0",
        "axios": "^1.8.2",
        "clsx": "^2.0.0"
    }
}
```

### Development Dependencies

```json
{
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "@vitejs/plugin-react": "^6.0.0",
        "@types/react": "^18.2.0",
        "@types/react-dom": "^18.2.0",
        "@testing-library/react": "^13.4.0",
        "@testing-library/jest-dom": "^6.1.0",
        "@testing-library/user-event": "^14.5.0",
        "eslint": "^8.57.0",
        "eslint-plugin-react": "^7.33.0",
        "eslint-plugin-react-hooks": "^4.6.0",
        "eslint-plugin-react-refresh": "^0.4.0",
        "happy-dom": "^12.10.0",
        "jsdom": "^23.0.0",
        "vitest": "^1.1.0",
        "@vitest/ui": "^1.1.0",
        "typescript": "^5.0.0"
    }
}
```

### Package Scripts

```json
{
    "scripts": {
        "lint": "eslint resources/js --ext .js,.jsx,.ts,.tsx --fix",
        "lint:check": "eslint resources/js --ext .js,.jsx,.ts,.tsx",
        "build": "vite build",
        "dev": "vite",
        "test": "vitest",
        "test:ui": "vitest --ui",
        "test:coverage": "vitest --coverage",
        "type-check": "tsc --noEmit"
    }
}
```

## Project Structure

### Directory Layout

```
resources/js/
├── Components/              # Reusable React components
│   ├── NavLink.jsx         # Navigation link component
│   ├── Dropdown.jsx        # Dropdown menu component
│   ├── DropdownLink.jsx    # Dropdown item component
│   └── ...                 # Your custom components
├── Layouts/                # Page layouts
│   └── AppLayout.jsx       # Main application layout
├── Pages/                  # Inertia.js pages
│   └── Welcome.jsx         # Demo welcome page
├── Store/                  # Redux store
│   ├── index.js           # Store configuration
│   ├── counterSlice.js    # Example counter slice
│   └── ...                # Your slices
├── Hooks/                  # Custom React hooks
│   └── ...                # Your custom hooks
├── Utils/                  # Utility functions
│   └── ...                # Helper functions
├── Types/                  # TypeScript definitions
│   └── ...                # Type definitions
├── app.jsx                 # Application entry point
└── bootstrap.js            # Laravel Echo, Axios setup

tests/React/                # React-specific tests
├── Welcome.test.jsx        # Component test example
└── setup.js               # Test environment setup

resources/views/layouts/
└── app.blade.php          # Main Blade layout for Inertia
```

### File Naming Conventions

- **Components**: PascalCase (e.g., `UserProfile.jsx`)
- **Pages**: PascalCase (e.g., `UserDashboard.jsx`)
- **Hooks**: camelCase with `use` prefix (e.g., `useUserData.js`)
- **Store Slices**: camelCase (e.g., `userSlice.js`)
- **TypeScript**: Use `.tsx` for components, `.ts` for utilities

## Configuration Files

### Vite Configuration (`vite.config.js`)

```javascript
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import { resolve } from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.jsx"],
            refresh: true,
        }),
        react({
            fastRefresh: true,
            babel: {
                plugins: [
                    [
                        "@babel/plugin-transform-react-jsx",
                        { runtime: "automatic" },
                    ],
                ],
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
            "@store": resolve(__dirname, "resources/js/Store"),
            "@hooks": resolve(__dirname, "resources/js/Hooks"),
            "@utils": resolve(__dirname, "resources/js/Utils"),
        },
    },
    define: {
        __DEV__: JSON.stringify(process.env.NODE_ENV === "development"),
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
        setupFiles: ["tests/React/setup.js"],
    },
});
```

### Application Entry Point (`resources/js/app.jsx`)

```jsx
import "./bootstrap";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { Provider } from "react-redux";
import { store } from "@store";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob("./Pages/**/*.jsx"),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <Provider store={store}>
                <App {...props} />
            </Provider>,
        );
    },
    progress: {
        color: "#4F46E5",
        showSpinner: true,
    },
});
```

### Redux Store Configuration (`resources/js/Store/index.js`)

```javascript
import { configureStore } from "@reduxjs/toolkit";
import counterReducer from "./counterSlice";

export const store = configureStore({
    reducer: {
        counter: counterReducer,
    },
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware({
            serializableCheck: {
                ignoredActions: ["persist/PERSIST"],
            },
        }),
    devTools: process.env.NODE_ENV !== "production",
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
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
        @routes @viteReactRefresh @vite(['resources/css/app.css',
        'resources/js/app.jsx']) @inertiaHead
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
npm run dev        # Start Vite dev server with Fast Refresh
npm run build      # Build for production
npm run test       # Run tests
npm run test:ui    # Run tests with UI interface
npm run lint       # Lint and fix React files
npm run type-check # TypeScript type checking
```

### Fast Refresh

React Fast Refresh provides instant feedback during development:

- **Component Updates**: Preserves component state
- **Hook Updates**: Maintains hook state when possible
- **Error Recovery**: Automatic recovery from syntax errors
- **Hot Reloading**: Instant updates without full page refresh

### Path Aliases

Use configured aliases for clean imports:

```jsx
// Instead of relative paths
import UserCard from "../../../Components/UserCard.jsx";

// Use aliases
import UserCard from "@components/UserCard.jsx";
import { useAppSelector } from "@store/hooks";
import useAuth from "@hooks/useAuth";
```

## Component Development

### Functional Component with Hooks

```jsx
import React, { useState, useEffect, useCallback } from "react";
import { useSelector, useDispatch } from "react-redux";
import { updateUser } from "@store/userSlice";
import PropTypes from "prop-types";

const UserCard = ({ user, onUserUpdated }) => {
    const [loading, setLoading] = useState(false);
    const dispatch = useDispatch();
    const currentUser = useSelector((state) => state.user.currentUser);

    const handleClick = useCallback(async () => {
        setLoading(true);
        try {
            await dispatch(updateUser(user.id)).unwrap();
            onUserUpdated?.(user);
        } catch (error) {
            console.error("Failed to update user:", error);
        } finally {
            setLoading(false);
        }
    }, [dispatch, user.id, user, onUserUpdated]);

    const isActive = user.status === "active";

    return (
        <div className="user-card bg-white rounded-lg shadow-md p-6">
            <h3 className="text-lg font-semibold">{user.name}</h3>
            <p className="text-gray-600">{user.email}</p>
            <div className="mt-4">
                <span
                    className={`px-2 py-1 rounded text-sm ${
                        isActive
                            ? "bg-green-100 text-green-800"
                            : "bg-red-100 text-red-800"
                    }`}
                >
                    {user.status}
                </span>
            </div>
            <button
                onClick={handleClick}
                disabled={loading}
                className="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
            >
                {loading ? "Loading..." : "Update User"}
            </button>
        </div>
    );
};

UserCard.propTypes = {
    user: PropTypes.shape({
        id: PropTypes.number.isRequired,
        name: PropTypes.string.isRequired,
        email: PropTypes.string.isRequired,
        status: PropTypes.oneOf(["active", "inactive"]).isRequired,
    }).isRequired,
    onUserUpdated: PropTypes.func,
};

export default UserCard;
```

### Inertia.js Page Component

```jsx
import React from "react";
import { Head } from "@inertiajs/react";
import AppLayout from "@layouts/AppLayout";
import UserCard from "@components/UserCard";

const Dashboard = ({ user, stats }) => {
    const handleUserUpdated = (updatedUser) => {
        console.log("User updated:", updatedUser);
    };

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h1 className="text-2xl font-bold text-gray-900 mb-6">
                                Welcome, {user.name}!
                            </h1>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                {stats.map((stat, index) => (
                                    <div
                                        key={index}
                                        className="bg-gray-50 p-4 rounded-lg"
                                    >
                                        <h3 className="text-lg font-medium">
                                            {stat.label}
                                        </h3>
                                        <p className="text-3xl font-bold text-blue-600">
                                            {stat.value}
                                        </p>
                                    </div>
                                ))}
                            </div>

                            <UserCard
                                user={user}
                                onUserUpdated={handleUserUpdated}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Dashboard;
```

### Using Inertia.js Features

```jsx
import React, { useState } from "react";
import { router } from "@inertiajs/react";
import { useForm } from "@inertiajs/react";

const UserForm = () => {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
        email: "",
    });

    // Navigate to another page
    const goToProfile = () => {
        router.visit("/profile");
    };

    // Submit form with Inertia
    const submit = (e) => {
        e.preventDefault();

        post("/users", {
            onSuccess: () => {
                reset();
                // Handle success
            },
            onError: (errors) => {
                console.error("Form errors:", errors);
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Name
                </label>
                <input
                    type="text"
                    value={data.name}
                    onChange={(e) => setData("name", e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300"
                />
                {errors.name && (
                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                )}
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input
                    type="email"
                    value={data.email}
                    onChange={(e) => setData("email", e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300"
                />
                {errors.email && (
                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                )}
            </div>

            <button
                type="submit"
                disabled={processing}
                className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
            >
                {processing ? "Creating..." : "Create User"}
            </button>
        </form>
    );
};

export default UserForm;
```

## State Management

### Redux Slice Example

```javascript
// Store/userSlice.js
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";

// Async thunks
export const fetchUsers = createAsyncThunk(
    "users/fetchUsers",
    async (_, { rejectWithValue }) => {
        try {
            const response = await axios.get("/api/users");
            return response.data;
        } catch (error) {
            return rejectWithValue(error.response.data);
        }
    },
);

export const updateUser = createAsyncThunk(
    "users/updateUser",
    async ({ id, data }, { rejectWithValue }) => {
        try {
            const response = await axios.put(`/api/users/${id}`, data);
            return response.data;
        } catch (error) {
            return rejectWithValue(error.response.data);
        }
    },
);

const userSlice = createSlice({
    name: "users",
    initialState: {
        users: [],
        currentUser: null,
        loading: false,
        error: null,
    },
    reducers: {
        clearError: (state) => {
            state.error = null;
        },
        setCurrentUser: (state, action) => {
            state.currentUser = action.payload;
        },
    },
    extraReducers: (builder) => {
        builder
            // Fetch users
            .addCase(fetchUsers.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchUsers.fulfilled, (state, action) => {
                state.loading = false;
                state.users = action.payload;
            })
            .addCase(fetchUsers.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            })
            // Update user
            .addCase(updateUser.fulfilled, (state, action) => {
                const index = state.users.findIndex(
                    (user) => user.id === action.payload.id,
                );
                if (index !== -1) {
                    state.users[index] = action.payload;
                }
            });
    },
});

export const { clearError, setCurrentUser } = userSlice.actions;
export default userSlice.reducer;

// Selectors
export const selectUsers = (state) => state.users.users;
export const selectActiveUsers = (state) =>
    state.users.users.filter((user) => user.status === "active");
export const selectUsersLoading = (state) => state.users.loading;
export const selectUsersError = (state) => state.users.error;
```

### Custom Hooks for Redux

```javascript
// Store/hooks.js
import { useDispatch, useSelector } from 'react-redux';
import type { RootState, AppDispatch } from './index';

// Use throughout your app instead of plain `useDispatch` and `useSelector`
export const useAppDispatch = () => useDispatch<AppDispatch>();
export const useAppSelector = <TSelected>(selector: (state: RootState) => TSelected) =>
    useSelector(selector);
```

### Custom React Hooks

```jsx
// Hooks/useAuth.js
import { useState, useEffect } from "react";
import { router } from "@inertiajs/react";
import { useAppSelector, useAppDispatch } from "@store/hooks";
import { setCurrentUser } from "@store/userSlice";

export const useAuth = () => {
    const [loading, setLoading] = useState(false);
    const currentUser = useAppSelector((state) => state.users.currentUser);
    const dispatch = useAppDispatch();

    const isAuthenticated = !!currentUser;
    const isAdmin = currentUser?.role === "admin";

    const login = async (credentials) => {
        setLoading(true);
        try {
            await router.post("/login", credentials);
        } finally {
            setLoading(false);
        }
    };

    const logout = () => {
        router.post("/logout");
        dispatch(setCurrentUser(null));
    };

    return {
        currentUser,
        loading,
        isAuthenticated,
        isAdmin,
        login,
        logout,
    };
};

// Hooks/useApi.js
import { useState, useCallback } from "react";
import axios from "axios";

export const useApi = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const request = useCallback(async (config) => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios(config);
            return response.data;
        } catch (err) {
            setError(err.response?.data || err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    return { request, loading, error };
};
```

## Testing

### Test Setup (`tests/React/setup.js`)

```javascript
import { vi } from "vitest";
import "@testing-library/jest-dom";

// Mock Inertia.js
vi.mock("@inertiajs/react", () => ({
    Head: ({ children, title }) => (
        <div data-testid="head" title={title}>
            {children}
        </div>
    ),
    Link: ({ children, href, ...props }) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
    router: {
        visit: vi.fn(),
        post: vi.fn(),
        get: vi.fn(),
    },
    useForm: () => ({
        data: {},
        setData: vi.fn(),
        post: vi.fn(),
        processing: false,
        errors: {},
        reset: vi.fn(),
    }),
}));

// Mock Redux store
vi.mock("react-redux", () => ({
    useSelector: vi.fn(),
    useDispatch: () => vi.fn(),
    Provider: ({ children }) => children,
}));

// Global test utilities
global.ResizeObserver = vi.fn(() => ({
    observe: vi.fn(),
    unobserve: vi.fn(),
    disconnect: vi.fn(),
}));
```

### Component Testing

```jsx
// tests/React/Components/UserCard.test.jsx
import React from "react";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import UserCard from "@/Components/UserCard";
import userReducer from "@/Store/userSlice";

const createMockStore = (initialState = {}) => {
    return configureStore({
        reducer: {
            users: userReducer,
        },
        preloadedState: {
            users: {
                users: [],
                currentUser: null,
                loading: false,
                error: null,
                ...initialState,
            },
        },
    });
};

const renderWithStore = (component, store) => {
    return render(<Provider store={store}>{component}</Provider>);
};

describe("UserCard", () => {
    const mockUser = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
        status: "active",
    };

    let store;

    beforeEach(() => {
        store = createMockStore();
    });

    it("renders user information correctly", () => {
        renderWithStore(<UserCard user={mockUser} />, store);

        expect(screen.getByText("John Doe")).toBeInTheDocument();
        expect(screen.getByText("john@example.com")).toBeInTheDocument();
        expect(screen.getByText("active")).toBeInTheDocument();
    });

    it("calls onUserUpdated when update button is clicked", async () => {
        const onUserUpdated = vi.fn();

        renderWithStore(
            <UserCard user={mockUser} onUserUpdated={onUserUpdated} />,
            store,
        );

        const updateButton = screen.getByText("Update User");
        fireEvent.click(updateButton);

        await waitFor(() => {
            expect(onUserUpdated).toHaveBeenCalledWith(mockUser);
        });
    });

    it("shows loading state when updating", async () => {
        renderWithStore(<UserCard user={mockUser} />, store);

        const updateButton = screen.getByText("Update User");
        fireEvent.click(updateButton);

        expect(screen.getByText("Loading...")).toBeInTheDocument();
    });
});
```

### Redux Testing

```javascript
// tests/React/Store/userSlice.test.js
import { describe, it, expect, vi, beforeEach } from "vitest";
import { configureStore } from "@reduxjs/toolkit";
import axios from "axios";
import userReducer, {
    fetchUsers,
    updateUser,
    clearError,
} from "@/Store/userSlice";

vi.mock("axios");

describe("userSlice", () => {
    let store;

    beforeEach(() => {
        store = configureStore({
            reducer: {
                users: userReducer,
            },
        });
        vi.clearAllMocks();
    });

    describe("reducers", () => {
        it("should clear error", () => {
            const initialState = {
                users: [],
                currentUser: null,
                loading: false,
                error: "Some error",
            };

            const action = clearError();
            const newState = userReducer(initialState, action);

            expect(newState.error).toBe(null);
        });
    });

    describe("async thunks", () => {
        it("should fetch users successfully", async () => {
            const mockUsers = [
                { id: 1, name: "John" },
                { id: 2, name: "Jane" },
            ];
            axios.get.mockResolvedValue({ data: mockUsers });

            await store.dispatch(fetchUsers());

            const state = store.getState().users;
            expect(state.users).toEqual(mockUsers);
            expect(state.loading).toBe(false);
            expect(state.error).toBe(null);
        });

        it("should handle fetch users error", async () => {
            const errorMessage = "Network Error";
            axios.get.mockRejectedValue({
                response: { data: errorMessage },
            });

            await store.dispatch(fetchUsers());

            const state = store.getState().users;
            expect(state.users).toEqual([]);
            expect(state.loading).toBe(false);
            expect(state.error).toBe(errorMessage);
        });
    });
});
```

### Hook Testing

```jsx
// tests/React/Hooks/useAuth.test.js
import { renderHook, act } from "@testing-library/react";
import { describe, it, expect, vi } from "vitest";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import { useAuth } from "@/Hooks/useAuth";
import userReducer from "@/Store/userSlice";

const createWrapper = (initialState = {}) => {
    const store = configureStore({
        reducer: { users: userReducer },
        preloadedState: { users: initialState },
    });

    return ({ children }) => <Provider store={store}>{children}</Provider>;
};

describe("useAuth", () => {
    it("should return authentication state", () => {
        const wrapper = createWrapper({
            currentUser: { id: 1, name: "John", role: "user" },
            users: [],
            loading: false,
            error: null,
        });

        const { result } = renderHook(() => useAuth(), { wrapper });

        expect(result.current.isAuthenticated).toBe(true);
        expect(result.current.isAdmin).toBe(false);
        expect(result.current.currentUser.name).toBe("John");
    });

    it("should identify admin users", () => {
        const wrapper = createWrapper({
            currentUser: { id: 1, name: "Admin", role: "admin" },
            users: [],
            loading: false,
            error: null,
        });

        const { result } = renderHook(() => useAuth(), { wrapper });

        expect(result.current.isAdmin).toBe(true);
    });
});
```

## Advanced Usage

### Higher-Order Components (HOCs)

```jsx
// Utils/withAuth.jsx
import React from "react";
import { useAuth } from "@hooks/useAuth";
import { router } from "@inertiajs/react";

const withAuth = (WrappedComponent, options = {}) => {
    const { requireAdmin = false } = options;

    return function AuthenticatedComponent(props) {
        const { isAuthenticated, isAdmin, loading } = useAuth();

        if (loading) {
            return <div>Loading...</div>;
        }

        if (!isAuthenticated) {
            router.visit("/login");
            return null;
        }

        if (requireAdmin && !isAdmin) {
            return <div>Access denied. Admin privileges required.</div>;
        }

        return <WrappedComponent {...props} />;
    };
};

export default withAuth;

// Usage
const AdminPanel = withAuth(AdminPanelComponent, { requireAdmin: true });
```

### Context API Integration

```jsx
// Contexts/ThemeContext.jsx
import React, { createContext, useContext, useState } from "react";

const ThemeContext = createContext();

export const useTheme = () => {
    const context = useContext(ThemeContext);
    if (!context) {
        throw new Error("useTheme must be used within a ThemeProvider");
    }
    return context;
};

export const ThemeProvider = ({ children }) => {
    const [theme, setTheme] = useState("light");

    const toggleTheme = () => {
        setTheme((prev) => (prev === "light" ? "dark" : "light"));
    };

    return (
        <ThemeContext.Provider value={{ theme, toggleTheme }}>
            {children}
        </ThemeContext.Provider>
    );
};
```

### TypeScript Integration

```typescript
// Types/User.ts
export interface User {
    id: number;
    name: string;
    email: string;
    status: "active" | "inactive";
    role: "user" | "admin";
    created_at: string;
    updated_at: string;
}

export interface UserState {
    users: User[];
    currentUser: User | null;
    loading: boolean;
    error: string | null;
}

// Component with TypeScript
import React from "react";
import type { User } from "@/Types/User";

interface UserCardProps {
    user: User;
    onUserUpdated?: (user: User) => void;
}

const UserCard: React.FC<UserCardProps> = ({ user, onUserUpdated }) => {
    // Component implementation
};

export default UserCard;
```

### Error Boundaries

```jsx
// Components/ErrorBoundary.jsx
import React from "react";

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        console.error("Error caught by boundary:", error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="error-boundary p-6 bg-red-50 border border-red-200 rounded-lg">
                    <h2 className="text-lg font-semibold text-red-800 mb-2">
                        Something went wrong
                    </h2>
                    <p className="text-red-600">
                        {this.state.error?.message ||
                            "An unexpected error occurred"}
                    </p>
                    <button
                        onClick={() =>
                            this.setState({ hasError: false, error: null })
                        }
                        className="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Try again
                    </button>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
```

## Troubleshooting

### Common Issues

**1. Fast Refresh Not Working**

```bash
# Ensure component names start with uppercase
# Check for syntax errors
# Restart Vite dev server
npm run dev
```

**2. Redux DevTools Not Showing**

```bash
# Install Redux DevTools browser extension
# Ensure devTools is enabled in store configuration
# Check browser console for errors
```

**3. TypeScript Errors**

```bash
# Run type checking
npm run type-check

# Check tsconfig.json configuration
# Ensure proper type imports
```

**4. Component Not Updating**

```bash
# Check if state is being mutated directly
# Ensure proper dependency arrays in useEffect
# Verify Redux actions are dispatched correctly
```

**5. Build Errors**

```bash
# Check for unused imports
npm run lint

# Clear build cache
rm -rf node_modules/.vite
npm run build
```

### Performance Tips

1. **Use React.memo for expensive components**
2. **Implement lazy loading with React.lazy**
3. **Use useMemo and useCallback appropriately**
4. **Optimize Redux selectors with reselect**
5. **Implement code splitting at route level**

### Debugging

```jsx
// Enable React DevTools
// Use Redux DevTools for state inspection

// Debug component renders
const MyComponent = () => {
    console.log("Component rendered");

    useEffect(() => {
        console.log("Effect ran");
    }, []);

    return <div>Content</div>;
};

// Debug Redux actions
const dispatch = useAppDispatch();
const handleClick = () => {
    console.log("Dispatching action");
    dispatch(someAction());
};
```

---

**Next Steps:**

- Explore [Vue.js setup](./vue-setup.md) for comparison
- Learn about [switching frameworks](./switching-frameworks.md)
- Check out [advanced configurations](./advanced-configuration.md)
