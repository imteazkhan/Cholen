<template>
  <section id="hero" class="py-5 py-md-5">
    <div class="container">
      <div class="row justify-content-center text-center">
        <div class="col-12">
          <h1 class="display-4 fw-bold mb-4">Go anywhere with us!</h1>
          <p class="lead text-muted mb-4">Book your ride in just a few clicks</p>
        </div>
        <div class="col-12 col-lg-10">
          <form @submit.prevent="handleBookRide" class="ride-booking-form">
            <div class="card shadow-sm">
              <div class="card-body p-3">
                <div class="row g-2">
                  <!-- Pickup Location -->
                  <div class="col-12 col-md-3">
                    <div class="input-group">
                      <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-geo-alt text-muted"></i>
                      </span>
                      <input type="text" class="form-control border-start-0" placeholder="Pickup location"
                        v-model="bookingForm.pickupLocation" :class="{ 'is-invalid': errors.pickupLocation }"
                        @input="onLocationInput('pickup')" @focus="showLocationSuggestions('pickup')"
                        @blur="hideLocationSuggestions('pickup')" @keydown="handleKeyNavigation($event, 'pickup')"
                        required>
                      <div v-if="errors.pickupLocation" class="invalid-feedback">
                        {{ errors.pickupLocation }}
                      </div>
                    </div>
                    <!-- Location Suggestions -->
                    <div v-if="showPickupSuggestions && locationSuggestions.length > 0" class="location-suggestions">
                      <div v-for="(suggestion, index) in locationSuggestions" :key="suggestion.id"
                        class="suggestion-item" :class="{ 'suggestion-selected': index === selectedSuggestionIndex }"
                        @mousedown="selectLocation('pickup', suggestion)">
                        <div class="suggestion-content">
                          <div class="suggestion-main">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>
                            <span class="suggestion-name">{{ suggestion.name }}</span>
                          </div>
                          <div class="suggestion-details">
                            <span class="suggestion-area">{{ suggestion.area }}</span>
                            <span class="suggestion-category">{{ suggestion.category }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Dropoff Location -->
                  <div class="col-12 col-md-3">
                    <div class="input-group">
                      <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-pin-map text-muted"></i>
                      </span>
                      <input type="text" class="form-control border-start-0" placeholder="Dropoff location"
                        v-model="bookingForm.dropoffLocation" :class="{ 'is-invalid': errors.dropoffLocation }"
                        @input="onLocationInput('dropoff')" @focus="showLocationSuggestions('dropoff')"
                        @blur="hideLocationSuggestions('dropoff')" @keydown="handleKeyNavigation($event, 'dropoff')"
                        required>
                      <div v-if="errors.dropoffLocation" class="invalid-feedback">
                        {{ errors.dropoffLocation }}
                      </div>
                    </div>
                    <!-- Location Suggestions -->
                    <div v-if="showDropoffSuggestions && locationSuggestions.length > 0" class="location-suggestions">
                      <div v-for="(suggestion, index) in locationSuggestions" :key="suggestion.id" class="suggestion-item"
                        :class="{ 'suggestion-selected': index === selectedSuggestionIndex }"
                        @mousedown="selectLocation('dropoff', suggestion)">
                        <div class="suggestion-content">
                          <div class="suggestion-main">
                            <i class="bi bi-pin-map me-2 text-danger"></i>
                            <span class="suggestion-name">{{ suggestion.name }}</span>
                          </div>
                          <div class="suggestion-details">
                            <span class="suggestion-area">{{ suggestion.area }}</span>
                            <span class="suggestion-category">{{ suggestion.category }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Date -->
                  <div class="col-12 col-md-2">
                    <div class="input-group">
                      <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-calendar3 text-muted"></i>
                      </span>
                      <input type="date" class="form-control border-start-0" v-model="bookingForm.date" :min="minDate"
                        :class="{ 'is-invalid': errors.date }" @change="clearError('date')" required>
                      <div v-if="errors.date" class="invalid-feedback">
                        {{ errors.date }}
                      </div>
                    </div>
                  </div>

                  <!-- Time -->
                  <div class="col-12 col-md-2">
                    <div class="input-group">
                      <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-clock text-muted"></i>
                      </span>
                      <input type="time" class="form-control border-start-0" v-model="bookingForm.time"
                        :class="{ 'is-invalid': errors.time }" @change="clearError('time')" required>
                      <div v-if="errors.time" class="invalid-feedback">
                        {{ errors.time }}
                      </div>
                    </div>
                  </div>

                  <!-- Search Button -->
                  <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100 h-100" :disabled="loading || !isFormValid">
                      <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-search me-2"></i>
                      {{ loading ? 'Searching...' : 'See Prices' }}
                    </button>
                  </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mt-3">
                  <div class="col-12">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="setCurrentLocation"
                        :disabled="gettingLocation">
                        <span v-if="gettingLocation" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-geo-alt-fill me-1"></i>
                        Use Current Location
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="swapLocations"
                        :disabled="!bookingForm.pickupLocation || !bookingForm.dropoffLocation">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        Swap
                      </button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearForm">
                        <i class="bi bi-x-circle me-1"></i>
                        Clear
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>

          <!-- Popular Routes -->
          <div class="mt-4">
            <h6 class="text-muted mb-3">Popular Routes</h6>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
              <button v-for="route in popularRoutes" :key="route.id" type="button" class="btn btn-outline-primary btn-sm"
                @click="selectPopularRoute(route)">
                {{ route.from }} → {{ route.to }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { rideAPI } from '../services/api'

const router = useRouter()
const authStore = useAuthStore()
const { appContext } = getCurrentInstance()

// Form data
const bookingForm = ref({
  pickupLocation: '',
  dropoffLocation: '',
  date: '',
  time: ''
})

// Form state
const loading = ref(false)
const gettingLocation = ref(false)
const errors = ref({})

// Location suggestions
const showPickupSuggestions = ref(false)
const showDropoffSuggestions = ref(false)
const locationSuggestions = ref([])
const selectedSuggestionIndex = ref(-1)

// Popular routes data
const popularRoutes = ref([
  { id: 1, from: 'Airport', to: 'Downtown' },
  { id: 2, from: 'Mall', to: 'University' },
  { id: 3, from: 'Train Station', to: 'Business District' },
  { id: 4, from: 'Hospital', to: 'Residential Area' }
])

// Comprehensive Dhaka location data for suggestions
const mockLocations = [
  // Airports & Transport Hubs
  { id: 1, name: 'Hazrat Shahjalal International Airport', category: 'Airport', area: 'Airport' },
  { id: 2, name: 'Kamalapur Railway Station', category: 'Transport', area: 'Kamalapur' },
  { id: 3, name: 'Sadarghat Launch Terminal', category: 'Transport', area: 'Sadarghat' },

  // Major Areas - Gulshan
  { id: 4, name: 'Gulshan Circle 1', category: 'Area', area: 'Gulshan' },
  { id: 5, name: 'Gulshan Circle 2', category: 'Area', area: 'Gulshan' },
  { id: 6, name: 'Gulshan Avenue', category: 'Area', area: 'Gulshan' },

  // Major Areas - Banani
  { id: 7, name: 'Banani 11', category: 'Area', area: 'Banani' },
  { id: 8, name: 'Banani Road 27', category: 'Area', area: 'Banani' },

  // Major Areas - Dhanmondi
  { id: 9, name: 'Dhanmondi 27', category: 'Area', area: 'Dhanmondi' },
  { id: 10, name: 'Dhanmondi 32', category: 'Area', area: 'Dhanmondi' },
  { id: 11, name: 'Dhanmondi Lake', category: 'Landmark', area: 'Dhanmondi' },

  // Major Areas - Uttara
  { id: 12, name: 'Uttara Sector 1', category: 'Area', area: 'Uttara' },
  { id: 13, name: 'Uttara Sector 7', category: 'Area', area: 'Uttara' },
  { id: 14, name: 'Uttara Sector 10', category: 'Area', area: 'Uttara' },

  // Major Areas - Mirpur
  { id: 15, name: 'Mirpur 1', category: 'Area', area: 'Mirpur' },
  { id: 16, name: 'Mirpur 10', category: 'Area', area: 'Mirpur' },
  { id: 17, name: 'Mirpur 12', category: 'Area', area: 'Mirpur' },

  // Shopping & Commercial
  { id: 18, name: 'Bashundhara City Mall', category: 'Shopping', area: 'Panthapath' },
  { id: 19, name: 'New Market', category: 'Shopping', area: 'New Market' },
  { id: 20, name: 'Jamuna Future Park', category: 'Shopping', area: 'Baridhara' },
  { id: 21, name: 'Motijheel Commercial Area', category: 'Commercial', area: 'Motijheel' },

  // Universities & Educational
  { id: 22, name: 'Dhaka University', category: 'University', area: 'Shahbag' },
  { id: 23, name: 'BUET', category: 'University', area: 'Palashi' },
  { id: 24, name: 'North South University', category: 'University', area: 'Bashundhara' },

  // Hospitals
  { id: 25, name: 'Dhaka Medical College Hospital', category: 'Hospital', area: 'Shahbag' },
  { id: 26, name: 'Square Hospital', category: 'Hospital', area: 'Panthapath' },
  { id: 27, name: 'United Hospital', category: 'Hospital', area: 'Gulshan' },

  // Other Major Areas
  { id: 28, name: 'Wari', category: 'Area', area: 'Wari' },
  { id: 29, name: 'Old Dhaka', category: 'Area', area: 'Old Dhaka' },
  { id: 30, name: 'Tejgaon', category: 'Area', area: 'Tejgaon' },
  { id: 31, name: 'Farmgate', category: 'Area', area: 'Farmgate' },
  { id: 32, name: 'Mohakhali', category: 'Area', area: 'Mohakhali' },
  { id: 33, name: 'Badda', category: 'Area', area: 'Badda' },
  { id: 34, name: 'Rampura', category: 'Area', area: 'Rampura' },
  { id: 35, name: 'Malibagh', category: 'Area', area: 'Malibagh' }
]

// Computed properties
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const isFormValid = computed(() => {
  return bookingForm.value.pickupLocation &&
    bookingForm.value.dropoffLocation &&
    bookingForm.value.date &&
    bookingForm.value.time &&
    Object.keys(errors.value).length === 0
})

// Methods
const handleBookRide = async () => {
  if (!validateForm()) return

  loading.value = true

  try {
    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      appContext.config.globalProperties.$toast?.info('Please sign in to book a ride')
      return
    }

    // Check if user has the right role
    if (!authStore.isUser) {
      appContext.config.globalProperties.$toast?.error('Only users can book rides')
      return
    }

    // Book the ride
    const response = await rideAPI.bookRide({
      pickup_location: bookingForm.value.pickupLocation,
      dropoff_location: bookingForm.value.dropoffLocation,
      ride_date: bookingForm.value.date,
      ride_time: bookingForm.value.time,
      vehicle_type: 'standard',
      passenger_count: 1
    })

    if (response.data.success) {
      const ride = response.data.ride

      appContext.config.globalProperties.$toast?.success(
        `Ride booked successfully! Estimated price: BDT ${ride.estimated_price} | ${ride.distance_km} km`
      )

      // Clear the form
      clearForm()

      // Navigate to dashboard to see the booking
      router.push('/dashboard')
    } else {
      throw new Error(response.data.message)
    }

  } catch (error) {
    const message = error.response?.data?.message || error.message || 'Failed to book ride. Please try again.'
    appContext.config.globalProperties.$toast?.error(message)
  } finally {
    loading.value = false
  }
}

const validateForm = () => {
  errors.value = {}

  if (!bookingForm.value.pickupLocation) {
    errors.value.pickupLocation = 'Pickup location is required'
  }

  if (!bookingForm.value.dropoffLocation) {
    errors.value.dropoffLocation = 'Dropoff location is required'
  }

  if (bookingForm.value.pickupLocation === bookingForm.value.dropoffLocation) {
    errors.value.dropoffLocation = 'Pickup and dropoff locations must be different'
  }

  if (!bookingForm.value.date) {
    errors.value.date = 'Date is required'
  } else {
    const selectedDate = new Date(bookingForm.value.date)
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    if (selectedDate < today) {
      errors.value.date = 'Date cannot be in the past'
    }
  }

  if (!bookingForm.value.time) {
    errors.value.time = 'Time is required'
  } else if (bookingForm.value.date === minDate.value) {
    // If booking for today, check if time is not in the past
    const now = new Date()
    const selectedTime = new Date(`${bookingForm.value.date}T${bookingForm.value.time}`)

    if (selectedTime <= now) {
      errors.value.time = 'Time must be in the future'
    }
  }

  return Object.keys(errors.value).length === 0
}

const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field]
  }
}

