import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import VueGoogleMaps from '@fawmi/vue-google-maps'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

// Toast notifications (you can install vue-toastification or use a simple solution)
const toast = {
    install(app) {
        app.config.globalProperties.$toast = {
            success: (message) => {
                // Simple toast implementation - you can replace with a proper toast library
                if (typeof window !== 'undefined') {
                    alert(`✅ ${message}`)
                }
            },
            error: (message) => {
                if (typeof window !== 'undefined') {
                    alert(`❌ ${message}`)
                }
            },
            info: (message) => {
                if (typeof window !== 'undefined') {
                    alert(`ℹ️ ${message}`)
                }
            }
        }
    }
}

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)

// Initialize auth store and check for existing session
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()

// Check for existing session and validate token
if (authStore.isAuthenticated) {
  authStore.fetchUser().then(() => {
    // Auto-redirect if on home page
    authStore.checkAuthAndRedirect(router)
    // Start session timer
    authStore.startSessionTimer()
  })
}

// Track user activity to refresh session
const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click']
let activityTimer = null

const resetActivityTimer = () => {
  if (activityTimer) clearTimeout(activityTimer)
  
  activityTimer = setTimeout(() => {
    if (authStore.isAuthenticated) {
      authStore.refreshSession()
    }
  }, 5 * 60 * 1000) // Reset session timer every 5 minutes of activity
}

// Add activity listeners
activityEvents.forEach(event => {
  document.addEventListener(event, resetActivityTimer, true)
})

// Suppress browser extension errors
window.addEventListener('unhandledrejection', event => {
  if (event.reason?.message?.includes('message channel closed') || 
      event.reason?.message?.includes('listener indicated an asynchronous response')) {
    event.preventDefault()
    console.debug('Suppressed browser extension error:', event.reason.message)
  }
})

// Suppress console errors from browser extensions
const originalError = console.error
console.error = (...args) => {
  const message = args.join(' ')
  if (message.includes('message channel closed') || 
      message.includes('listener indicated an asynchronous response')) {
    return // Suppress these errors
  }
  originalError.apply(console, args)
}

app.use(VueGoogleMaps, {
    load: {
        key: ''
    }
})

app.use(router)
app.use(toast)
app.mount('#app')