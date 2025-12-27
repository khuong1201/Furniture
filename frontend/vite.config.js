import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default ({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  const APP = env.VITE_APP_TYPE 

  const port =
    APP === 'admin'
      ? Number(env.VITE_DEV_PORT_ADMIN)
      : Number(env.VITE_DEV_PORT_CUSTOMER)

  const openPath =
    APP === 'admin' ? '/admin' : '/customer'

  return defineConfig({
    plugins: [react()],
    server: {
      host: true,
      port: port,
      open: false, // docker
      watch: {
        usePolling: true,
      },
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      },
    },
    define: {
      __APP_TYPE__: JSON.stringify(APP),
    },
  })
}