const filterLocationSuggestions = (query) => {
  if (!query || query.length < 1) {
    return mockLocations.slice(0, 8) // Show first 8 locations if no query
  }

  const searchQuery = query.toLowerCase()

  // Filter locations based on name, area, or category
  const filtered = mockLocations.filter(location =>
    location.name.toLowerCase().includes(searchQuery) ||
    location.area.toLowerCase().includes(searchQuery) ||
    location.category.toLowerCase().includes(searchQuery)
  )

  // Sort by relevance (exact matches first, then starts with, then contains)
  return filtered.sort((a, b) => {
    const aName = a.name.toLowerCase()
    const bName = b.name.toLowerCase()

    // Exact match
    if (aName === searchQuery) return -1
    if (bName === searchQuery) return 1

    // Starts with query
    if (aName.startsWith(searchQuery) && !bName.startsWith(searchQuery)) return -1
    if (bName.startsWith(searchQuery) && !aName.startsWith(searchQuery)) return 1

    // Alphabetical order for same relevance
    return aName.localeCompare(bName)
  }).slice(0, 8) // Limit to 8 suggestions
}

const onLocationInput = (type) => {
  clearError(type === 'pickup' ? 'pickupLocation' : 'dropoffLocation')

  const query = type === 'pickup' ? bookingForm.value.pickupLocation : bookingForm.value.dropoffLocation
  locationSuggestions.value = filterLocationSuggestions(query)

  if (type === 'pickup') {
    showPickupSuggestions.value = true
    showDropoffSuggestions.value = false
  } else {
    showDropoffSuggestions.value = true
    showPickupSuggestions.value = false
  }
}

