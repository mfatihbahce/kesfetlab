import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/sass/app.scss",
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/admin.css",
                "resources/js/admin.js",
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: "public/build",
        manifest: true,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});