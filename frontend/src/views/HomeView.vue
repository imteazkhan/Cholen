<template>
  <div class="home">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
      <div class="container">
        <a class="navbar-brand fw-bold" href="#">
          <i class="bi bi-car-front-fill me-2"></i>RideNow
        </a>

        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item">
              <button class="nav-link btn btn-link text-white" @click="scrollToFeatures">
                Features
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link btn btn-link text-white" @click="scrollTo('#stats')">
                Stats
              </button>
            </li>
            <li class="nav-item ms-lg-3">
              <button
                v-if="!isLoggedIn"
                class="btn btn-outline-light btn-sm px-4"
                @click="goToLogin"
              >
                Login
              </button>
              <div v-else class="dropdown">
                <button
                  class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center"
                  type="button"
                  data-bs-toggle="dropdown"
                >
                  <i class="bi bi-person-circle me-1"></i>
                  <span class="d-none d-sm-inline">Hi, Alex</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="/profile">Profile</a></li>
                  <li><a class="dropdown-item" href="/trips">My Trips</a></li>
                  <li><hr class="dropdown-divider" /></li>
                  <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout">Logout</a></li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white py-5">
      <div class="container">
        <div class="row align-items-center min-vh-75">
          <div class="col-lg-6 order-lg-1 order-2 mt-4 mt-lg-0">
            <h1 class="display-4 fw-bold mb-4">Your Ride, Your Way</h1>
            <p class="lead mb-4">
              Book a safe, affordable ride in seconds – anywhere, anytime.
            </p>

            <div class="d-flex flex-column flex-sm-row gap-3">
              <button class="btn btn-light btn-lg px-5" @click="openRideModal">
                Book a Ride
              </button>
              <button class="btn btn-outline-light btn-lg px-5" @click="scrollToFeatures">
                Learn More
              </button>
            </div>
          </div>

          <div class="col-lg-6 order-lg-2 order-1 text-center">
            <div class="hero-image bg-light rounded-3 p-5 shadow mx-auto" style="max-width: 380px;">
              <i class="bi bi-car-front-fill display-1 text-primary"></i>
              <h3 class="mt-3 text-dark">Tap & Go</h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ride Booking Modal -->
    <teleport to="body">
      <transition name="modal">
        <div v-if="showRideModal" class="modal-backdrop" @click="closeRideModal">
          <div class="modal-dialog modal-dialog-centered modal-lg" @click.stop>
            <div class="modal-content rounded-3">
              <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold">Where are you going?</h5>
                <button class="btn-close" @click="closeRideModal" aria-label="Close"></button>
              </div>
              <div class="modal-body pt-2">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                      <input
                        type="text"
                        class="form-control form-control-lg"
                        placeholder="Pickup location"
                        v-model="pickup"
                      />
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-geo"></i></span>
                      <input
                        type="text"
                        class="form-control form-control-lg"
                        placeholder="Drop-off location"
                        v-model="dropoff"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer border-0 pt-2">
                <button class="btn btn-primary btn-lg w-100" @click="requestRide">
                  Find a Driver
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-white">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Us?</h2>
            <p class="lead text-muted">
              Real-time rides, transparent pricing, and top-notch safety.
            </p>
          </div>
        </div>

        <div class="row g-4">
          <div
            class="col-md-6 col-lg-4"
            v-for="feature in features"
            :key="feature.id"
          >
            <div class="card h-100 shadow-sm border-0 hover-card">
              <div class="card-body text-center p-4">
                <div class="feature-icon mb-3">
                  <i :class="feature.icon" class="display-4 text-primary"></i>
                </div>
                <h5 class="card-title">{{ feature.title }}</h5>
                <p class="card-text text-muted">{{ feature.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="bg-light py-5">
      <div class="container">
        <div class="row text-center g-4">
          <div class="col-6 col-md-3" v-for="stat in stats" :key="stat.label">
            <div class="stat-item p-3">
              <h3 class="display-5 fw-bold text-primary mb-1">{{ stat.value }}</h3>
              <p class="text-muted small mb-0">{{ stat.label }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
      <div class="container">
        <div class="row">
          <div class="col-12 text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Ride or Drive?</h2>
            <p class="lead text-muted mb-4">
              Join millions of riders and earn as a driver.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
              <button class="btn btn-primary btn-lg px-5" @click="openRideModal">
                Ride Now
              </button>
              <button class="btn btn-outline-primary btn-lg px-5" @click="goToDriver">
                Become a Driver
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
      <div class="container text-center">
        <p class="mb-0">&copy; 2025 RideNow. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// -------------------------------------------------
// Auth state (mock)
// -------------------------------------------------
const isLoggedIn = ref(false) // Set to true after login

// -------------------------------------------------
// Reactive data
// -------------------------------------------------
const features = ref([
  { id: 1, icon: 'bi bi-geo-alt-fill', title: 'Live Tracking', description: 'Watch your driver approach in real time on an interactive map.' },
  { id: 2, icon: 'bi bi-credit-card', title: 'Cashless Payments', description: 'Pay securely with credit card, Apple Pay, Google Pay or wallet.' },
  { id: 3, icon: 'bi bi-shield-lock-fill', title: 'Safety First', description: 'Verified drivers, emergency SOS, ride sharing with trusted contacts.' },
  { id: 4, icon: 'bi bi-currency-exchange', title: 'Transparent Pricing', description: 'See the fare upfront – no surge surprises, split fare with friends.' },
  { id: 5, icon: 'bi bi-star-fill', title: 'Ratings & Reviews', description: 'Rate your ride and driver, helping keep the community reliable.' },
  { id: 6, icon: 'bi bi-headset', title: '24/7 Support', description: 'In-app chat or call support whenever you need assistance.' }
])

const stats = ref([
  { value: '2M+', label: 'Rides Completed' },
  { value: '50K+', label: 'Active Drivers' },
  { value: '4.8 stars', label: 'Average Rating' },
  { value: '30s', label: 'Avg. Pickup Time' }
])

// -------------------------------------------------
// Modal state
// -------------------------------------------------
const showRideModal = ref(false)
const pickup = ref('')
const dropoff = ref('')

const openRideModal = () => (showRideModal.value = true)
const closeRideModal = () => {
  showRideModal.value = false
  pickup.value = ''
  dropoff.value = ''
}

const requestRide = () => {
  if (!pickup.value || !dropoff.value) {
    alert('Please enter both pickup and drop-off locations.')
    return
  }
  alert(`Searching for a driver from "${pickup.value}" to "${dropoff.value}"…`)
  closeRideModal()
}

// -------------------------------------------------
// Navigation
// -------------------------------------------------
const scrollToFeatures = () => {
  document.getElementById('features')?.scrollIntoView({ behavior: 'smooth' })
}

const scrollTo = (id) => {
  document.querySelector(id)?.scrollIntoView({ behavior: 'smooth' })
}

const goToLogin = () => {
  window.location.href = '/login' // or use router.push('/login')
}

const goToDriver = () => {
  window.location.href = '/driver-signup'
}

const logout = () => {
  isLoggedIn.value = false
  alert('Logged out successfully!')
}

// -------------------------------------------------
// Lifecycle
// -------------------------------------------------
onMounted(() => {
  console.log('Ride-sharing HomeView mounted')
  // Simulate login check
  // isLoggedIn.value = !!localStorage.getItem('token')
})
</script>

<style scoped>
/* Hero */
.hero-section {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
}
.min-vh-75 {
  min-height: 75vh;
}

/* Hover Effects */
.hover-card {
  transition: all 0.3s ease;
}
.hover-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}
.feature-icon {
  transition: transform 0.3s ease;
}
.hover-card:hover .feature-icon {
  transform: scale(1.1);
}

/* Buttons */
.btn {
  transition: all 0.3s ease;
}
.btn:hover {
  transform: translateY(-2px);
}

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  backdrop-filter: blur(4px);
}
.modal-dialog {
  width: 90%;
  max-width: 500px;
}
.modal-content {
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .display-4 {
    font-size: 2.5rem;
  }
  .display-5 {
    font-size: 1.8rem;
  }
  .hero-image {
    max-width: 250px !important;
  }
}
</style>