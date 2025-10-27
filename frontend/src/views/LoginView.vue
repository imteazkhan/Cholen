<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6 col-xl-5">

        <!-- Card wrapper -->
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-md-5">

            <h1 class="h3 mb-4 text-center fw-bold">Enter your phone number</h1>

            <!-- Success / error alerts -->
            <div v-if="alert.message" class="alert" :class="alert.type" role="alert">
              {{ alert.message }}
            </div>

            <form @submit.prevent="handleLogin" novalidate>
              <div class="row g-3 align-items-center">

                <!-- Country code -->
                <div class="col-auto">
                  <select
                    v-model="credentials.countryCode"
                    class="form-select"
                    required
                  >
                    <option value="" disabled>Select code</option>
                    <option v-for="c in countryCodes" :key="c.code" :value="c.code">
                      {{ c.flag }} {{ c.code }}
                    </option>
                  </select>
                </div>

                <!-- Phone number -->
                <div class="col">
                  <input
                    type="tel"
                    class="form-control"
                    placeholder="123 456 7890"
                    v-model.trim="credentials.phone"
                    @input="clearAlert"
                    required
                    pattern="[0-9]{10,15}"
                    autocomplete="tel-national"
                  />
                </div>

                <!-- Submit -->
                <div class="col-12 col-sm-auto d-grid">
                  <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="isSubmitting"
                  >
                    <span
                      v-if="isSubmitting"
                      class="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true"
                    ></span>
                    {{ isSubmitting ? 'Sending OTP…' : 'Continue' }}
                  </button>
                </div>
              </div>

              <!-- Helper text -->
              <div class="form-text mt-2">
                We’ll send you an OTP to verify this number.
              </div>
            </form>

          </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-muted mt-4 small">
          By continuing you agree to our
          <a href="#" class="text-decoration-none">Terms</a> &
          <a href="#" class="text-decoration-none">Privacy Policy</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'

// -------------------------------------------------
// Reactive form state
// -------------------------------------------------
const credentials = reactive({
  countryCode: '+91', // default for India
  phone: ''
})

// -------------------------------------------------
// UI state
// -------------------------------------------------
const isSubmitting = ref(false)
const alert = ref({ message: '', type: '' }) // '' | 'alert-success' | 'alert-danger'

// -------------------------------------------------
// Country codes (add more as needed)
// -------------------------------------------------
const countryCodes = [
  { code: '+1', flag: 'US' },
  { code: '+91', flag: 'IN' },
  { code: '+44', flag: 'GB' },
  { code: '+81', flag: 'JP' },
  { code: '+86', flag: 'CN' },
  { code: '+33', flag: 'FR' },
  { code: '+49', flag: 'DE' },
  { code: '+55', flag: 'BR' }
]

// -------------------------------------------------
// Helpers
// -------------------------------------------------
const clearAlert = () => (alert.value = { message: '', type: '' })

const showAlert = (msg, type = 'alert-danger') => {
  alert.value = { message: msg, type }
  setTimeout(clearAlert, 5000)
}

// -------------------------------------------------
// Login handler
// -------------------------------------------------
const handleLogin = async () => {
  clearAlert()

  // Basic client-side validation
  if (!credentials.countryCode) {
    showAlert('Please select a country code.')
    return
  }
  if (!credentials.phone || !/^\d{10,15}$/.test(credentials.phone)) {
    showAlert('Enter a valid phone number (10-15 digits).')
    return
  }

  isSubmitting.value = true

  try {
    const payload = {
      // API expects full international number or separate fields – adjust as needed
      phone: `${credentials.countryCode}${credentials.phone}`
    }

    const { data } = await axios.post('http://localhost/api/login', payload, {
      headers: { 'Content-Type': 'application/json' }
    })

    // Assuming API returns { success: true, message: 'OTP sent' }
    showAlert(data.message || 'OTP sent successfully!', 'alert-success')
    // You could redirect to OTP verification page here:
    // router.push('/verify-otp')
  } catch (error) {
    const msg =
      error.response?.data?.message ||
      error.message ||
      'Something went wrong. Try again.'
    showAlert(msg)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Optional: make the card a little more rounded on larger screens */
@media (min-width: 576px) {
  .card {
    border-radius: 1rem;
  }
}
</style>