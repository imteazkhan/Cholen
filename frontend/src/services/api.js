import axios from 'axios'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // Laravel backend URL
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      window.location.href = '/'
    }
    return Promise.reject(error)
  }
)

// Auth API methods
export const authAPI = {
  // Register new user
  register: (userData) => api.post('/auth/register', userData),
  
  // Login user
  login: (credentials) => api.post('/auth/login', credentials),
  
  // Get current user
  getUser: () => api.get('/auth/user'),
  
  // Logout user
  logout: () => api.post('/auth/logout'),
  
  // Admin methods (using test endpoints temporarily)
  getAllUsers: () => api.get('/test-admin/users'),
  getAllDrivers: () => api.get('/test-admin/drivers'),
  createDriver: (driverData) => api.post('/test-admin/drivers', driverData),
  updateDriverStatus: (driverId, status) => api.put(`/test-admin/drivers/${driverId}/status`, { status }),
  deleteDriver: (driverId) => api.delete(`/test-admin/drivers/${driverId}`),
  deleteUser: (userId) => api.delete(`/test-admin/users/${userId}`),
  updateUser: (userId, userData) => api.put(`/test-admin/users/${userId}`, userData),
  getDashboardAnalytics: () => api.get('/test-admin/analytics'),
  getAllRides: () => api.get('/test-admin/rides'),
  updateRideStatus: (rideId, status) => api.put(`/test-admin/rides/${rideId}/status`, { status }),
  getSystemSettings: () => api.get('/test-admin/settings'),
  updateSystemSettings: (settings) => api.put('/test-admin/settings', settings),
}

// Ride API methods
export const rideAPI = {
  // User methods
  bookRide: (rideData) => api.post('/rides/book', rideData),
  getUserRides: () => api.get('/rides/my-rides'),
  
  // Driver methods
  getAvailableRides: () => api.get('/rides/available'),
  acceptRide: (rideId) => api.post(`/rides/${rideId}/accept`),
  getDriverRides: () => api.get('/rides/my-driver-rides'),
  
  // Common methods
  updateRideStatus: (rideId, status) => api.put(`/rides/${rideId}/status`, { status }),
  cancelRide: (rideId) => api.delete(`/rides/${rideId}/cancel`),
  processPayment: (rideId, paymentData) => api.post(`/rides/${rideId}/payment`, paymentData),
}

// General API methods
export const generalAPI = {
  // Test API connection
  test: () => api.get('/test'),
}

export default api