import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

/**
 * Composable for global session management and verification
 * Periodically checks authentication status and handles expired sessions
 * 
 * @param {number} checkInterval - Interval in milliseconds to check session (default: 5 minutes)
 * @returns {Object} Session guard methods and state
 */
export function useSessionGuard(checkInterval = 5 * 60 * 1000) {
  const authStore = useAuthStore()
  const router = useRouter()
  const intervalId = ref(null)
  const lastActivityTime = ref(Date.now())
  const maxInactiveTime = 30 * 60 * 1000 // 30 minutes

  /**
   * Updates the last activity timestamp
   */
  const updateActivity = () => {
    lastActivityTime.value = Date.now()
  }

  /**
   * Verifies the current session validity
   */
  const verifySession = async () => {
    if (!authStore.isAuthenticated) {
      return
    }

    try {
      // Check if user has been inactive for too long
      const inactiveTime = Date.now() - lastActivityTime.value
      if (inactiveTime > maxInactiveTime) {
        await handleSessionExpired('Session expired due to inactivity')
        return
      }

      // Verify session with backend
      await authStore.fetchUser()
    } catch (error) {
      if (error.status === 401) {
        await handleSessionExpired('Your session has expired')
      }
    }
  }

  /**
   * Handles expired session
   * 
   * @param {string} message - Message to display
   */
  const handleSessionExpired = async (message) => {
    await authStore.logout()
    router.push({
      path: '/login',
      query: { message, expired: 'true' }
    })
  }

  /**
   * Starts the session verification interval
   */
  const startSessionMonitoring = () => {
    if (intervalId.value) {
      return
    }

    // Initial verification
    verifySession()

    // Set up periodic verification
    intervalId.value = setInterval(() => {
      verifySession()
    }, checkInterval)

    // Track user activity
    const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click']
    activityEvents.forEach(event => {
      window.addEventListener(event, updateActivity, { passive: true })
    })
  }

  /**
   * Stops the session verification interval
   */
  const stopSessionMonitoring = () => {
    if (intervalId.value) {
      clearInterval(intervalId.value)
      intervalId.value = null
    }

    // Remove activity listeners
    const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click']
    activityEvents.forEach(event => {
      window.removeEventListener(event, updateActivity)
    })
  }

  // Lifecycle hooks
  onMounted(() => {
    if (authStore.isAuthenticated) {
      startSessionMonitoring()
    }
  })

  onUnmounted(() => {
    stopSessionMonitoring()
  })

  return {
    verifySession,
    startSessionMonitoring,
    stopSessionMonitoring,
    updateActivity,
    lastActivityTime
  }
}

