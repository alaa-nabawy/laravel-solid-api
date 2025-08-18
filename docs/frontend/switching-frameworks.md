# Switching Between Vue.js and React

<p align="center">
<img src="https://img.shields.io/badge/Vue.js-3.4+-4FC08D?style=flat&logo=vue.js&logoColor=white" alt="Vue.js">
<img src="https://img.shields.io/badge/⟷-Switch-gray?style=flat" alt="Switch">
<img src="https://img.shields.io/badge/React-18.2+-61DAFB?style=flat&logo=react&logoColor=white" alt="React">
</p>

## Overview

This guide covers how to switch between Vue.js and React frameworks in your Laravel application. Both frameworks are fully supported and can be switched seamlessly using the provided setup scripts.

## Table of Contents

- [Quick Switch](#quick-switch)
- [What Happens During Switch](#what-happens-during-switch)
- [Data Migration](#data-migration)
- [Component Migration](#component-migration)
- [State Management Migration](#state-management-migration)
- [Testing Migration](#testing-migration)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Quick Switch

### From Vue.js to React

```bash
# Ensure development environment is running
make dev

# Switch to React (will remove Vue dependencies)
make setup-react

# Restart development environment
make dev-restart
```

### From React to Vue.js

```bash
# Ensure development environment is running
make dev

# Switch to Vue.js (will remove React dependencies)
make setup-vue

# Restart development environment
make dev-restart
```

### Verification

```bash
# Check which framework is active
cat package.json | grep -E '"(vue|react)"'

# Verify Vite configuration
cat vite.config.js | grep -E '(vue|react)'

# Test the application
curl -I http://localhost:8000
```

## What Happens During Switch

### Automatic Changes

**Package Management:**

- ✅ Removes previous framework dependencies
- ✅ Installs new framework dependencies
- ✅ Updates package.json scripts
- ✅ Configures appropriate dev dependencies

**Configuration Files:**

- ✅ Updates `vite.config.js` with correct plugins
- ✅ Replaces `resources/js/app.js` or `resources/js/app.jsx`
- ✅ Updates Blade layout file
- ✅ Configures path aliases

**Directory Structure:**

- ✅ Creates framework-specific directories
- ✅ Installs sample components
- ✅ Sets up testing environment
- ✅ Configures state management

**Docker Configuration:**

- ✅ Updates `compose.vue.yaml` when switching to Vue
- ✅ Configures Vite dev server settings
- ✅ Sets up proper port forwarding

### Manual Migration Required

**Custom Components:**

- ⚠️ Your custom components need manual migration
- ⚠️ Business logic must be adapted to new framework
- ⚠️ Styling may need adjustments

**State Management:**

- ⚠️ Store/state structure needs conversion
- ⚠️ API calls and data handling
- ⚠️ Component communication patterns

**Tests:**

- ⚠️ Test files need framework-specific updates
- ⚠️ Mocking strategies differ between frameworks
- ⚠️ Testing utilities are framework-specific

## Data Migration

### Backup Strategy

```bash
# Before switching, backup your current implementation
mkdir -p backups/$(date +%Y%m%d_%H%M%S)

# Backup components
cp -r resources/js/Components backups/$(date +%Y%m%d_%H%M%S)/
cp -r resources/js/Pages backups/$(date +%Y%m%d_%H%M%S)/

# Backup store/state management
cp -r resources/js/Store* backups/$(date +%Y%m%d_%H%M%S)/ 2>/dev/null || true
cp -r resources/js/Stores* backups/$(date +%Y%m%d_%H%M%S)/ 2>/dev/null || true

# Backup tests
cp -r tests/Vue backups/$(date +%Y%m%d_%H%M%S)/ 2>/dev/null || true
cp -r tests/React backups/$(date +%Y%m%d_%H%M%S)/ 2>/dev/null || true

# Backup configuration
cp package.json backups/$(date +%Y%m%d_%H%M%S)/
cp vite.config.js backups/$(date +%Y%m%d_%H%M%S)/
```

### Data Structure Mapping

**API Responses:**

```javascript
// Both frameworks handle the same Laravel API responses
// No changes needed in your Laravel controllers

// Example API response (works with both)
{
    "users": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "status": "active"
        }
    ],
    "meta": {
        "total": 100,
        "per_page": 15
    }
}
```

## Component Migration

### Vue.js to React

**Vue Component:**

```vue
<!-- UserCard.vue -->
<template>
    <div class="user-card">
        <h3>{{ user.name }}</h3>
        <p>{{ user.email }}</p>
        <button @click="handleUpdate" :disabled="loading">
            {{ loading ? "Loading..." : "Update" }}
        </button>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useUserStore } from "@stores/userStore";

const props = defineProps({
    user: Object,
});

const emit = defineEmits(["user-updated"]);

const loading = ref(false);
const userStore = useUserStore();

const handleUpdate = async () => {
    loading.value = true;
    try {
        await userStore.updateUser(props.user.id);
        emit("user-updated", props.user);
    } finally {
        loading.value = false;
    }
};
</script>
```

**Equivalent React Component:**

```jsx
// UserCard.jsx
import React, { useState } from "react";
import { useDispatch } from "react-redux";
import { updateUser } from "@store/userSlice";

const UserCard = ({ user, onUserUpdated }) => {
    const [loading, setLoading] = useState(false);
    const dispatch = useDispatch();

    const handleUpdate = async () => {
        setLoading(true);
        try {
            await dispatch(updateUser(user.id)).unwrap();
            onUserUpdated?.(user);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="user-card">
            <h3>{user.name}</h3>
            <p>{user.email}</p>
            <button onClick={handleUpdate} disabled={loading}>
                {loading ? "Loading..." : "Update"}
            </button>
        </div>
    );
};

export default UserCard;
```

### React to Vue.js

**React Component:**

```jsx
// UserForm.jsx
import React, { useState } from "react";
import { useForm } from "@inertiajs/react";

const UserForm = () => {
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
    });

    const submit = (e) => {
        e.preventDefault();
        post("/users");
    };

    return (
        <form onSubmit={submit}>
            <input
                type="text"
                value={data.name}
                onChange={(e) => setData("name", e.target.value)}
                placeholder="Name"
            />
            {errors.name && <span>{errors.name}</span>}

            <input
                type="email"
                value={data.email}
                onChange={(e) => setData("email", e.target.value)}
                placeholder="Email"
            />
            {errors.email && <span>{errors.email}</span>}

            <button type="submit" disabled={processing}>
                {processing ? "Creating..." : "Create User"}
            </button>
        </form>
    );
};

export default UserForm;
```

**Equivalent Vue Component:**

```vue
<!-- UserForm.vue -->
<template>
    <form @submit.prevent="submit">
        <input v-model="form.name" type="text" placeholder="Name" />
        <span v-if="errors.name">{{ errors.name }}</span>

        <input v-model="form.email" type="email" placeholder="Email" />
        <span v-if="errors.email">{{ errors.email }}</span>

        <button type="submit" :disabled="processing">
            {{ processing ? "Creating..." : "Create User" }}
        </button>
    </form>
</template>

<script setup>
import { reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";

const form = reactive({
    name: "",
    email: "",
});

const processing = ref(false);
const errors = ref({});

const submit = () => {
    router.post("/users", form, {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
        onError: (formErrors) => (errors.value = formErrors),
        onSuccess: () => {
            form.name = "";
            form.email = "";
            errors.value = {};
        },
    });
};
</script>
```

## State Management Migration

### Pinia (Vue) to Redux (React)

**Pinia Store:**

```javascript
// stores/userStore.js (Vue)
import { defineStore } from "pinia";
import axios from "axios";

export const useUserStore = defineStore("user", {
    state: () => ({
        users: [],
        loading: false,
        error: null,
    }),

    getters: {
        activeUsers: (state) => state.users.filter((user) => user.active),
    },

    actions: {
        async fetchUsers() {
            this.loading = true;
            try {
                const response = await axios.get("/api/users");
                this.users = response.data;
            } catch (error) {
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },
    },
});
```

**Redux Slice:**

```javascript
// Store/userSlice.js (React)
import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";

export const fetchUsers = createAsyncThunk(
    "users/fetchUsers",
    async (_, { rejectWithValue }) => {
        try {
            const response = await axios.get("/api/users");
            return response.data;
        } catch (error) {
            return rejectWithValue(error.message);
        }
    },
);

const userSlice = createSlice({
    name: "users",
    initialState: {
        users: [],
        loading: false,
        error: null,
    },
    reducers: {},
    extraReducers: (builder) => {
        builder
            .addCase(fetchUsers.pending, (state) => {
                state.loading = true;
            })
            .addCase(fetchUsers.fulfilled, (state, action) => {
                state.loading = false;
                state.users = action.payload;
            })
            .addCase(fetchUsers.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload;
            });
    },
});

export const selectActiveUsers = (state) =>
    state.users.users.filter((user) => user.active);

export default userSlice.reducer;
```

### Redux (React) to Pinia (Vue)

**Migration Steps:**

1. Convert async thunks to Pinia actions
2. Transform reducers to state mutations
3. Convert selectors to getters
4. Update component usage patterns

## Testing Migration

### Vue to React Testing

**Vue Test:**

```javascript
// tests/Vue/Components/UserCard.test.js
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import UserCard from "@/Components/UserCard.vue";

describe("UserCard", () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it("renders user information", () => {
        const user = { id: 1, name: "John Doe" };
        const wrapper = mount(UserCard, {
            props: { user },
        });

        expect(wrapper.text()).toContain("John Doe");
    });
});
```

**React Test:**

```javascript
// tests/React/Components/UserCard.test.jsx
import React from "react";
import { render, screen } from "@testing-library/react";
import { Provider } from "react-redux";
import { configureStore } from "@reduxjs/toolkit";
import UserCard from "@/Components/UserCard";
import userReducer from "@/Store/userSlice";

const createMockStore = () => {
    return configureStore({
        reducer: { users: userReducer },
    });
};

describe("UserCard", () => {
    it("renders user information", () => {
        const user = { id: 1, name: "John Doe" };
        const store = createMockStore();

        render(
            <Provider store={store}>
                <UserCard user={user} />
            </Provider>,
        );

        expect(screen.getByText("John Doe")).toBeInTheDocument();
    });
});
```

## Best Practices

### Before Switching

1. **Document Current Implementation**

    ```bash
    # Create documentation of current components
    find resources/js -name "*.vue" -o -name "*.jsx" | xargs wc -l > component_inventory.txt

    # Document API endpoints used
    grep -r "axios\|fetch" resources/js/ > api_calls.txt
    ```

2. **Test Current Functionality**

    ```bash
    # Run all tests before switching
    npm run test

    # Test critical user flows
    npm run test:e2e  # if available
    ```

3. **Backup Database State**
    ```bash
    # If you have seeded data
    php artisan db:seed --class=TestDataSeeder
    ```

### During Migration

1. **Incremental Migration**
    - Start with simple components
    - Migrate complex components last
    - Test each component individually

2. **Maintain API Compatibility**
    - Keep Laravel controllers unchanged
    - Ensure API responses remain consistent
    - Test API endpoints independently

3. **Preserve Business Logic**
    - Extract business logic to separate files
    - Use framework-agnostic utility functions
    - Maintain consistent data validation

### After Switching

1. **Comprehensive Testing**

    ```bash
    # Test new implementation
    npm run test
    npm run lint
    npm run type-check  # if using TypeScript
    ```

2. **Performance Verification**

    ```bash
    # Build and check bundle size
    npm run build

    # Test development server
    npm run dev
    ```

3. **Documentation Updates**
    - Update component documentation
    - Revise development workflows
    - Update team guidelines

## Troubleshooting

### Common Issues

**1. Port Conflicts**

```bash
# Check what's running on port 5173
lsof -i :5173

# Kill conflicting processes
kill -9 $(lsof -t -i:5173)

# Restart development environment
make dev-restart
```

**2. Dependency Conflicts**

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

**3. Vite Configuration Issues**

```bash
# Verify Vite config syntax
npx vite --config vite.config.js --mode development --dry-run

# Check for plugin conflicts
npm ls @vitejs/plugin-vue @vitejs/plugin-react
```

**4. Import Path Issues**

```bash
# Check path aliases in vite.config.js
# Verify file extensions (.vue vs .jsx)
# Update import statements
```

**5. State Management Issues**

```bash
# Verify store configuration
# Check action/mutation syntax
# Test store independently
```

### Recovery Procedures

**If Switch Fails:**

```bash
# Restore from backup
cp -r backups/YYYYMMDD_HHMMSS/* .

# Reinstall dependencies
npm install

# Restart development environment
make dev-restart
```

**If Application Won't Start:**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Vite dev server logs
npm run dev -- --debug

# Verify Docker containers
make status
docker-compose logs workspace
```

### Performance Comparison

**Bundle Size Analysis:**

```bash
# After switching, compare bundle sizes
npm run build
ls -la public/build/assets/

# Use bundle analyzer if available
npm install --save-dev webpack-bundle-analyzer
npx webpack-bundle-analyzer public/build/assets/
```

**Development Server Performance:**

```bash
# Measure startup time
time npm run dev

# Monitor memory usage
top -p $(pgrep -f "vite")
```

---

**Next Steps:**

- Review [Vue.js setup guide](./vue-setup.md)
- Review [React setup guide](./react-setup.md)
- Check [advanced configurations](./advanced-configuration.md)
- Explore [deployment strategies](./deployment.md)
