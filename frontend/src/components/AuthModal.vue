<template>
  <div v-if="showModal" class="modal-overlay" @click="hideModal">
    <div class="modal-dialog" @click.stop>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ isLoginMode ? 'Sign In' : 'Sign Up' }}
          </h5>
          <button type="button" class="btn-close" @click="hideModal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <!-- Login Form -->
          <form v-if="isLoginMode" @submit.prevent="handleLogin">
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <input 
                type="email" 
                class="form-control" 
                id="loginEmail" 
                v-model="loginForm.email"
                required
                placeholder="Enter your email"
              >
            </div>
            
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <input 
                type="password" 
                class="form-control" 
                id="loginPassword" 
                v-model="loginForm.password"
                required
                placeholder="Enter your password"
              >
            </div>

            <div class="mb-3">
              <label for="loginRole" class="form-label">Login as</label>
              <select class="form-select" id="loginRole" v-model="loginForm.role" required>
                <option value="">Select your role</option>
                <option value="user">
                  <i class="bi bi-person"></i> User - Book rides and travel
                </option>
                <option value="driver">
                  <i class="bi bi-car-front"></i> Driver - Provide rides and earn
                </option>
                <option value="admin">
                  <i class="bi bi-gear"></i> Admin - Manage the platform
                </option>
              </select>
              <div class="form-text">Choose the role you want to login as</div>
            </div>

            <!-- Admin access code for admin login -->
            <div v-if="loginForm.role === 'admin'" class="mb-3">
              <label for="loginAdminCode" class="form-label">Admin Access Code</label>
              <input 
                type="password" 
                class="form-control" 
                id="loginAdminCode" 
                v-model="loginForm.adminCode"
                placeholder="Enter admin access code"
                required
              >
              <div class="form-text">Admin access code is required for admin login</div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-box-arrow-in-right me-2"></i>
              Sign In as {{ loginForm.role ? loginForm.role.charAt(0).toUpperCase() + loginForm.role.slice(1) : 'User' }}
            </button>
            
            <div class="text-center mt-3">
              <a href="#" class="text-decoration-none" @click="toggleMode">
                Don't have an account? Sign up
              </a>
            </div>

            <!-- Demo accounts info -->
            <div class="mt-4 p-3 bg-light rounded">
              <h6 class="text-muted mb-2">Demo Accounts:</h6>
              <small class="text-muted d-block">User: user@gmail.com / 12345678</small>
              <small class="text-muted d-block">Driver: driver@gmail.com / 12345678</small>
              <small class="text-muted d-block">Admin: admin@gmail.com / 12345678 (Code: admin123)</small>
            </div>
          </form>
          
          <!-- Registration Form -->
          <form v-else @submit.prevent="handleRegister">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="regFirstName" class="form-label">First Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="regFirstName" 
                  v-model="registerForm.firstName"
                  required
                >
              </div>
              <div class="col-md-6 mb-3">
                <label for="regLastName" class="form-label">Last Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="regLastName" 
                  v-model="registerForm.lastName"
                  required
                >
              </div>
            </div>
            
            <div class="mb-3">
              <label for="regEmail" class="form-label">Email</label>
              <input 
                type="email" 
                class="form-control" 
                id="regEmail" 
                v-model="registerForm.email"
                required
              >
            </div>
            
            <div class="mb-3">
              <label for="regRole" class="form-label">Join as</label>
              <select class="form-select" id="regRole" v-model="registerForm.role" required>
                <option value="">Select your role</option>
                <option value="user">
                  <i class="bi bi-person"></i> User - Book rides and travel
                </option>
                <option value="driver">
                  <i class="bi bi-car-front"></i> Driver - Provide rides and earn
                </option>
              </select>
              <small class="form-text text-muted">
                Note: Admin access is granted by existing administrators only
              </small>
            </div>
            
            <!-- Additional fields for drivers -->
            <div v-if="registerForm.role === 'driver'" class="mb-3">
              <label for="regLicense" class="form-label">Driver License Number</label>
              <input 
                type="text" 
                class="form-control" 
                id="regLicense" 
                v-model="registerForm.driverLicense"
                placeholder="Enter your valid driver license number"
                required
              >
              <small class="form-text text-muted">
                Your driver account will be reviewed and approved by our admin team
              </small>
            </div>
            
            <div class="mb-3">
              <label for="regPassword" class="form-label">Password</label>
              <input 
                type="password" 
                class="form-control" 
                id="regPassword" 
                v-model="registerForm.password"
                required
                minlength="6"
              >
              <div class="form-text">Password must be at least 6 characters long.</div>
            </div>
            
            <div class="mb-3">
              <label for="regConfirmPassword" class="form-label">Confirm Password</label>
              <input 
                type="password" 
                class="form-control" 
                id="regConfirmPassword" 
                v-model="registerForm.confirmPassword"
                required
              >
            </div>
            
            <button type="submit" class="btn btn-primary w-100" :disabled="loading || !passwordsMatch">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              Sign Up
            </button>
            
            <div class="text-center mt-3">
              <a href="#" class="text-decoration-none" @click="toggleMode">
                Already have an account? Sign in
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, getCurrentInstance } from 'vue'
import { useAuthStore } from '../stores/auth'

