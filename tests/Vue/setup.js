import { config } from "@vue/test-utils";
import { vi } from "vitest";

// Mock Inertia.js
vi.mock("@inertiajs/vue3", () => ({
    createInertiaApp: vi.fn(),
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        reload: vi.fn(),
    },
    usePage: () => ({
        props: {
            value: {
                auth: {
                    user: null,
                },
                flash: {},
                errors: {},
            },
        },
    }),
    useForm: vi.fn(() => ({
        data: vi.fn(),
        transform: vi.fn(),
        defaults: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        submit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        cancel: vi.fn(),
        processing: false,
        progress: null,
        errors: {},
        hasErrors: false,
        data: {},
        isDirty: false,
        recentlySuccessful: false,
    })),
    Head: {
        name: "Head",
        template: "<head><slot /></head>",
    },
    Link: {
        name: "Link",
        template: "<a><slot /></a>",
        props: [
            "href",
            "method",
            "data",
            "headers",
            "replace",
            "preserve-scroll",
            "preserve-state",
        ],
    },
}));

// Global test configuration
config.global.mocks = {
    $page: {
        props: {
            auth: {
                user: null,
            },
            flash: {},
            errors: {},
        },
    },
};

// Mock window.axios if needed
Object.defineProperty(window, "axios", {
    value: {
        get: vi.fn(() => Promise.resolve({ data: {} })),
        post: vi.fn(() => Promise.resolve({ data: {} })),
        put: vi.fn(() => Promise.resolve({ data: {} })),
        delete: vi.fn(() => Promise.resolve({ data: {} })),
        defaults: {
            headers: {
                common: {},
            },
        },
    },
    writable: true,
});
