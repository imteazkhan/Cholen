<template>
  <div v-if="showModal" class="custom-modal-overlay" @click="closeModal">
    <div class="custom-modal-dialog" @click.stop>
      <div class="custom-modal-content">
        <div class="custom-modal-header">
          <h5 class="custom-modal-title">
            <i class="bi bi-credit-card me-2"></i>
            Payment for Ride
          </h5>
          <button type="button" class="custom-btn-close" @click="closeModal" :disabled="processing">×</button>
        </div>

        <div class="custom-modal-body">
          <!-- Ride Details -->
          <div class="card mb-4" v-if="ride">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-car-front me-2"></i>Ride Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong>From:</strong> {{ ride.pickup_location }}
                  </div>
                  <div class="mb-2">
                    <strong>To:</strong> {{ ride.dropoff_location }}
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-2">
                    <strong>Distance:</strong> {{ ride.distance_km }} km
                  </div>
                  <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-info">{{ ride.status }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Form -->
          <form @submit.prevent="processPayment">
            <div class="row g-3">
              <!-- Amount -->
              <div class="col-md-6">
                <label class="form-label">Amount to Pay</label>
                <div class="input-group">
                  <span class="input-group-text">BDT</span>
                  <input type="number" class="form-control" v-model.number="paymentForm.amount"
                    :class="{ 'is-invalid': errors.amount }" min="1" step="0.01" readonly>
                </div>
                <div v-if="errors.amount" class="invalid-feedback">{{ errors.amount }}</div>
              </div>

              <!-- Payment Method -->
              <div class="col-md-6">
                <label class="form-label">Payment Method *</label>
                <select class="form-select" v-model="paymentForm.method" :class="{ 'is-invalid': errors.method }">
                  <option value="">Select Payment Method</option>
                  <option value="cash">Cash Payment</option>
                  <option value="mobile">Mobile Banking (bKash, Nagad, Rocket)</option>
                </select>
                <div v-if="errors.method" class="invalid-feedback">{{ errors.method }}</div>
              </div>

              <!-- Customer Details (Only for Mobile Payment) -->
              <template v-if="paymentForm.method === 'mobile'">
                <!-- Customer Name -->
                <div class="col-md-6">
                  <label class="form-label">Full Name *</label>
                  <input type="text" class="form-control" v-model="paymentForm.customer_name"
                    :class="{ 'is-invalid': errors.customer_name }" placeholder="Enter your full name" required>
                  <div v-if="errors.customer_name" class="invalid-feedback">{{ errors.customer_name }}</div>
                </div>

                <!-- Customer Email -->
                <div class="col-md-6">
                  <label class="form-label">Email Address *</label>
                  <input type="email" class="form-control" v-model="paymentForm.customer_email"
                    :class="{ 'is-invalid': errors.customer_email }" placeholder="Enter your email" required>
                  <div v-if="errors.customer_email" class="invalid-feedback">{{ errors.customer_email }}</div>
                </div>

                <!-- Customer Phone -->
                <div class="col-md-12">
                  <label class="form-label">Phone Number *</label>
                  <input type="tel" class="form-control" v-model="paymentForm.customer_phone"
                    :class="{ 'is-invalid': errors.customer_phone }"
                    placeholder="Enter your phone number (e.g., 01712345678)" required>
                  <div v-if="errors.customer_phone" class="invalid-feedback">{{ errors.customer_phone }}</div>
                </div>
              </template>

              <!-- Cash Payment Note -->
              <div v-if="paymentForm.method === 'cash'" class="col-md-12">
                <div class="alert alert-info">
                  <h6><i class="bi bi-cash me-2"></i>Cash Payment</h6>
                  <p class="mb-0">You will pay the driver directly in cash when the ride is completed. Please have the
                    exact amount
                    ready.</p>
                </div>
              </div>
            </div>

            <!-- Mobile Payment Methods Info -->
            <div v-if="paymentForm.method === 'mobile'" class="alert alert-info mt-4">
              <h6><i class="bi bi-info-circle me-2"></i>Available Mobile Payment Methods</h6>
              <div class="row g-2">
                <div class="col-md-3">
                  <div class="payment-method-card">
                    <i class="bi bi-phone text-success"></i>
                    <small>bKash</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="payment-method-card">
                    <i class="bi bi-wallet text-primary"></i>
                    <small>Nagad</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="payment-method-card">
                    <i class="bi bi-credit-card text-warning"></i>
                    <small>Rocket</small>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="payment-method-card">
                    <i class="bi bi-bank text-info"></i>
                    <small>Bank Cards</small>
                  </div>
                </div>
              </div>
              <small class="text-muted mt-2 d-block">
                You'll be redirected to SSLCommerz secure payment gateway to complete your payment.
              </small>
            </div>
          </form>
        </div>

        <div class="custom-modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="processing">
            Cancel
          </button>
          <button type="button" class="btn btn-primary" @click="processPayment" :disabled="processing || !isFormValid">
            <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else-if="paymentForm.method === 'cash'" class="bi bi-cash me-2"></i>
            <i v-else class="bi bi-shield-check me-2"></i>
            {{ getPaymentButtonText() }}
          </button>
        </div>
      </div>
    </div>
  </div>


