<template>
  <!-- Authenticated User Dropdown -->
  <div class="dropdown" v-if="authStore.isAuthenticated">
    <button 
      class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" 
      type="button" 
      @click="toggleDropdown"
      :class="{ show: showDropdown }"
    >
      <div class="avatar-circle me-2">
        {{ authStore.userInitials }}
      </div>
      <div class="d-flex flex-column align-items-start">
        <span class="fw-medium">{{ authStore.userName }}</span>
        <span class="badge" :class="`bg-${authStore.roleColor}`">{{ authStore.userRole }}</span>
      </div>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" :class="{ show: showDropdown }">
      <li>
        <h6 class="dropdown-header">Signed in as</h6>
      </li>
      <li>
        <span class="dropdown-item-text">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-2">
              {{ authStore.userInitials }}
            </div>
            <div>
              <div class="fw-medium">{{ authStore.userName }}</div>
              <small class="text-muted">{{ authStore.user.email }}</small>
              <div class="mt-1">
                <span class="badge" :class="`bg-${authStore.roleColor}`">{{ authStore.userRole }}</span>
              </div>
            </div>
          </div>
        </span>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <router-link to="/dashboard" class="dropdown-item" @click="handleMenuClick">
          <i class="bi bi-speedometer2 me-2"></i>
          Dashboard
        </router-link>
      </li>
      <li v-if="authStore.isAdmin">
        <router-link to="/admin" class="dropdown-item" @click="handleMenuClick">
          <i class="bi bi-gear me-2"></i>
          Admin Panel
        </router-link>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item" href="#" @click="handleLogout">
          <i class="bi bi-box-arrow-right me-2"></i>
          Sign Out
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Sign In/Sign Up Buttons for Non-authenticated Users -->
  <div v-else class="d-flex gap-2">
    <button class="btn btn-outline-primary" @click="showLoginModal">
      Sign In
    </button>
    <button class="btn btn-primary" @click="showRegisterModal">
      Sign Up
    </button>
  </div>
</template>

<script setup>
import { ref, inject, getCurrentInstance, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const emit = defineEmits(['close-navbar'])
const router = useRouter()
const authStore = useAuthStore()
const { appContext } = getCurrentInstance()

const showDropdown = ref(false)
const showAuthModal = inject('showAuthModal')

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const handleMenuClick = () => {
  showDropdown.value = false
  emit('close-navbar')
}

const showLoginModal = () => {
  if (showAuthModal) {
    showAuthModal(true) // true for login mode
  }
  emit('close-navbar')
}

const showRegisterModal = () => {
  if (showAuthModal) {
    showAuthModal(false) // false for register mode
  }
  emit('close-navbar')
}

const handleLogout = async () => {
  try {
    // Close dropdown and navbar
    showDropdown.value = false
    emit('close-navbar')
    
    // Show signing out message
    appContext.config.globalProperties.$toast?.info('Signing out...')
    
    // Use the enhanced logout with redirect
    await authStore.logoutAndRedirect(
      router, 
      (message) => appContext.config.globalProperties.$toast?.success(message)
    )
    
  } catch (error) {
    console.error('Logout error:', error)
    appContext.config.globalProperties.$toast?.error('Error during sign out')
    
    // Fallback: force redirect to home
    router.push('/').then(() => {
      setTimeout(() => window.location.reload(), 500)
    })
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.dropdown')) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-toggle {
  border: 1px solid #dee2e6;
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
}

.dropdown-toggle:hover {
  background-color: #f8f9fa;
  border-color: var(--bs-primary);
}

.dropdown-toggle.show {
  background-color: var(--bs-primary);
  color: white;
  border-color: var(--bs-primary);
}

.dropdown-menu {
  min-width: 280px;
  border: 1px solid #dee2e6;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.badge {
  font-size: 0.7em;
  font-weight: 500;
}

.dropdown-item-text {
  white-space: normal;
  padding: 0.75rem 1rem;
}

.avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
}

.dropdown-header {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6c757d;
}

.dropdown-item {
  padding: 0.5rem 1rem;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}
</style>