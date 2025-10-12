import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const isLoading = ref(false)
  const notifications = ref([])

  // Actions
  function setLoading(loading) {
    isLoading.value = loading
  }

  function addNotification(notification) {
    const id = Date.now() + Math.random()
    const newNotification = {
      id,
      type: 'info',
      duration: 5000,
      ...notification
    }

    notifications.value.push(newNotification)

    // Auto remove notification
    setTimeout(() => {
      removeNotification(id)
    }, newNotification.duration)

    return id
  }

  function removeNotification(id) {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  function showSuccess(message, title = 'Success') {
    return addNotification({
      type: 'success',
      title,
      message
    })
  }

  function showError(message, title = 'Error') {
    return addNotification({
      type: 'error',
      title,
      message,
      duration: 7000
    })
  }

  function showWarning(message, title = 'Warning') {
    return addNotification({
      type: 'warning',
      title,
      message
    })
  }

  function showInfo(message, title = 'Info') {
    return addNotification({
      type: 'info',
      title,
      message
    })
  }

  return {
    // State
    isLoading,
    notifications,

    // Actions
    setLoading,
    addNotification,
    removeNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo
  }
})