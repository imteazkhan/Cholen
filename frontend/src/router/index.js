import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import HomeView from '../views/HomeView.vue'
import Dashboard from '../views/Dashboard.vue'
import AdminPanel from '../views/AdminPanel.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomeView,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true, roles: ['user', 'driver', 'admin'] }
  },
  {
    path: '/admin',
    name: 'AdminPanel',
    component: AdminPanel,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/login',
    redirect: '/'
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Redirect to home page if not authenticated
      next('/')
      return
    }
    
    // Check role-based access
    if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
      // Redirect to appropriate dashboard based on user role
      const redirectTo = authStore.getRoleBasedRoute(authStore.user.role)
      next(redirectTo)
      return
    }
  }
  
  // Auto-redirect authenticated users from home page to their dashboard
  if (to.path === '/' && authStore.isAuthenticated) {
    const redirectTo = authStore.getRoleBasedRoute(authStore.user.role)
    if (from.path !== redirectTo) {
      next(redirectTo)
      return
    }
  }
  
  next()
})

export default router