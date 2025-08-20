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
            jsxRuntime: "automatic",
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "@": resolve(__dirname, "resources/js"),
            "@components": resolve(__dirname, "resources/js/Components"),
            "@layouts": resolve(__dirname, "resources/js/Layouts"),
            "@pages": resolve(__dirname, "resources/js/Pages"),
            "@hooks": resolve(__dirname, "resources/js/Hooks"),
            "@lib": resolve(__dirname, "resources/js/lib"),
        },
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
        setupFiles: ["tests/React/setup.js"],
    },
});