</template>

<script setup>
import { ref, computed, watch, getCurrentInstance } from 'vue'
import { paymentAPI } from '../services/payment'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  ride: {
    type: Object,
    default: () => null
  }
})

const emit = defineEmits(['close', 'payment-success', 'payment-failed'])
const { appContext } = getCurrentInstance()

// Reactive data
const showModal = ref(false)
const processing = ref(false)
const errors = ref({})

const paymentForm = ref({
  amount: 0,
  method: '',
  customer_name: '',
  customer_email: '',
  customer_phone: ''
})

// Watch for show prop changes
watch(() => props.show, (newValue) => {
  showModal.value = newValue
  if (newValue && props.ride) {
    // Initialize form with ride data
    paymentForm.value.amount = props.ride.estimated_price || props.ride.final_price || 0

    // Pre-fill user data if available
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    if (user) {
      paymentForm.value.customer_name = `${user.first_name || ''} ${user.last_name || ''}`.trim()
      paymentForm.value.customer_email = user.email || ''
      paymentForm.value.customer_phone = user.phone || ''
    }
  }
})

// Computed properties
const isFormValid = computed(() => {
  const baseValid = paymentForm.value.amount > 0 && paymentForm.value.method !== ''

  if (paymentForm.value.method === 'cash') {
    return baseValid
  } else if (paymentForm.value.method === 'mobile') {
    return baseValid &&
      paymentForm.value.customer_name.trim() !== '' &&
      paymentForm.value.customer_email.includes('@') &&
      paymentForm.value.customer_phone.length >= 10
  }

  return false
})

// Methods
const closeModal = () => {
  if (!processing.value) {
    showModal.value = false
    emit('close')
    resetForm()
  }
}

const resetForm = () => {
  errors.value = {}
  paymentForm.value = {
    amount: 0,
    method: '',
    customer_name: '',
    customer_email: '',
    customer_phone: ''
  }
}

const validateForm = () => {
  errors.value = {}

  if (!paymentForm.value.amount || paymentForm.value.amount <= 0) {
    errors.value.amount = 'Valid amount is required'
  }

  if (!paymentForm.value.method) {
    errors.value.method = 'Please select a payment method'
  }

  // Only validate customer details for mobile payments
  if (paymentForm.value.method === 'mobile') {
    if (!paymentForm.value.customer_name.trim()) {
      errors.value.customer_name = 'Full name is required'
    }

    if (!paymentForm.value.customer_email || !paymentForm.value.customer_email.includes('@')) {
      errors.value.customer_email = 'Valid email address is required'
    }

    if (!paymentForm.value.customer_phone || paymentForm.value.customer_phone.length < 10) {
      errors.value.customer_phone = 'Valid phone number is required (minimum 10 digits)'
    }
  }

  return Object.keys(errors.value).length === 0
}

