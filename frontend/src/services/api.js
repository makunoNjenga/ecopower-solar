import axios from 'axios'

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Store for tracking last session check
let lastSessionCheck = Date.now()
const SESSION_CHECK_INTERVAL = 2 * 60 * 1000 // 2 minutes

/**
 * Verifies if session check is needed
 * 
 * @returns {boolean} True if session should be verified
 */
const shouldVerifySession = () => {
  const now = Date.now()
  return (now - lastSessionCheck) > SESSION_CHECK_INTERVAL
}

/**
 * Updates last session check timestamp
 */
const updateSessionCheck = () => {
  lastSessionCheck = Date.now()
}

// Request interceptor to add auth token and verify session
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      
      // Add session check flag for verification
      if (shouldVerifySession() && config.url !== '/user') {
        config.headers['X-Verify-Session'] = 'true'
        updateSessionCheck()
      }
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors and session validation
api.interceptors.response.use(
  (response) => {
    return response.data
  },
  (error) => {
    // Handle different error scenarios
    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response

      if (status === 401) {
        // Unauthorized - session expired or invalid token
        const token = localStorage.getItem('auth_token')
        
        if (token) {
          // Had a token but it's invalid/expired - clear everything
          localStorage.removeItem('auth_token')
          
          // Redirect to login with expired session message
          const currentPath = window.location.pathname
          if (currentPath !== '/login') {
            const message = data?.message || 'Your session has expired. Please login again.'
            const queryParams = new URLSearchParams({ 
              message, 
              expired: 'true',
              redirect: currentPath 
            })
            window.location.href = `/login?${queryParams.toString()}`
          }
        } else {
          // No token, just redirect to login
          if (window.location.pathname !== '/login') {
            window.location.href = '/login'
          }
        }
      }

      // Handle 419 (CSRF token mismatch) - treat as session expired
      if (status === 419) {
        localStorage.removeItem('auth_token')
        const message = 'Session expired. Please login again.'
        window.location.href = `/login?message=${encodeURIComponent(message)}&expired=true`
      }

      // Create error object with message
      const errorMessage = data?.message || data?.error || 'An error occurred'
      const customError = new Error(errorMessage)
      customError.status = status
      customError.data = data

      return Promise.reject(customError)
    } else if (error.request) {
      // Network error
      const networkError = new Error('Network error. Please check your connection.')
      networkError.status = 0
      return Promise.reject(networkError)
    } else {
      // Something else happened
      return Promise.reject(error)
    }
  }
)

export default api