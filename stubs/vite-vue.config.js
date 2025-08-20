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
            host: "0.0.0.0",
        },
        cors: {
            origin: true,
            credentials: true,
        },
    },
    test: {
        globals: true,
        environment: "happy-dom",
        setupFiles: ["tests/Vue/setup.js"],
    },
});
