import api from './api'

export const categoryService = {
  async getCategories(parentOnly = false) {
    const params = parentOnly ? '?parent_only=true' : ''
    return await api.get(`/categories${params}`)
  },

  async getCategoryProducts(categoryId, params = {}) {
    const queryString = new URLSearchParams(params).toString()
    return await api.get(`/categories/${categoryId}/products${queryString ? `?${queryString}` : ''}`)
  },

  // Admin endpoints
  async createCategory(categoryData) {
    return await api.post('/admin/categories', categoryData)
  },

  async updateCategory(categoryId, categoryData) {
    return await api.put(`/admin/categories/${categoryId}`, categoryData)
  },

  async deleteCategory(categoryId) {
    return await api.delete(`/admin/categories/${categoryId}`)
  }
}