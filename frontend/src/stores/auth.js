import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import { useAppStore } from './app'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const isInitialized = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.is_admin || false)

  // Actions
  async function login(credentials) {
    const appStore = useAppStore()
    appStore.setLoading(true)

    try {
      const response = await authService.login(credentials)

      token.value = response.token
      user.value = response.user

      localStorage.setItem('auth_token', response.token)

      appStore.showSuccess('Login successful!')
      return { success: true }
    } catch (error) {
      appStore.showError(error.message || 'Login failed')
      return { success: false, error: error.message }
    } finally {
      appStore.setLoading(false)
    }
  }

  // Registration disabled
  // async function register(userData) {
  //   const appStore = useAppStore()
  //   appStore.setLoading(true)

  //   try {
  //     const response = await authService.register(userData)

  //     token.value = response.token
  //     user.value = response.user

  //     localStorage.setItem('auth_token', response.token)

  //     appStore.showSuccess('Registration successful!')
  //     return { success: true }
  //   } catch (error) {
  //     appStore.showError(error.message || 'Registration failed')
  //     return { success: false, error: error.message }
  //   } finally {
  //     appStore.setLoading(false)
  //   }
  // }

  async function logout() {
    const appStore = useAppStore()

    try {
      if (token.value) {
        await authService.logout()
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      // Clear local state regardless of API call result
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      appStore.showInfo('Logged out successfully')
    }
  }

  async function fetchUser() {
    if (!token.value) {
      isInitialized.value = true
      return
    }

    try {
      const response = await authService.getUser()
      user.value = response.user
    } catch (error) {
      console.error('Failed to fetch user:', error)
      // If token is invalid, clear auth state
      if (error.status === 401) {
        await logout()
      }
    } finally {
      isInitialized.value = true
    }
  }

  async function initialize() {
    if (isInitialized.value) return
    await fetchUser()
  }

  async function updateProfile(profileData) {
    const appStore = useAppStore()
    appStore.setLoading(true)

    try {
      const response = await authService.updateProfile(profileData)
      user.value = response.user
      appStore.showSuccess('Profile updated successfully!')
      return { success: true }
    } catch (error) {
      appStore.showError(error.message || 'Failed to update profile')
      return { success: false, error: error.message }
    } finally {
      appStore.setLoading(false)
    }
  }

  async function updatePassword(passwordData) {
    const appStore = useAppStore()
    appStore.setLoading(true)

    try {
      await authService.updatePassword(passwordData)
      appStore.showSuccess('Password updated successfully!')
      return { success: true }
    } catch (error) {
      appStore.showError(error.message || 'Failed to update password')
      return { success: false, error: error.message }
    } finally {
      appStore.setLoading(false)
    }
  }

  return {
    // State
    user,
    token,
    isInitialized,

    // Getters
    isAuthenticated,
    isAdmin,

    // Actions
    login,
    // register, // Registration disabled
    logout,
    fetchUser,
    initialize,
    updateProfile,
    updatePassword
  }
})