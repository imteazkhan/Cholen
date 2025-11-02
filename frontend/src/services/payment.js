import api from './api'

// Payment API methods
export const paymentAPI = {
  /**
   * Initialize payment with SSLCommerz
   * @param {Object} paymentData - Payment initialization data
   * @returns {Promise} API response
   */
  initializePayment: (paymentData) => api.post('/payment/initialize', paymentData),

  /**
   * Get payment status
   * @param {string} transactionId - Transaction ID
   * @returns {Promise} API response
   */
  getPaymentStatus: (transactionId) => api.get(`/payment/status/${transactionId}`),

  /**
   * Process cash payment
   * @param {Object} paymentData - Cash payment data
   * @returns {Promise} API response
   */
  processCashPayment: async (paymentData) => {
    try {
      const response = await api.post('/payment/cash', paymentData)
      
      if (response.data.success) {
        return {
          success: true,
          payment: response.data.data.payment,
          message: 'Cash payment processed successfully'
        }
      } else {
        throw new Error(response.data.message || 'Cash payment failed')
      }
    } catch (error) {
      console.error('Cash payment error:', error)
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Cash payment failed',
        error: error
      }
    }
  },

  /**
   * Process mobile payment
   * @param {Object} paymentData - Mobile payment data
   * @returns {Promise} API response
   */
  processMobilePayment: async (paymentData) => {
    try {
      // Initialize payment
      const response = await paymentAPI.initializePayment(paymentData)

      if (response.data.success) {
        const { gateway_url, session_key, transaction_id } = response.data.data

        // For mobile, we'll open the payment gateway in a new window/webview
        return {
          success: true,
          paymentUrl: gateway_url,
          sessionKey: session_key,
          transactionId: transaction_id,
          message: 'Payment initialized successfully'
        }
      } else {
        throw new Error(response.data.message || 'Payment initialization failed')
      }
    } catch (error) {
      console.error('Mobile payment error:', error)
      return {
        success: false,
        message: error.response?.data?.message || error.message || 'Payment failed',
        error: error
      }
    }
  },

  /**
   * Open SSLCommerz payment gateway
   * @param {string} paymentUrl - Gateway URL
   * @param {string} transactionId - Transaction ID
   * @returns {Promise} Payment result
   */
  openPaymentGateway: (paymentUrl, transactionId) => {
    return new Promise((resolve, reject) => {
      // For web browsers
      if (typeof window !== 'undefined') {
        const paymentWindow = window.open(
          paymentUrl,
          'sslcommerz_payment',
          'width=800,height=600,scrollbars=yes,resizable=yes'
        )

        // Check payment status periodically
        const checkPaymentStatus = setInterval(async () => {
          try {
            // Check if payment window is closed
            if (paymentWindow.closed) {
              clearInterval(checkPaymentStatus)

              // Check final payment status
              const statusResponse = await paymentAPI.getPaymentStatus(transactionId)

              if (statusResponse.data.success) {
                const payment = statusResponse.data.data

                if (payment.status === 'completed') {
                  resolve({
                    success: true,
                    message: 'Payment completed successfully',
                    payment: payment
                  })
                } else if (payment.status === 'failed') {
                  reject({
                    success: false,
                    message: 'Payment failed',
                    payment: payment
                  })
                } else if (payment.status === 'cancelled') {
                  reject({
                    success: false,
                    message: 'Payment cancelled by user',
                    payment: payment
                  })
                } else {
                  reject({
                    success: false,
                    message: 'Payment status unknown',
                    payment: payment
                  })
                }
              } else {
                reject({
                  success: false,
                  message: 'Failed to check payment status'
                })
              }
            }
          } catch (error) {
            clearInterval(checkPaymentStatus)
            reject({
              success: false,
              message: 'Error checking payment status',
              error: error
            })
          }
        }, 2000) // Check every 2 seconds

        // Timeout after 10 minutes
        setTimeout(() => {
          clearInterval(checkPaymentStatus)
          if (!paymentWindow.closed) {
            paymentWindow.close()
          }
          reject({
            success: false,
            message: 'Payment timeout - please try again'
          })
        }, 600000) // 10 minutes
      } else {
        // For mobile apps, return the URL for webview
        resolve({
          success: true,
          paymentUrl: paymentUrl,
          transactionId: transactionId,
          message: 'Open payment URL in webview'
        })
      }
    })
  },

  /**
   * Validate payment amount and details
   * @param {Object} paymentData - Payment data to validate
   * @returns {Object} Validation result
   */
  validatePaymentData: (paymentData) => {
    const errors = {}

    if (!paymentData.ride_id) {
      errors.ride_id = 'Ride ID is required'
    }

    if (!paymentData.amount || paymentData.amount <= 0) {
      errors.amount = 'Valid amount is required'
    }

    if (!paymentData.customer_name || paymentData.customer_name.trim() === '') {
      errors.customer_name = 'Customer name is required'
    }

    if (!paymentData.customer_email || !paymentData.customer_email.includes('@')) {
      errors.customer_email = 'Valid email is required'
    }

    if (!paymentData.customer_phone || paymentData.customer_phone.length < 10) {
      errors.customer_phone = 'Valid phone number is required'
    }

    return {
      isValid: Object.keys(errors).length === 0,
      errors: errors
    }
  }
}

export default paymentAPI