# Global Alert System Usage Guide

This project uses a unified alert system that combines:
- **Toast Notifications** (top-right corner) for feedback messages
- **SweetAlert2** for confirmation dialogs and important alerts

## Quick Start

Import the `useAlert` composable in your component:

```vue
<script setup>
import { useAlert } from '@/composables/useAlert'

const alert = useAlert()
</script>
```

## Toast Notifications

Use these for quick feedback messages that appear in the top-right corner:

### Success Messages
```javascript
alert.success('Product created successfully')
alert.success('Operation completed', 'Custom Title')
```

### Error Messages
```javascript
alert.error('Failed to save data')
alert.error('Something went wrong', 'Error Title')
```

### Warning Messages
```javascript
alert.warning('Please check your input')
alert.warning('Validation failed', 'Warning')
```

### Info Messages
```javascript
alert.info('New updates available')
alert.info('This is an info message', 'Info')
```

## Confirmation Dialogs (SweetAlert2)

Use these for actions that need user confirmation:

### Basic Confirmation
```javascript
const confirmed = await alert.confirm({
  title: 'Are you sure?',
  text: 'This action cannot be undone',
  icon: 'warning', // 'warning', 'error', 'success', 'info', 'question'
  confirmButtonText: 'Yes, proceed',
  cancelButtonText: 'Cancel'
})

if (confirmed) {
  // User clicked confirm
  // Proceed with the action
}
```

### Delete Confirmation (Shorthand)
```javascript
const confirmed = await alert.confirmDelete('Product Name')

if (confirmed) {
  // Proceed with deletion
}
```

### Simple Alert Dialog
```javascript
await alert.alert('Success!', 'Your changes have been saved', 'success')
```

### Loading Dialog
```javascript
// Show loading
alert.showLoading('Processing...', 'Please wait')

// Perform async operation
await someAsyncOperation()

// Close loading
alert.close()
```

## Complete Example

```vue
<script setup>
import { ref } from 'vue'
import { useAlert } from '@/composables/useAlert'
import { productService } from '@/services/productService'

const alert = useAlert()
const loading = ref(false)

/**
 * Delete product with confirmation
 */
async function deleteProduct(product) {
  const confirmed = await alert.confirmDelete(product.name)
  
  if (confirmed) {
    loading.value = true
    try {
      await productService.deleteProduct(product.id)
      alert.success('Product deleted successfully')
      await loadProducts()
    } catch (error) {
      alert.error('Failed to delete product')
    } finally {
      loading.value = false
    }
  }
}

/**
 * Save product with validation
 */
async function saveProduct() {
  // Validation
  if (!form.value.name) {
    alert.warning('Please enter a product name')
    return
  }

  loading.value = true
  try {
    await productService.createProduct(form.value)
    alert.success('Product created successfully')
  } catch (error) {
    alert.error('Failed to save product: ' + error.message)
  } finally {
    loading.value = false
  }
}

/**
 * Custom confirmation
 */
async function publishBlog() {
  const confirmed = await alert.confirm({
    title: 'Publish Blog Post?',
    text: 'This will make the blog post visible to all users',
    icon: 'question',
    confirmButtonText: 'Yes, publish it',
    cancelButtonText: 'Not yet',
    confirmButtonColor: '#10b981'
  })
  
  if (confirmed) {
    // Publish logic here
    alert.success('Blog post published!')
  }
}
</script>
```

## Advanced Usage

### Custom SweetAlert2 Dialog
```javascript
const result = await alert.customAlert({
  title: 'Custom Dialog',
  html: '<p>Custom HTML content</p>',
  icon: 'info',
  showCancelButton: true,
  confirmButtonText: 'OK'
})
```

## Best Practices

1. **Use Toast Notifications for:**
   - Success/failure feedback after operations
   - Validation warnings
   - Non-critical information

2. **Use Confirmation Dialogs for:**
   - Delete operations
   - Irreversible actions
   - Important decisions

3. **Message Guidelines:**
   - Keep messages concise and clear
   - Use active voice
   - Be specific about what happened or what will happen

4. **Avoid:**
   - Using browser's native `alert()`, `confirm()`
   - Showing multiple alerts at the same time
   - Long messages in toast notifications

## Icon Types (SweetAlert2)

- `warning` - For cautionary confirmations
- `error` - For error confirmations
- `success` - For success confirmations
- `info` - For informational dialogs
- `question` - For question-based confirmations

## Color Customization

Default colors can be customized in the confirmation dialog:

```javascript
const confirmed = await alert.confirm({
  title: 'Delete Item?',
  confirmButtonColor: '#dc2626', // Red
  cancelButtonColor: '#6b7280'   // Gray
})
```

## Migration from Old Code

**Before:**
```javascript
alert('Success!')
confirm('Are you sure?')
appStore.showSuccess('Done')
```

**After:**
```javascript
const alert = useAlert()

alert.success('Done')
const confirmed = await alert.confirm({ title: 'Are you sure?' })
```