const showLocationSuggestions = (type) => {
  const query = type === 'pickup' ? bookingForm.value.pickupLocation : bookingForm.value.dropoffLocation
  locationSuggestions.value = filterLocationSuggestions(query)

  if (type === 'pickup') {
    showPickupSuggestions.value = true
    showDropoffSuggestions.value = false
  } else {
    showDropoffSuggestions.value = true
    showPickupSuggestions.value = false
  }
}

const hideLocationSuggestions = (type) => {
  // Delay hiding to allow click events on suggestions
  setTimeout(() => {
    if (type === 'pickup') {
      showPickupSuggestions.value = false
    } else {
      showDropoffSuggestions.value = false
    }
    selectedSuggestionIndex.value = -1
  }, 200)
}

const handleKeyNavigation = (event, type) => {
  const suggestions = locationSuggestions.value
  const isShowingSuggestions = type === 'pickup' ? showPickupSuggestions.value : showDropoffSuggestions.value

  if (!isShowingSuggestions || suggestions.length === 0) return

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      selectedSuggestionIndex.value = Math.min(selectedSuggestionIndex.value + 1, suggestions.length - 1)
      break

    case 'ArrowUp':
      event.preventDefault()
      selectedSuggestionIndex.value = Math.max(selectedSuggestionIndex.value - 1, -1)
      break

    case 'Enter':
      event.preventDefault()
      if (selectedSuggestionIndex.value >= 0 && selectedSuggestionIndex.value < suggestions.length) {
        selectLocation(type, suggestions[selectedSuggestionIndex.value])
      }
      break

    case 'Escape':
      event.preventDefault()
      if (type === 'pickup') {
        showPickupSuggestions.value = false
      } else {
        showDropoffSuggestions.value = false
      }
      selectedSuggestionIndex.value = -1
      break
  }
}

