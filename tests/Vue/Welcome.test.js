import { describe, it, expect, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import Welcome from "@/Pages/Welcome.vue";

// Mock Inertia components
vi.mock("@inertiajs/vue3", () => ({
    Head: {
        name: "Head",
        template: "<head><slot /></head>",
    },
    Link: {
        name: "Link",
        template: "<a><slot /></a>",
        props: ["href"],
    },
}));

// Mock the layout component
vi.mock("@/Layouts/AppLayout.vue", () => ({
    default: {
        name: "AppLayout",
        template: `
      <div>
        <header><slot name="header" /></header>
        <main><slot /></main>
      </div>
    `,
        props: ["appName"],
    },
}));

// Mock the counter store
vi.mock("@/Stores/counter", () => ({
    useCounterStore: () => ({
        count: 0,
        increment: vi.fn(),
        decrement: vi.fn(),
        reset: vi.fn(),
    }),
}));

describe("Welcome Component", () => {
    beforeEach(() => {
        // Create a fresh Pinia instance for each test
        setActivePinia(createPinia());

        // Mock axios
        global.window = {
            axios: {
                get: vi.fn(() =>
                    Promise.resolve({ data: { message: "Test API response" } }),
                ),
            },
        };
    });

    it("renders welcome message", () => {
        const wrapper = mount(Welcome, {
            props: {
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        expect(wrapper.text()).toContain("Laravel + Vue 3 + Inertia.js");
        expect(wrapper.text()).toContain("Modern full-stack development");
    });

    it("displays Laravel version from props", () => {
        const wrapper = mount(Welcome, {
            props: {
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        expect(wrapper.text()).toContain("Laravel 11.0");
    });

    it("increments counter when button is clicked", async () => {
        const wrapper = mount(Welcome, {
            props: {
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        const button = wrapper.find("button");
        expect(button.text()).toContain("Click me! (0)");

        await button.trigger("click");
        expect(button.text()).toContain("Click me! (1)");

        await button.trigger("click");
        expect(button.text()).toContain("Click me! (2)");
    });

    it("displays tech stack information", () => {
        const wrapper = mount(Welcome, {
            props: {
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        expect(wrapper.text()).toContain("Tech Stack");
        expect(wrapper.text()).toContain("Laravel 11.0");
        expect(wrapper.text()).toContain("Vue 3.4.x");
        expect(wrapper.text()).toContain("Inertia.js");
        expect(wrapper.text()).toContain("Tailwind CSS");
    });

    it("has proper component structure", () => {
        const wrapper = mount(Welcome, {
            props: {
                laravelVersion: "11.0",
                phpVersion: "8.2",
            },
        });

        // Check for main sections
        expect(wrapper.find("header").exists()).toBe(true);
        expect(wrapper.find("main").exists()).toBe(true);

        // Check for interactive elements
        expect(wrapper.find("button").exists()).toBe(true);
        expect(wrapper.find("a").exists()).toBe(true);
    });
});
