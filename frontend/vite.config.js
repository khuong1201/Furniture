import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path';

export default ({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return defineConfig({
    plugins: [react()],
    server: {
      port: Number(env.VITE_DEV_PORT),
      open: env.VITE_OPEN_PATH,
    },
    resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
  })
}