const selectLocation = (type, location) => {
  if (type === 'pickup') {
    bookingForm.value.pickupLocation = location.name
    showPickupSuggestions.value = false
  } else {
    bookingForm.value.dropoffLocation = location.name
    showDropoffSuggestions.value = false
  }
  clearError(type === 'pickup' ? 'pickupLocation' : 'dropoffLocation')
}

const setCurrentLocation = async () => {
  if (!navigator.geolocation) {
    appContext.config.globalProperties.$toast?.error('Geolocation is not supported by this browser')
    return
  }

  gettingLocation.value = true

  try {
    const position = await new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
      })
    })

    // Mock reverse geocoding - in real app, you'd use a geocoding service
    const mockCurrentLocation = 'Current Location (Lat: ' +
      position.coords.latitude.toFixed(4) + ', Lng: ' +
      position.coords.longitude.toFixed(4) + ')'

    bookingForm.value.pickupLocation = mockCurrentLocation
    clearError('pickupLocation')

    appContext.config.globalProperties.$toast?.success('Current location set as pickup')

  } catch (error) {
    appContext.config.globalProperties.$toast?.error('Unable to get your location. Please enter manually.')
  } finally {
    gettingLocation.value = false
  }
}

const swapLocations = () => {
  const temp = bookingForm.value.pickupLocation
  bookingForm.value.pickupLocation = bookingForm.value.dropoffLocation
  bookingForm.value.dropoffLocation = temp

  clearError('pickupLocation')
  clearError('dropoffLocation')

  appContext.config.globalProperties.$toast?.info('Locations swapped')
}

