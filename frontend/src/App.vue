<template>
  <div class="app-container">
    <Header />
    <main>
      <router-view />
    </main>
    <Footer />
    <AuthModal ref="authModal" @auth-success="onAuthSuccess" />
  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import { useRouter } from 'vue-router'
import Header from './components/header.vue'
import Footer from './components/Footer.vue'
import AuthModal from './components/AuthModal.vue'

const router = useRouter()
const authModal = ref(null)

const showAuthModal = (isLogin = true) => {
  if (authModal.value) {
    authModal.value.setMode(isLogin)
    authModal.value.showModal()
  }
}

// Provide the showAuthModal function to child components
provide('showAuthModal', showAuthModal)

const onAuthSuccess = (authData) => {
  // Redirect based on user role
  if (authData && authData.redirectTo) {
    router.push(authData.redirectTo)
  } else {
    router.push('/dashboard')
  }
}
</script>

<style>
:root {
  --primary: #007BFF;
  --secondary: #6C757D;
  --background-light: #FFFFFF;
  --background-accent: #F8F9FA;
  --background-dark: #101922;
}

body {
  font-family: 'Inter', sans-serif;
  background-color: var(--background-light);
  color: #111418;
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Custom Bootstrap overrides */
.btn-primary {
  background-color: var(--primary);
  border-color: var(--primary);
}

.btn-primary:hover {
  background-color: #0069d9;
  border-color: #0062cc;
}

.bg-light-accent {
  background-color: var(--background-accent);
}

.text-primary-custom {
  color: var(--primary);
}

.bg-primary-light {
  background-color: rgba(0, 123, 255, 0.1);
}

/* Router link styles */
.router-link-active {
  color: var(--primary) !important;
  font-weight: 500;
}
</style>