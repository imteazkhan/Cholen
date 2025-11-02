// Auth utility functions
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

/**
 * Global logout function that can be used from anywhere in the app
 * @param {Object} options - Configuration options
 * @param {Function} options.toast - Toast notification function
 * @param {Object} options.router - Vue router instance
 * @param {boolean} options.showMessage - Whether to show logout message
 * @param {boolean} options.forceRefresh - Whether to force page refresh
 */
export const globalLogout = async (options = {}) => {
  const {
    toast = null,
    router = null,
    showMessage = true,
    forceRefresh = true
  } = options

  try {
    const authStore = useAuthStore()
    
    // Show signing out message
    if (showMessage && toast) {
      toast('Signing out...', 'info')
    }
    
    // Perform logout
    await authStore.logout()
    
    // Clear additional cached data
    if (typeof window !== 'undefined') {
      // Clear sessionStorage
      sessionStorage.clear()
      
      // Clear user-specific localStorage (keep app preferences)
      const keysToKeep = ['theme', 'language', 'app_preferences']
      const allKeys = Object.keys(localStorage)
      allKeys.forEach(key => {
        if (!keysToKeep.includes(key)) {
          localStorage.removeItem(key)
        }
      })
    }
    
    // Show success message
    if (showMessage && toast) {
      toast('Successfully signed out', 'success')
    }
    
    // Navigate to home
    if (router) {
      await router.push('/')
    } else if (typeof window !== 'undefined') {
      window.location.href = '/'
    }
    
    // Force refresh if requested
    if (forceRefresh && typeof window !== 'undefined') {
      setTimeout(() => {
        window.location.reload()
      }, 500)
    }
    
    return true
    
  } catch (error) {
    console.error('Global logout error:', error)
    
    if (showMessage && toast) {
      toast('Error during sign out', 'error')
    }
    
    // Fallback: force redirect
    if (typeof window !== 'undefined') {
      window.location.href = '/'
      if (forceRefresh) {
        setTimeout(() => window.location.reload(), 500)
      }
    }
    
    return false
  }
}

/**
 * Emergency logout - forces logout even if API calls fail
 */
export const emergencyLogout = () => {
  console.warn('🚨 Emergency logout triggered')
  
  // Clear all storage
  if (typeof window !== 'undefined') {
    localStorage.clear()
    sessionStorage.clear()
  }
  
  // Force redirect to home
  window.location.href = '/'
  setTimeout(() => window.location.reload(), 100)
}

/**
 * Check if user should be logged out (e.g., token expired)
 */
export const shouldLogout = (error) => {
  return error?.response?.status === 401 || 
         error?.response?.status === 403 ||
         error?.message?.includes('token') ||
         error?.message?.includes('unauthorized')
}