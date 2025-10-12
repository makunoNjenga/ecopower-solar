import api from './api'

export const productService = {
  async getProducts(params = {}) {
    const queryString = new URLSearchParams(params).toString()
    return await api.get(`/products${queryString ? `?${queryString}` : ''}`)
  },

  async getProduct(productId) {
    return await api.get(`/products/${productId}`)
  },

  async getFeaturedProducts() {
    return await api.get('/products?featured=true&per_page=8')
  },

  async searchProducts(query, params = {}) {
    return await api.get('/products', {
      params: {
        search: query,
        ...params
      }
    })
  },

  // Admin endpoints
  async getAdminProducts(params = {}) {
    const queryString = new URLSearchParams(params).toString()
    return await api.get(`/admin/products${queryString ? `?${queryString}` : ''}`)
  },

  async getAdminProduct(productId) {
    return await api.get(`/admin/products/${productId}`)
  },

  async createProduct(productData) {
    return await api.post('/admin/products', productData)
  },

  async updateProduct(productId, productData) {
    return await api.put(`/admin/products/${productId}`, productData)
  },

  async deleteProduct(productId) {
    return await api.delete(`/admin/products/${productId}`)
  },

  // Product image endpoints
  async getProductImages(productId) {
    return await api.get(`/admin/products/${productId}/images`)
  },

  async uploadProductImage(productId, formData) {
    return await api.post(`/admin/products/${productId}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  async uploadMultipleProductImages(productId, formData) {
    return await api.post(`/admin/products/${productId}/images/multiple`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  async updateProductImage(productId, imageId, imageData) {
    return await api.put(`/admin/products/${productId}/images/${imageId}`, imageData)
  },

  async deleteProductImage(productId, imageId) {
    return await api.delete(`/admin/products/${productId}/images/${imageId}`)
  }
}