const processPayment = async () => {
  if (!props.ride) {
    appContext.config.globalProperties.$toast?.error('No ride selected for payment')
    return
  }

  if (!validateForm()) {
    appContext.config.globalProperties.$toast?.error('Please fix the form errors')
    return
  }

  processing.value = true

  try {
    if (paymentForm.value.method === 'cash') {
      // Handle cash payment
      appContext.config.globalProperties.$toast?.info('Processing cash payment...')

      const cashPaymentData = {
        ride_id: props.ride.id,
        amount: paymentForm.value.amount
      }

      const result = await paymentAPI.processCashPayment(cashPaymentData)

      if (result.success) {
        appContext.config.globalProperties.$toast?.success('Cash payment confirmed!')
        emit('payment-success', result.payment)
        closeModal()
      } else {
        throw new Error(result.message)
      }

    } else if (paymentForm.value.method === 'mobile') {
      // Handle mobile payment through SSLCommerz
      const paymentData = {
        ride_id: props.ride.id,
        amount: paymentForm.value.amount,
        customer_name: paymentForm.value.customer_name,
        customer_email: paymentForm.value.customer_email,
        customer_phone: paymentForm.value.customer_phone
      }

      appContext.config.globalProperties.$toast?.info('Initializing mobile payment...')

      const result = await paymentAPI.processMobilePayment(paymentData)

      if (result.success) {
        appContext.config.globalProperties.$toast?.success('Redirecting to payment gateway...')

        // Open payment gateway
        try {
          const paymentResult = await paymentAPI.openPaymentGateway(
            result.paymentUrl,
            result.transactionId
          )

          if (paymentResult.success) {
            appContext.config.globalProperties.$toast?.success('Payment completed successfully!')
            emit('payment-success', paymentResult.payment)
            closeModal()
          } else {
            throw new Error(paymentResult.message)
          }
        } catch (paymentError) {
          console.error('Payment gateway error:', paymentError)
          appContext.config.globalProperties.$toast?.error(paymentError.message || 'Payment failed')
          emit('payment-failed', paymentError)
        }
      } else {
        throw new Error(result.message)
      }
    }
  } catch (error) {
    console.error('Payment processing error:', error)
    appContext.config.globalProperties.$toast?.error(error.message || 'Payment processing failed')
    emit('payment-failed', error)
  } finally {
    processing.value = false
  }
}

// Get dynamic button text based on payment method
const getPaymentButtonText = () => {
  if (processing.value) {
    return 'Processing...'
  } else if (paymentForm.value.method === 'cash') {
    return 'Confirm Cash Payment'
  } else if (paymentForm.value.method === 'mobile') {
    return 'Pay with Mobile Banking'
  } else {
    return 'Select Payment Method'
  }
}
</script>

<style scoped>
/* Custom Modal Styles */
.custom-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.custom-modal-dialog {
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.custom-modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.custom-modal-header {
  padding: 20px;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.custom-modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
}

.custom-btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6c757d;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.custom-btn-close:hover {
  background-color: #e9ecef;
  color: #495057;
}

.custom-modal-body {
  padding: 20px;
  max-height: 60vh;
  overflow-y: auto;
}

.custom-modal-footer {
  padding: 20px;
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.payment-method-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 0.75rem;
  text-align: center;
  transition: all 0.2s ease;
}

.payment-method-card:hover {
  border-color: #0d6efd;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.payment-method-card i {
  font-size: 1.5rem;
  display: block;
  margin-bottom: 0.25rem;
}

.payment-method-card small {
  font-weight: 500;
  color: #495057;
}

.form-label {
  font-weight: 500;
  color: #495057;
}

.alert {
  border-radius: 0.5rem;
}

.btn {
  border-radius: 0.375rem;
}

.card {
  border-radius: 0.5rem;
}

.modal-content {
  border-radius: 0.75rem;
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0.75rem 0.75rem 0 0;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0 0 0.75rem 0.75rem;
}
</style>