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
    return response.data
  },
  (error) => {
    // Handle different error scenarios
    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response

      if (status === 401) {
        // Unauthorized - clear token and redirect to login
        localStorage.removeItem('auth_token')
        if (window.location.pathname !== '/login') {
          window.location.href = '/login'
        }
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