import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth'
import PriceDisplay from '@/components/global/PriceDisplay.vue'
import './style.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

const app = createApp(App)
const pinia = createPinia()

// Register global components
app.component('PriceDisplay', PriceDisplay)

app.use(pinia)
app.use(router)

// Initialize auth before mounting
const authStore = useAuthStore()
authStore.initialize().finally(() => {
  app.mount('#app')
})