export default defineNuxtConfig({
  devtools: { enabled: true },
  modules: ['@pinia/nuxt'],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
    },
  },
  typescript: {
    strict: true,
    shim: false,
  },
  routeRules: {
    '/': { redirect: '/login' },
  },
  build: {
    transpile: ['@fullcalendar/core', '@fullcalendar/vue3', '@fullcalendar/daygrid', '@fullcalendar/timegrid', '@fullcalendar/interaction'],
  },
  css: ['~/assets/css/main.css'],
  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0',
        },
      ],
    },
  },
})
