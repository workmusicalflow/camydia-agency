import { defineConfig } from "vite";
import { resolve } from "path";

export default defineConfig({
  plugins: [],
  build: {
    outDir: "public/build",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, "src/assets/js/main.js"),
        animations: resolve(__dirname, "src/assets/js/animations/index.js"),
      },
      output: {
        entryFileNames: "js/[name].[hash].js",
        chunkFileNames: "js/[name].[hash].js",
        assetFileNames: "assets/[name].[hash].[ext]",
      },
    },
  },
  server: {
    watch: {
      usePolling: true,
      interval: 100,
    },
  },
  optimizeDeps: {
    include: ["gsap"],
  },
});
