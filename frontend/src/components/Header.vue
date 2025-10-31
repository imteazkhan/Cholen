<template>
  <header class="sticky-top bg-white shadow-sm">
    <div class="container">
      <nav class="navbar navbar-expand-md navbar-light py-3">
        <div class="container-fluid">
          <router-link to="/" class="navbar-brand d-flex align-items-center">
            <i class="bi bi-hexagon text-primary me-2 fs-4"></i>
            <span class="fw-bold">Cholen!</span>
          </router-link>
          
          <button 
            class="navbar-toggler" 
            type="button" 
            @click="toggleNavbar"
            aria-expanded="false"
            aria-controls="navbarNav"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="collapse navbar-collapse" :class="{ show: showNavbar }" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
              <li class="nav-item">
                <router-link to="/" class="nav-link" @click="closeNavbar">Home</router-link>
              </li>
              <!-- <li class="nav-item">
                <a class="nav-link" href="#features" @click="scrollToSection('features')">Features</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" @click="navigateToHome">Pricing</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#contact" @click="scrollToSection('contact')">Contact</a>
              </li> -->
              <li v-if="authStore.isAuthenticated" class="nav-item">
                <router-link to="/dashboard" class="nav-link" @click="closeNavbar">Dashboard</router-link>
              </li>
            </ul>
            
            <UserProfile @close-navbar="closeNavbar" />
          </div>
        </div>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import UserProfile from './UserProfile.vue'

const router = useRouter()
const authStore = useAuthStore()
const showNavbar = ref(false)

const toggleNavbar = () => {
  showNavbar.value = !showNavbar.value
}

const closeNavbar = () => {
  showNavbar.value = false
}

const scrollToSection = (sectionId) => {
  closeNavbar()
  
  // If we're not on the home page, navigate to home first
  if (router.currentRoute.value.path !== '/') {
    router.push('/').then(() => {
      // Wait for navigation to complete, then scroll
      setTimeout(() => {
        const element = document.getElementById(sectionId)
        if (element) {
          element.scrollIntoView({ behavior: 'smooth' })
        }
      }, 300)
    })
    return
  }
  
  // If we're on home page, scroll to section
  setTimeout(() => {
    const element = document.getElementById(sectionId)
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    }
  }, 100)
}

const navigateToHome = () => {
  closeNavbar()
  // Navigate to home page using Vue Router
  router.push('/')
}
</script>