const clearForm = () => {
  bookingForm.value = {
    pickupLocation: '',
    dropoffLocation: '',
    date: '',
    time: ''
  }
  errors.value = {}

  appContext.config.globalProperties.$toast?.info('Form cleared')
}

const selectPopularRoute = (route) => {
  bookingForm.value.pickupLocation = route.from
  bookingForm.value.dropoffLocation = route.to

  clearError('pickupLocation')
  clearError('dropoffLocation')

  appContext.config.globalProperties.$toast?.info(`Selected route: ${route.from} → ${route.to}`)
}

// Initialize form with current date and time
onMounted(() => {
  const now = new Date()
  bookingForm.value.date = now.toISOString().split('T')[0]

  // Set time to next hour
  const nextHour = new Date(now.getTime() + 60 * 60 * 1000)
  bookingForm.value.time = nextHour.toTimeString().slice(0, 5)

  // Hide suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.location-suggestions') && !e.target.closest('input')) {
      showPickupSuggestions.value = false
      showDropoffSuggestions.value = false
    }
  })
})
</script>

<style scoped>
.ride-booking-form {
  position: relative;
}

.location-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  z-index: 1000;
  max-height: 280px;
  overflow-y: auto;
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.suggestion-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #f1f3f4;
  transition: all 0.15s ease-in-out;
  position: relative;
}

.suggestion-item:hover,
.suggestion-selected {
  background-color: #f8f9fa;
  border-left: 3px solid #007bff;
  padding-left: 0.875rem;
}

.suggestion-selected {
  background-color: #e3f2fd !important;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.suggestion-main {
  display: flex;
  align-items: center;
  font-weight: 500;
  color: #212529;
}

.suggestion-name {
  font-size: 0.9rem;
}

.suggestion-details {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: 1.5rem;
}

.suggestion-area {
  font-size: 0.8rem;
  color: #6c757d;
  background: #e9ecef;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-weight: 500;
}

.suggestion-category {
  font-size: 0.75rem;
  color: #495057;
  background: #f8f9fa;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  border: 1px solid #dee2e6;
}

.input-group {
  position: relative;
}

.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875em;
  color: #dc3545;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .location-suggestions {
    position: fixed;
    left: 1rem;
    right: 1rem;
    top: auto;
    bottom: 1rem;
    border-radius: 0.375rem;
    max-height: 150px;
  }

  .btn-sm {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
  }
}

/* Animation for loading states */
@keyframes pulse {
  0% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }

  100% {
    opacity: 1;
  }
}

.btn:disabled .spinner-border {
  animation: pulse 1.5s ease-in-out infinite;
}

/* Popular routes styling */
.btn-outline-primary:hover {
  transform: translateY(-1px);
  transition: transform 0.2s ease;
}

/* Card hover effect */
.card {
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>