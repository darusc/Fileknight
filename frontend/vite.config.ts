import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    hmr: {
      host: 'localhost',
      protocol: 'ws',
      port: 5173,
    },
    watch: {
      usePolling: true,
      interval: 100,
    },
  }
})
