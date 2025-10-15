import { useAppStore } from '@/stores/app'
import Swal from 'sweetalert2'

/**
 * Global alert composable that provides toast notifications and confirmation dialogs
 * 
 * @returns {Object} Alert methods
 */
export function useAlert() {
  const appStore = useAppStore()

  /**
   * Show success toast notification
   * 
   * @param {string} message - Success message
   * @param {string} title - Title (default: 'Success')
   * @returns {number} Notification ID
   */
  const success = (message, title = 'Success') => {
    return appStore.showSuccess(message, title)
  }

  /**
   * Show error toast notification
   * 
   * @param {string} message - Error message
   * @param {string} title - Title (default: 'Error')
   * @returns {number} Notification ID
   */
  const error = (message, title = 'Error') => {
    return appStore.showError(message, title)
  }

  /**
   * Show warning toast notification
   * 
   * @param {string} message - Warning message
   * @param {string} title - Title (default: 'Warning')
   * @returns {number} Notification ID
   */
  const warning = (message, title = 'Warning') => {
    return appStore.showWarning(message, title)
  }

  /**
   * Show info toast notification
   * 
   * @param {string} message - Info message
   * @param {string} title - Title (default: 'Info')
   * @returns {number} Notification ID
   */
  const info = (message, title = 'Info') => {
    return appStore.showInfo(message, title)
  }

  /**
   * Show confirmation dialog using SweetAlert2
   * 
   * @param {Object} options - Confirmation options
   * @param {string} options.title - Dialog title
   * @param {string} options.text - Dialog text
   * @param {string} options.icon - Icon type: 'warning', 'error', 'success', 'info', 'question' (default: 'warning')
   * @param {string} options.confirmButtonText - Confirm button text (default: 'Yes, proceed')
   * @param {string} options.cancelButtonText - Cancel button text (default: 'Cancel')
   * @param {string} options.confirmButtonColor - Confirm button color (default: '#ef4444')
   * @param {string} options.cancelButtonColor - Cancel button color (default: '#6b7280')
   * @returns {Promise<boolean>} True if confirmed, false if cancelled
   */
  const confirm = async ({
    title = 'Are you sure?',
    text = 'This action cannot be undone',
    icon = 'warning',
    confirmButtonText = 'Yes, proceed',
    cancelButtonText = 'Cancel',
    confirmButtonColor = '#ef4444',
    cancelButtonColor = '#6b7280'
  } = {}) => {
    const result = await Swal.fire({
      title,
      text,
      icon,
      showCancelButton: true,
      confirmButtonColor,
      cancelButtonColor,
      confirmButtonText,
      cancelButtonText,
      reverseButtons: true,
      customClass: {
        popup: 'rounded-lg',
        confirmButton: 'px-4 py-2 rounded-md font-medium',
        cancelButton: 'px-4 py-2 rounded-md font-medium'
      }
    })

    return result.isConfirmed
  }

  /**
   * Show delete confirmation dialog
   * 
   * @param {string} itemName - Name of the item to delete (optional)
   * @returns {Promise<boolean>} True if confirmed, false if cancelled
   */
  const confirmDelete = async (itemName = 'this item') => {
    return confirm({
      title: 'Delete Confirmation',
      text: `Are you sure you want to delete ${itemName}? This action cannot be undone.`,
      icon: 'warning',
      confirmButtonText: 'Yes, delete it',
      confirmButtonColor: '#dc2626'
    })
  }

  /**
   * Show custom SweetAlert2 dialog
   * 
   * @param {Object} options - SweetAlert2 options
   * @returns {Promise} SweetAlert2 result
   */
  const customAlert = async (options) => {
    return Swal.fire(options)
  }

  /**
   * Show a simple alert dialog (info)
   * 
   * @param {string} title - Alert title
   * @param {string} text - Alert text
   * @param {string} icon - Icon type (default: 'info')
   * @returns {Promise} SweetAlert2 result
   */
  const alert = async (title, text, icon = 'info') => {
    return Swal.fire({
      title,
      text,
      icon,
      confirmButtonColor: '#0ea5e9',
      customClass: {
        popup: 'rounded-lg',
        confirmButton: 'px-4 py-2 rounded-md font-medium'
      }
    })
  }

  /**
   * Show loading dialog
   * 
   * @param {string} title - Loading title
   * @param {string} text - Loading text
   */
  const showLoading = (title = 'Loading...', text = 'Please wait') => {
    Swal.fire({
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })
  }

  /**
   * Close any open SweetAlert2 dialog
   */
  const close = () => {
    Swal.close()
  }

  return {
    // Toast notifications (top-right corner)
    success,
    error,
    warning,
    info,
    
    // Confirmation dialogs (SweetAlert2)
    confirm,
    confirmDelete,
    alert,
    customAlert,
    showLoading,
    close
  }
}

