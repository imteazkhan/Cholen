<template>
  <div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header bg-gradient-primary text-white py-4 mb-4">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="d-flex align-items-center">
              <div class="avatar-circle me-3">
                {{ authStore.userInitials }}
              </div>
              <div>
                <h3 class="mb-1">Welcome back, {{ user.first_name }}!</h3>
                <p class="mb-0 opacity-75">
                  <i class="bi bi-person-badge me-2"></i>
                  {{ user.role?.charAt(0).toUpperCase() + user.role?.slice(1) }} Account
                  <span class="mx-2">•</span>
                  <i class="bi bi-calendar3 me-1"></i>
                  {{ getCurrentDate() }}
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="d-flex flex-column align-items-md-end">
              <div class="status-indicator mb-2">
                <span class="badge" :class="getAccountStatusBadge()">
                  <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
                  {{ getAccountStatus() }}
                </span>
              </div>
              <small class="opacity-75">Last login: {{ formatLastLogin() }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <!-- Quick Stats Row -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="quick-stats-container">
            <!-- User Stats -->
            <div v-if="authStore.isUser" class="row g-3">
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-primary">
                  <div class="stat-icon">
                    <i class="bi bi-car-front"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ userStats.totalRides }}</h4>
                    <p>Total Rides</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-success">
                  <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ userStats.completedRides }}</h4>
                    <p>Completed</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-info">
                  <div class="stat-icon">
                    <i class="bi bi-currency-bdt"><span>৳</span></i>
                  </div>
                  <div class="stat-content">
                    <h4>BDT {{ userStats.totalSpent }}</h4>
                    <p>Total Spent</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-warning">
                  <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ userStats.activeRides }}</h4>
                    <p>Active Rides</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Driver Stats -->
            <div v-else-if="authStore.isDriver" class="row g-3">
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-success">
                  <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                  </div>
                  <div class="stat-content">
                    <h4>BDT {{ driverStats.totalEarnings }}</h4>
                    <p>Total Earnings</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-primary">
                  <div class="stat-icon">
                    <i class="bi bi-car-front"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ driverStats.totalRides }}</h4>
                    <p>Total Rides</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card bg-info">
                  <div class="stat-icon">
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ driverStats.rating }}</h4>
                    <p>Rating</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="stat-card" :class="isOnline ? 'bg-success' : 'bg-secondary'">
                  <div class="stat-icon">
                    <i class="bi" :class="isOnline ? 'bi-wifi' : 'bi-wifi-off'"></i>
                  </div>
                  <div class="stat-content">
                    <h4>{{ isOnline ? 'Online' : 'Offline' }}</h4>
                    <p>Status</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Dashboard Content -->
      <div v-if="authStore.isUser" class="row g-4">
        <!-- Quick Actions -->
        <div class="col-12">
          <div class="action-cards-container mb-4">
            <div class="row g-3">
              <div class="col-md-4">
                <router-link to="/" class="action-card text-decoration-none">
                  <div class="action-icon bg-primary">
                    <i class="bi bi-plus-circle"></i>
                  </div>
                  <div class="action-content">
                    <h6>Book New Ride</h6>
                    <p>Quick and easy booking</p>
                  </div>
                  <i class="bi bi-arrow-right action-arrow"></i>
                </router-link>
              </div>
              <div class="col-md-4">
                <div class="action-card" @click="loadUserRides">
                  <div class="action-icon bg-info">
                    <i class="bi bi-arrow-clockwise"></i>
                  </div>
                  <div class="action-content">
                    <h6>Refresh Rides</h6>
                    <p>Update ride status</p>
                  </div>
                  <i class="bi bi-arrow-right action-arrow"></i>
                </div>
              </div>
              <div class="col-md-4">
                <div class="action-card">
                  <div class="action-icon bg-success">
                    <i class="bi bi-headset"></i>
                  </div>
                  <div class="action-content">
                    <h6>Support</h6>
                    <p>Get help & assistance</p>
                  </div>
                  <i class="bi bi-arrow-right action-arrow"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Map Section -->
        <div class="col-12 mb-4">
          <div class="professional-card">
            <div class="card-header-custom">
              <h5 class="card-title-custom">
                <i class="bi bi-geo-alt me-2"></i>
                Live Map
              </h5>
              <p class="card-subtitle-custom">Track your rides and nearby drivers</p>
            </div>
            <div class="card-body-custom">
              <MapComponent :initial-center="[23.79, 90.41]" :initial-zoom="13" :markers="mapMarkers" />
            </div>
          </div>
        </div>

        <!-- Active Rides -->
        <div class="col-md-8">
          <div class="professional-card">
            <div class="card-header-custom">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="card-title-custom">
                    <i class="bi bi-car-front me-2"></i>
                    My Rides
                  </h5>
                  <p class="card-subtitle-custom">Track your ride history and current bookings</p>
                </div>
                <div class="header-actions">
                  <button class="btn btn-outline-primary btn-sm" @click="loadUserRides" :disabled="loadingRides">
                    <span v-if="loadingRides" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-arrow-clockwise me-1"></i>
                    Refresh
                  </button>
                </div>
              </div>
            </div>

            <div class="card-body-custom">
              <div v-if="loadingRides" class="loading-state">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Loading your rides...</p>
              </div>

              <div v-else-if="userRides.length === 0" class="empty-state">
                <div class="empty-icon">
                  <i class="bi bi-car-front"></i>
                </div>
                <h6>No rides yet</h6>
                <p>Book your first ride to get started!</p>
                <router-link to="/" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>
                  Book Your First Ride
                </router-link>
              </div>

              <div v-else class="rides-list">
                <div v-for="ride in userRides.slice(0, 5)" :key="ride.id" class="ride-item">
                  <div class="ride-status-indicator" :class="getStatusIndicatorClass(ride.status)"></div>
                  <div class="ride-content">
                    <div class="ride-header">
                      <div class="ride-locations">
                        <div class="location-item">
                          <i class="bi bi-record-circle text-success"></i>
                          <span class="location-text">{{ ride.pickup_location }}</span>
                        </div>
                        <div class="location-divider">
                          <i class="bi bi-three-dots-vertical text-muted"></i>
                        </div>
                        <div class="location-item">
                          <i class="bi bi-geo-alt text-danger"></i>
                          <span class="location-text">{{ ride.dropoff_location }}</span>
                        </div>
                      </div>
                      <div class="ride-meta">
                        <span class="badge status-badge" :class="getStatusBadgeClass(ride.status)">
                          {{ getStatusText(ride.status) }}
                        </span>
                        <div class="ride-price">BDT {{ ride.final_price || ride.estimated_price }}</div>
                      </div>
                    </div>
                    <div class="ride-details">
                      <div class="detail-item">
                        <i class="bi bi-calendar3 me-1"></i>
                        <small>{{ formatDate(ride.scheduled_at) }}</small>
                      </div>
                      <div class="detail-item">
                        <i class="bi bi-geo me-1"></i>
                        <small>{{ ride.distance_km }} km</small>
                      </div>
                      <div class="detail-item" v-if="ride.driver">
                        <i class="bi bi-person me-1"></i>
                        <small>{{ ride.driver.name }}</small>
                      </div>
                    </div>
                    <div class="ride-actions" v-if="canCancelRide(ride)">
                      <button class="btn btn-outline-danger btn-sm" @click="cancelRide(ride.id)"
                        :disabled="cancellingRide === ride.id">
                        <span v-if="cancellingRide === ride.id" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-x-circle me-1"></i>
                        Cancel Ride
                      </button>
                    </div>
                  </div>
                </div>

                <div v-if="userRides.length > 5" class="view-all-rides">
                  <button class="btn btn-outline-primary w-100" @click="showAllRides = !showAllRides">
                    <i class="bi bi-eye me-2"></i>
                    {{ showAllRides ? 'Show Less' : `View All ${userRides.length} Rides` }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile & Activity Sidebar -->
        <div class="col-md-4">
          <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-12">
              <div class="professional-card">
                <div class="card-header-custom">
                  <h6 class="card-title-custom">
                    <i class="bi bi-person-circle me-2"></i>
                    Profile Summary
                  </h6>
                </div>
                <div class="card-body-custom">
                  <div class="profile-info">
                    <div class="profile-item">
                      <span class="profile-label">Name</span>
                      <span class="profile-value">{{ user.first_name }} {{ user.last_name }}</span>
                    </div>
                    <div class="profile-item">
                      <span class="profile-label">Email</span>
                      <span class="profile-value">{{ user.email }}</span>
                    </div>
                    <div class="profile-item">
                      <span class="profile-label">Member Since</span>
                      <span class="profile-value">{{ formatMemberSince() }}</span>
                    </div>
                    <div class="profile-item">
                      <span class="profile-label">Account Status</span>
                      <span class="badge bg-success">Active</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-12">
              <div class="professional-card">
                <div class="card-header-custom">
                  <h6 class="card-title-custom">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Activity
                  </h6>
                </div>
                <div class="card-body-custom">
                  <div class="activity-list">
                    <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
                      <div class="activity-icon" :class="activity.iconClass">
                        <i :class="activity.icon"></i>
                      </div>
                      <div class="activity-content">
                        <p class="activity-text">{{ activity.text }}</p>
                        <small class="activity-time">{{ activity.time }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Driver Dashboard Content -->
      <div v-else-if="authStore.isDriver" class="row g-4">
        <!-- Driver Status Card -->
        <div class="col-12">
          <div class="professional-card">
            <div class="card-header-custom">
              <h5 class="card-title-custom">
                <i class="bi bi-car-front me-2"></i>
                Driver Control Panel
              </h5>
            </div>
            <div class="card-body-custom">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <div class="driver-info">
                    <p class="mb-2"><strong>License:</strong> {{ user.driver_license }}</p>
                    <span class="badge" :class="user.driver_status === 'approved' ? 'bg-success' : 'bg-warning'">
                      {{ user.driver_status === 'approved' ? 'Approved Driver' : 'Pending Approval' }}
                    </span>
                  </div>
                </div>
                <div class="col-md-4 text-md-end">
                  <button v-if="user.driver_status === 'approved'" class="btn"
                    :class="isOnline ? 'btn-danger' : 'btn-success'" @click="toggleOnlineStatus"
                    :disabled="togglingStatus">
                    <span v-if="togglingStatus" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi" :class="isOnline ? 'bi-wifi-off' : 'bi-wifi'"></i>
                    {{ isOnline ? 'Go Offline' : 'Go Online' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Available Rides -->
        <div class="col-md-6">
          <div class="professional-card">
            <div class="card-header-custom">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title-custom">
                  <i class="bi bi-search me-2"></i>
                  Available Rides
                </h5>
                <button class="btn btn-outline-primary btn-sm" @click="loadAvailableRides"
                  :disabled="loadingAvailableRides">
                  <span v-if="loadingAvailableRides" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-arrow-clockwise me-1"></i>
                  Refresh
                </button>
              </div>
            </div>
            <div class="card-body-custom">
              <div v-if="loadingAvailableRides" class="loading-state">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading available rides...</p>
              </div>

              <div v-else-if="availableRides.length === 0" class="empty-state">
                <div class="empty-icon">
                  <i class="bi bi-search"></i>
                </div>
                <h6>No rides available</h6>
                <p>Check back later for new ride requests</p>
              </div>

              <div v-else class="rides-list" style="max-height: 400px; overflow-y: auto;">
                <div v-for="ride in availableRides" :key="ride.id" class="ride-item">
                  <div class="ride-content">
                    <div class="ride-header">
                      <div class="ride-locations">
                        <div class="location-item">
                          <i class="bi bi-geo-alt text-success"></i>
                          <span class="location-text">{{ ride.pickup_location }}</span>
                        </div>
                        <div class="location-item">
                          <i class="bi bi-pin-map text-danger"></i>
                          <span class="location-text">{{ ride.dropoff_location }}</span>
                        </div>
                      </div>
                      <div class="ride-meta">
                        <div class="ride-price">BDT {{ ride.estimated_price }}</div>
                        <small class="text-muted">{{ ride.distance_km }} km</small>
                      </div>
                    </div>
                    <div class="ride-details">
                      <div class="detail-item">
                        <i class="bi bi-clock me-1"></i>
                        <small>{{ formatDate(ride.scheduled_at) }}</small>
                      </div>
                      <div class="detail-item">
                        <i class="bi bi-people me-1"></i>
                        <small>{{ ride.passenger_count }} passenger(s)</small>
                      </div>
                    </div>
                    <div class="ride-actions">
                      <button class="btn btn-primary btn-sm" @click="acceptRide(ride.id)"
                        :disabled="acceptingRide === ride.id">
                        <span v-if="acceptingRide === ride.id" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="bi bi-check-circle me-1"></i>
                        Accept Ride
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- My Rides -->
        <div class="col-md-6">
          <div class="professional-card">
            <div class="card-header-custom">
              <h5 class="card-title-custom">
                <i class="bi bi-car-front me-2"></i>
                My Rides
              </h5>
            </div>
            <div class="card-body-custom">
              <div v-if="loadingDriverRides" class="loading-state">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading your rides...</p>
              </div>

              <div v-else-if="driverRides.length === 0" class="empty-state">
                <div class="empty-icon">
                  <i class="bi bi-car-front"></i>
                </div>
                <h6>No rides yet</h6>
                <p>Accept rides to start earning!</p>
              </div>

              <div v-else class="rides-list" style="max-height: 400px; overflow-y: auto;">
                <div v-for="ride in driverRides" :key="ride.id" class="ride-item">
                  <div class="ride-status-indicator" :class="getStatusIndicatorClass(ride.status)"></div>
                  <div class="ride-content">
                    <div class="ride-header">
                      <div class="ride-locations">
                        <div class="location-item">
                          <i class="bi bi-person me-1"></i>
                          <span class="location-text">{{ ride.user.name }}</span>
                        </div>
                        <div class="location-item">
                          <i class="bi bi-geo-alt text-success"></i>
                          <span class="location-text">{{ ride.pickup_location }}</span>
                        </div>
                        <div class="location-item">
                          <i class="bi bi-pin-map text-danger"></i>
                          <span class="location-text">{{ ride.dropoff_location }}</span>
                        </div>
                      </div>
                      <div class="ride-meta">
                        <span class="badge status-badge" :class="getStatusBadgeClass(ride.status)">
                          {{ getStatusText(ride.status) }}
                        </span>
                        <div class="ride-price">BDT {{ ride.final_price || ride.estimated_price }}</div>
                      </div>
                    </div>

                    <!-- Status Update Buttons -->
                    <div class="ride-actions mt-2">
                      <div v-if="ride.status === 'accepted'">
                        <button class="btn btn-info btn-sm" @click="updateRideStatus(ride.id, 'driver_arrived')"
                          :disabled="updatingStatus === ride.id">
                          <span v-if="updatingStatus === ride.id" class="spinner-border spinner-border-sm me-1"></span>
                          <i v-else class="bi bi-geo-alt me-1"></i>
                          I've Arrived
                        </button>
                      </div>
                      <div v-else-if="ride.status === 'driver_arrived'">
                        <button class="btn btn-success btn-sm" @click="updateRideStatus(ride.id, 'in_progress')"
                          :disabled="updatingStatus === ride.id">
                          <span v-if="updatingStatus === ride.id" class="spinner-border spinner-border-sm me-1"></span>
                          <i v-else class="bi bi-play-circle me-1"></i>
                          Start Ride
                        </button>
                      </div>
                      <div v-else-if="ride.status === 'in_progress'">
                        <button class="btn btn-success btn-sm" @click="updateRideStatus(ride.id, 'completed')"
                          :disabled="updatingStatus === ride.id">
                          <span v-if="updatingStatus === ride.id" class="spinner-border spinner-border-sm me-1"></span>
                          <i v-else class="bi bi-check-circle me-1"></i>
                          Complete Ride
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Dashboard Content -->
      <div v-else-if="authStore.isAdmin" class="row g-4">
        <div class="col-12">
          <div class="professional-card">
            <div class="card-header-custom">
              <h5 class="card-title-custom">
                <i class="bi bi-gear me-2"></i>
                Admin Dashboard
              </h5>
            </div>
            <div class="card-body-custom">
              <p>Welcome to the admin dashboard! Here you can:</p>
              <ul>
                <li>Manage users and drivers</li>
                <li>View system analytics</li>
                <li>Monitor ride requests</li>
                <li>Configure system settings</li>
              </ul>
              <div class="row g-3 mt-3">
                <div class="col-md-4">
                  <div class="stat-card bg-primary">
                    <div class="stat-icon">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                      <h4>150</h4>
                      <p>Total Users</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="stat-card bg-success">
                    <div class="stat-icon">
                      <i class="bi bi-car-front"></i>
                    </div>
                    <div class="stat-content">
                      <h4>45</h4>
                      <p>Active Drivers</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="stat-card bg-info">
                    <div class="stat-icon">
                      <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-content">
                      <h4>89</h4>
                      <p>Rides Today</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <router-link to="/admin" class="btn btn-primary">
                  <i class="bi bi-gear me-2"></i>
                  Go to Admin Panel
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { rideAPI } from '../services/api'
import MapComponent from '../components/MapComponent.vue'

const router = useRouter()
const authStore = useAuthStore()
const { appContext } = getCurrentInstance()

// Redirect if not authenticated
if (!authStore.isAuthenticated) {
  router.push('/')
}

// Reactive data
const userRides = ref([])
const driverRides = ref([])
const availableRides = ref([])
const loadingRides = ref(false)
const loadingDriverRides = ref(false)
const loadingAvailableRides = ref(false)
const cancellingRide = ref(null)
const acceptingRide = ref(null)
const updatingStatus = ref(null)
const isOnline = ref(false)
const togglingStatus = ref(false)
const showAllRides = ref(false)

// Map markers
const mapMarkers = computed(() => {
  const markers = []

  // Add user's current rides as markers
  if (authStore.isUser) {
    userRides.value.forEach(ride => {
      if (ride.pickup_lat && ride.pickup_lng) {
        markers.push({
          id: `pickup-${ride.id}`,
          position: [ride.pickup_lat, ride.pickup_lng],
          popup: `Pickup: ${ride.pickup_location}`
        })
      }
      if (ride.dropoff_lat && ride.dropoff_lng) {
        markers.push({
          id: `dropoff-${ride.id}`,
          position: [ride.dropoff_lat, ride.dropoff_lng],
          popup: `Dropoff: ${ride.dropoff_location}`
        })
      }
    })
  }

  // Add default Dhaka marker if no rides
  if (markers.length === 0) {
    markers.push({
      id: 'default',
      position: [23.79, 90.41],
      popup: 'Mirpur, Dhaka - Your Location'
    })
  }

  return markers
})

const user = computed(() => authStore.user || {})

// Computed statistics
const userStats = computed(() => {
  const rides = userRides.value
  return {
    totalRides: rides.length,
    completedRides: rides.filter(r => r.status === 'completed').length,
    activeRides: rides.filter(r => ['pending', 'accepted', 'driver_arrived', 'in_progress'].includes(r.status)).length,
    totalSpent: rides.filter(r => r.final_price).reduce((sum, r) => sum + parseFloat(r.final_price), 0).toFixed(0)
  }
})

const driverStats = computed(() => {
  const rides = driverRides.value
  return {
    totalRides: rides.length,
    totalEarnings: rides.filter(r => r.final_price).reduce((sum, r) => sum + parseFloat(r.final_price), 0).toFixed(0),
    rating: '4.8', // Mock rating
    activeRides: rides.filter(r => ['accepted', 'driver_arrived', 'in_progress'].includes(r.status)).length
  }
})

const recentActivity = computed(() => {
  const activities = []

  if (authStore.isUser) {
    userRides.value.slice(0, 3).forEach(ride => {
      activities.push({
        id: `ride-${ride.id}`,
        icon: 'bi bi-car-front',
        iconClass: getActivityIconClass(ride.status),
        text: `Ride to ${ride.dropoff_location}`,
        time: formatRelativeTime(ride.created_at)
      })
    })
  } else if (authStore.isDriver) {
    driverRides.value.slice(0, 3).forEach(ride => {
      activities.push({
        id: `drive-${ride.id}`,
        icon: 'bi bi-steering-wheel',
        iconClass: getActivityIconClass(ride.status),
        text: `Drive from ${ride.pickup_location}`,
        time: formatRelativeTime(ride.created_at)
      })
    })
  }

  return activities
})

// Load user rides
const loadUserRides = async () => {
  if (!authStore.isUser) return

  loadingRides.value = true
  try {
    const response = await rideAPI.getUserRides()
    if (response.data.success) {
      userRides.value = response.data.rides
    }
  } catch (error) {
    console.error('Failed to load user rides:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load your rides')
  } finally {
    loadingRides.value = false
  }
}

// Load driver rides
const loadDriverRides = async () => {
  if (!authStore.isDriver) return

  loadingDriverRides.value = true
  try {
    const response = await rideAPI.getDriverRides()
    if (response.data.success) {
      driverRides.value = response.data.rides
    }
  } catch (error) {
    console.error('Failed to load driver rides:', error)
    appContext.config.globalProperties.$toast?.error('Failed to load your rides')
  } finally {
    loadingDriverRides.value = false
  }
}

// Load available rides for drivers
const loadAvailableRides = async () => {
  if (!authStore.isDriver) return

  loadingAvailableRides.value = true
  try {
    const response = await rideAPI.getAvailableRides()
    if (response.data.success) {
      availableRides.value = response.data.rides
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to load available rides'
    if (error.response?.data?.active_ride) {
      // Driver has an active ride
      appContext.config.globalProperties.$toast?.info(message)
    } else {
      appContext.config.globalProperties.$toast?.error(message)
    }
    availableRides.value = []
  } finally {
    loadingAvailableRides.value = false
  }
}

// Accept a ride
const acceptRide = async (rideId) => {
  acceptingRide.value = rideId
  try {
    const response = await rideAPI.acceptRide(rideId)
    if (response.data.success) {
      appContext.config.globalProperties.$toast?.success('Ride accepted successfully!')
      // Refresh both lists
      await Promise.all([loadAvailableRides(), loadDriverRides()])
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to accept ride'
    appContext.config.globalProperties.$toast?.error(message)
  } finally {
    acceptingRide.value = null
  }
}

// Update ride status
const updateRideStatus = async (rideId, status) => {
  updatingStatus.value = rideId
  try {
    const response = await rideAPI.updateRideStatus(rideId, status)
    if (response.data.success) {
      const statusMessages = {
        'driver_arrived': 'Marked as arrived at pickup location',
        'in_progress': 'Ride started successfully',
        'completed': 'Ride completed successfully!'
      }
      appContext.config.globalProperties.$toast?.success(statusMessages[status] || 'Status updated')

      // Refresh rides
      if (authStore.isDriver) {
        await loadDriverRides()
      } else {
        await loadUserRides()
      }
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to update ride status'
    appContext.config.globalProperties.$toast?.error(message)
  } finally {
    updatingStatus.value = null
  }
}

// Cancel a ride
const cancelRide = async (rideId) => {
  if (!confirm('Are you sure you want to cancel this ride?')) return

  cancellingRide.value = rideId
  try {
    const response = await rideAPI.cancelRide(rideId)
    if (response.data.success) {
      appContext.config.globalProperties.$toast?.success('Ride cancelled successfully')

      // Refresh rides
      if (authStore.isUser) {
        await loadUserRides()
      } else if (authStore.isDriver) {
        await Promise.all([loadDriverRides(), loadAvailableRides()])
      }
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Failed to cancel ride'
    appContext.config.globalProperties.$toast?.error(message)
  } finally {
    cancellingRide.value = null
  }
}

// Toggle online status (mock implementation)
const toggleOnlineStatus = () => {
  togglingStatus.value = true
  setTimeout(() => {
    isOnline.value = !isOnline.value
    const status = isOnline.value ? 'online' : 'offline'
    appContext.config.globalProperties.$toast?.success(`You are now ${status}`)

    if (isOnline.value) {
      loadAvailableRides()
    }
    togglingStatus.value = false
  }, 1000)
}

// Helper functions
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatRelativeTime = (dateString) => {
  const now = new Date()
  const date = new Date(dateString)
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))

  if (diffInHours < 1) return 'Just now'
  if (diffInHours < 24) return `${diffInHours}h ago`
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays}d ago`
  return formatDate(dateString)
}

const getCurrentDate = () => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatLastLogin = () => {
  if (user.value.last_login_at) {
    return formatRelativeTime(user.value.last_login_at)
  }
  return 'First time login'
}

const formatMemberSince = () => {
  if (user.value.created_at) {
    return new Date(user.value.created_at).toLocaleDateString('en-US', {
      month: 'long',
      year: 'numeric'
    })
  }
  return 'Recently joined'
}

const getAccountStatus = () => {
  if (authStore.isDriver) {
    return user.value.driver_status === 'approved' ? 'Approved Driver' : 'Pending Approval'
  }
  return user.value.is_active ? 'Active Account' : 'Inactive Account'
}

const getAccountStatusBadge = () => {
  if (authStore.isDriver) {
    return user.value.driver_status === 'approved' ? 'bg-success' : 'bg-warning'
  }
  return user.value.is_active ? 'bg-success' : 'bg-danger'
}

const getActivityIconClass = (status) => {
  const classes = {
    'completed': 'bg-success',
    'in_progress': 'bg-primary',
    'accepted': 'bg-info',
    'pending': 'bg-warning',
    'cancelled': 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getStatusIndicatorClass = (status) => {
  const classes = {
    'pending': 'status-pending',
    'accepted': 'status-accepted',
    'driver_arrived': 'status-arrived',
    'in_progress': 'status-progress',
    'completed': 'status-completed',
    'cancelled': 'status-cancelled'
  }
  return classes[status] || 'status-default'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'pending': 'bg-warning text-dark',
    'accepted': 'bg-info',
    'driver_arrived': 'bg-primary',
    'in_progress': 'bg-success',
    'completed': 'bg-success',
    'cancelled': 'bg-danger',
    'expired': 'bg-secondary'
  }
  return classes[status] || 'bg-secondary'
}

const getStatusBorderClass = (status) => {
  const classes = {
    'pending': 'border-warning',
    'accepted': 'border-info',
    'driver_arrived': 'border-primary',
    'in_progress': 'border-success',
    'completed': 'border-success',
    'cancelled': 'border-danger',
    'expired': 'border-secondary'
  }
  return classes[status] || 'border-secondary'
}

const getStatusText = (status) => {
  const texts = {
    'pending': 'Looking for driver',
    'accepted': 'Driver assigned',
    'driver_arrived': 'Driver arrived',
    'in_progress': 'In progress',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
    'expired': 'Expired'
  }
  return texts[status] || 'Unknown'
}

const canCancelRide = (ride) => {
  return ['pending', 'accepted'].includes(ride.status)
}

// Load data on mount
onMounted(() => {
  if (authStore.isUser) {
    loadUserRides()
  } else if (authStore.isDriver) {
    loadDriverRides()
    if (user.value.driver_status === 'approved') {
      loadAvailableRides()
    }
  }
})
</script>

<style scoped>
/* Dashboard Container */
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Header Styles */
.dashboard-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  border: 3px solid rgba(255, 255, 255, 0.3);
}

.status-indicator .badge {
  font-size: 0.75rem;
  padding: 0.5rem 1rem;
}

/* Quick Stats */
.quick-stats-container {
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: none;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  color: white;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  transform: translate(30px, -30px);
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  font-size: 2.5rem;
  opacity: 0.9;
}

.stat-content h4 {
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
  line-height: 1;
}

.stat-content p {
  margin: 0;
  opacity: 0.9;
  font-size: 0.9rem;
}

/* Action Cards */
.action-cards-container {
  margin-bottom: 2rem;
}

.action-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
  border: none;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  position: relative;
}

.action-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  color: inherit;
  text-decoration: none;
}

.action-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.action-content h6 {
  margin: 0;
  font-weight: 600;
  color: #2d3748;
}

.action-content p {
  margin: 0;
  color: #718096;
  font-size: 0.875rem;
}

.action-arrow {
  margin-left: auto;
  color: #cbd5e0;
  transition: all 0.3s ease;
}

.action-card:hover .action-arrow {
  color: #4299e1;
  transform: translateX(5px);
}

/* Professional Cards */
.professional-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: none;
  overflow: hidden;
  transition: all 0.3s ease;
}

.professional-card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.card-header-custom {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-bottom: 1px solid #e2e8f0;
  padding: 1.5rem;
}

.card-title-custom {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  display: flex;
  align-items: center;
}

.card-subtitle-custom {
  margin: 0.5rem 0 0 0;
  color: #718096;
  font-size: 0.875rem;
}

.card-body-custom {
  padding: 1.5rem;
}

.header-actions .btn {
  border-radius: 8px;
  font-weight: 500;
}

/* Loading and Empty States */
.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  color: #cbd5e0;
  margin-bottom: 1rem;
}

.empty-state h6 {
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #718096;
  margin-bottom: 1.5rem;
}

/* Rides List */
.rides-list {
  max-height: 600px;
  overflow-y: auto;
}

.ride-item {
  background: #f8fafc;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border-left: 4px solid #e2e8f0;
  position: relative;
  transition: all 0.3s ease;
}

.ride-item:hover {
  background: #f1f5f9;
  transform: translateX(5px);
}

.ride-status-indicator {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  border-radius: 0 4px 4px 0;
}

.status-pending {
  background: #f59e0b;
}

.status-accepted {
  background: #3b82f6;
}

.status-arrived {
  background: #8b5cf6;
}

.status-progress {
  background: #10b981;
}

.status-completed {
  background: #059669;
}

.status-cancelled {
  background: #ef4444;
}

.ride-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.ride-locations {
  flex: 1;
}

.location-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.location-text {
  font-weight: 500;
  color: #2d3748;
}

.location-divider {
  margin-left: 0.5rem;
  opacity: 0.5;
}

.ride-meta {
  text-align: right;
}

.status-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  margin-bottom: 0.5rem;
}

.ride-price {
  font-size: 1.25rem;
  font-weight: bold;
  color: #059669;
}

.ride-details {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.detail-item {
  display: flex;
  align-items: center;
  color: #718096;
  font-size: 0.875rem;
}

.ride-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
}

/* Profile Info */
.profile-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.profile-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.profile-item:last-child {
  border-bottom: none;
}

.profile-label {
  font-weight: 500;
  color: #4a5568;
}

.profile-value {
  color: #2d3748;
  font-weight: 500;
}

/* Activity List */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-text {
  margin: 0;
  font-weight: 500;
  color: #2d3748;
  font-size: 0.875rem;
}

.activity-time {
  color: #718096;
  font-size: 0.75rem;
}

/* View All Rides */
.view-all-rides {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 2rem 0;
  }

  .avatar-circle {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }

  .stat-card {
    padding: 1rem;
  }

  .stat-content h4 {
    font-size: 1.5rem;
  }

  .action-card {
    padding: 1rem;
  }

  .ride-header {
    flex-direction: column;
    gap: 1rem;
  }

  .ride-meta {
    text-align: left;
  }

  .ride-details {
    flex-direction: column;
    gap: 0.5rem;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.professional-card {
  animation: fadeInUp 0.6s ease-out;
}

.stat-card {
  animation: fadeInUp 0.6s ease-out;
}

.action-card {
  animation: fadeInUp 0.6s ease-out;
}

/* Custom Scrollbar */
.rides-list::-webkit-scrollbar {
  width: 6px;
}

.rides-list::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.rides-list::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.rides-list::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>