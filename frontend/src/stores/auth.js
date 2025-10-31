import { defineStore } from 'pinia'
import { authAPI } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token'),
    loading: false,
    sessionTimer: null
  }),

  actions: {
    async login(credentials) {
      this.loading = true
      try {
        const response = await authAPI.login(credentials)
        
        if (response.data.success) {
          this.user = response.data.user
          this.token = response.data.token
          this.isAuthenticated = true
          
          // Store in localStorage
          localStorage.setItem('user', JSON.stringify(response.data.user))
          localStorage.setItem('auth_token', response.data.token)
          
          // Update last login time
          this.user.last_login_at = new Date().toISOString()
          localStorage.setItem('user', JSON.stringify(this.user))
          
          // Start session timer
          this.startSessionTimer()
          
          return { 
            success: true, 
            user: response.data.user,
            redirectTo: this.getRoleBasedRoute(response.data.user.role)
          }
        } else {
          throw new Error(response.data.message || 'Login failed')
        }
      } catch (error) {
        let message = error.response?.data?.message || error.message || 'Login failed'
        const errors = error.response?.data?.errors || {}
        
        // If there are validation errors, format them nicely
        if (Object.keys(errors).length > 0) {
          const errorMessages = Object.values(errors).flat()
          message = errorMessages.join('. ')
        }
        
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      try {
        const response = await authAPI.register(userData)
        
        if (response.data.success) {
          this.user = response.data.user
          this.token = response.data.token
          this.isAuthenticated = true
          
          // Store in localStorage
          localStorage.setItem('user', JSON.stringify(response.data.user))
          localStorage.setItem('auth_token', response.data.token)
          
          return { success: true, user: response.data.user }
        } else {
          throw new Error(response.data.message || 'Registration failed')
        }
      } catch (error) {
        let message = error.response?.data?.message || error.message || 'Registration failed'
        const errors = error.response?.data?.errors || {}
        
        // If there are validation errors, format them nicely
        if (Object.keys(errors).length > 0) {
          const errorMessages = Object.values(errors).flat()
          message = errorMessages.join('. ')
        }
        
        return { success: false, message, errors }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        if (this.token) {
          await authAPI.logout()
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear state regardless of API call success
        this.user = null
        this.token = null
        this.isAuthenticated = false
        this.loading = false
        
        // Clear session timer
        if (this.sessionTimer) {
          clearTimeout(this.sessionTimer)
          this.sessionTimer = null
        }
        
        // Clear localStorage
        localStorage.removeItem('user')
        localStorage.removeItem('auth_token')
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      try {
        const response = await authAPI.getUser()
        if (response.data.success) {
          this.user = response.data.user
          localStorage.setItem('user', JSON.stringify(response.data.user))
        }
      } catch (error) {
        console.error('Fetch user error:', error)
        // If token is invalid, logout
        if (error.response?.status === 401) {
          this.logout()
        }
      }
    },

    updateProfile(profileData) {
      if (this.user) {
        this.user = { ...this.user, ...profileData }
        localStorage.setItem('user', JSON.stringify(this.user))
      }
    },

    getRoleBasedRoute(role) {
      switch (role) {
        case 'admin':
          return '/admin'
        case 'driver':
          return '/dashboard'
        case 'user':
          return '/dashboard'
        default:
          return '/dashboard'
      }
    },

    checkAuthAndRedirect(router) {
      if (this.isAuthenticated && this.user) {
        const targetRoute = this.getRoleBasedRoute(this.user.role)
        if (router.currentRoute.value.path === '/' || router.currentRoute.value.path === '/login') {
          router.push(targetRoute)
        }
      }
    },

    startSessionTimer() {
      // Auto-logout after 24 hours of inactivity
      const sessionTimeout = 24 * 60 * 60 * 1000 // 24 hours
      
      if (this.sessionTimer) {
        clearTimeout(this.sessionTimer)
      }
      
      this.sessionTimer = setTimeout(() => {
        this.logout()
        // Show session expired message
        if (typeof window !== 'undefined') {
          alert('⏰ Your session has expired. Please log in again.')
        }
      }, sessionTimeout)
    },

    refreshSession() {
      if (this.isAuthenticated) {
        this.startSessionTimer()
      }
    }
  },

  getters: {
    isAdmin: (state) => state.user?.role === 'admin',
    isDriver: (state) => state.user?.role === 'driver',
    isUser: (state) => state.user?.role === 'user',
    userName: (state) => {
      if (state.user?.first_name && state.user?.last_name) {
        return `${state.user.first_name} ${state.user.last_name}`
      }
      return state.user?.name || 'User'
    },
    userInitials: (state) => {
      if (state.user?.first_name && state.user?.last_name) {
        return `${state.user.first_name.charAt(0)}${state.user.last_name.charAt(0)}`
      }
      return state.user?.name?.charAt(0) || 'U'
    },
    userRole: (state) => {
      if (!state.user?.role) return 'Guest'
      return state.user.role.charAt(0).toUpperCase() + state.user.role.slice(1)
    },
    roleColor: (state) => {
      switch (state.user?.role) {
        case 'admin': return 'danger'
        case 'driver': return 'warning'
        case 'user': return 'primary'
        default: return 'secondary'
      }
    }
  }
})