const emit = defineEmits(['auth-success'])
const { appContext } = getCurrentInstance()

const isLoginMode = ref(true)
const loading = ref(false)
const showModal = ref(false)

const loginForm = ref({
  email: '',
  password: '',
  role: '',
  adminCode: ''
})

const registerForm = ref({
  firstName: '',
  lastName: '',
  email: '',
  role: '',
  password: '',
  confirmPassword: '',
  driverLicense: ''
})

const passwordsMatch = computed(() => {
  return registerForm.value.password === registerForm.value.confirmPassword
})

const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value
  resetForms()
}

const resetForms = () => {
  loginForm.value = { 
    email: '', 
    password: '', 
    role: '', 
    adminCode: '' 
  }
  registerForm.value = {
    firstName: '',
    lastName: '',
    email: '',
    role: '',
    password: '',
    confirmPassword: '',
    driverLicense: ''
  }
}

const handleLogin = async () => {
  loading.value = true
  
  try {
    const authStore = useAuthStore()
    
    const credentials = {
      email: loginForm.value.email,
      password: loginForm.value.password,
      role: loginForm.value.role,
      admin_code: loginForm.value.role === 'admin' ? loginForm.value.adminCode : null
    }
    
    const result = await authStore.login(credentials)
    
    if (result.success) {
      hideModal()
      emit('auth-success', { user: result.user, redirectTo: result.redirectTo })
      
      // Role-specific welcome messages
      const roleMessages = {
        user: `Welcome back, ${result.user.first_name}! Ready to book your next ride?`,
        driver: `Welcome back, ${result.user.first_name}! Ready to start earning?`,
        admin: `Welcome back, ${result.user.first_name}! Admin dashboard is ready.`
      }
      
      appContext.config.globalProperties.$toast?.success(
        roleMessages[result.user.role] || `Welcome back, ${result.user.first_name}!`
      )
    } else {
      throw new Error(result.message)
    }
  } catch (error) {
    appContext.config.globalProperties.$toast?.error(error.message || 'Login failed')
  } finally {
    loading.value = false
  }
}

const handleRegister = async () => {
  if (!passwordsMatch.value) {
    appContext.config.globalProperties.$toast?.error('Passwords do not match')
    return
  }
  
  loading.value = true
  
  try {
    const authStore = useAuthStore()
    
    const userData = {
      first_name: registerForm.value.firstName,
      last_name: registerForm.value.lastName,
      email: registerForm.value.email,
      password: registerForm.value.password,
      password_confirmation: registerForm.value.confirmPassword,
      role: registerForm.value.role,
      driver_license: registerForm.value.role === 'driver' ? registerForm.value.driverLicense : null
    }
    
    const result = await authStore.register(userData)
    
    if (result.success) {
      hideModal()
      const redirectTo = authStore.getRoleBasedRoute(result.user.role)
      emit('auth-success', { user: result.user, redirectTo })
      appContext.config.globalProperties.$toast?.success(`Welcome to Cholen App, ${result.user.first_name}!`)
    } else {
      // Handle validation errors
      if (result.errors) {
        const errorMessages = Object.values(result.errors).flat()
        appContext.config.globalProperties.$toast?.error(errorMessages.join(', '))
      } else {
        throw new Error(result.message)
      }
    }
  } catch (error) {
    appContext.config.globalProperties.$toast?.error(error.message || 'Registration failed')
  } finally {
    loading.value = false
  }
}

const hideModal = () => {
  showModal.value = false
}

const setMode = (isLogin) => {
  isLoginMode.value = isLogin
  resetForms()
}

// Demo users are now managed by the Laravel backend database

// Expose methods for parent components
defineExpose({
  showModal: () => { showModal.value = true },
  hideModal,
  setMode
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal-dialog {
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content {
  border: none;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  background: white;
}

.modal-header {
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem;
  color: #6c757d;
}

.btn-close:hover {
  color: #000;
}

.modal-body {
  padding: 1.5rem;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.btn-primary {
  padding: 0.75rem;
  font-weight: 600;
}
</style>