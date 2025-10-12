import api from './api'

/**
 * Service for blog-related API calls
 */
export const blogService = {
  /**
   * Get published blogs for guests
   */
  async getBlogs(params = {}) {
    const queryString = new URLSearchParams(params).toString()
    return await api.get(`/blogs${queryString ? `?${queryString}` : ''}`)
  },

  /**
   * Get a single blog by slug for guests
   */
  async getBlog(slug) {
    return await api.get(`/blogs/${slug}`)
  },

  /**
   * Search blogs
   */
  async searchBlogs(query, params = {}) {
    return await api.get('/blogs', {
      params: {
        search: query,
        ...params
      }
    })
  },

  // Admin endpoints
  /**
   * Get all blogs for admin (including drafts)
   */
  async getAdminBlogs(params = {}) {
    const queryString = new URLSearchParams(params).toString()
    return await api.get(`/admin/blogs${queryString ? `?${queryString}` : ''}`)
  },

  /**
   * Get a single blog by ID for admin
   */
  async getAdminBlog(blogId) {
    return await api.get(`/admin/blogs/${blogId}`)
  },

  /**
   * Get blog statistics for admin dashboard
   */
  async getBlogStatistics() {
    return await api.get('/admin/blogs/statistics')
  },

  /**
   * Create a new blog
   */
  async createBlog(blogData) {
    return await api.post('/admin/blogs', blogData)
  },

  /**
   * Update a blog
   */
  async updateBlog(blogId, blogData) {
    return await api.put(`/admin/blogs/${blogId}`, blogData)
  },

  /**
   * Delete a blog
   */
  async deleteBlog(blogId) {
    return await api.delete(`/admin/blogs/${blogId}`)
  },

  // Blog image endpoints
  /**
   * Get all images for a blog
   */
  async getBlogImages(blogId) {
    return await api.get(`/admin/blogs/${blogId}/images`)
  },

  /**
   * Upload a single image to a blog
   */
  async uploadBlogImage(blogId, formData) {
    return await api.post(`/admin/blogs/${blogId}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Upload multiple images to a blog
   */
  async uploadMultipleBlogImages(blogId, formData) {
    return await api.post(`/admin/blogs/${blogId}/images/multiple`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Update blog image details
   */
  async updateBlogImage(blogId, imageId, imageData) {
    return await api.put(`/admin/blogs/${blogId}/images/${imageId}`, imageData)
  },

  /**
   * Delete a blog image
   */
  async deleteBlogImage(blogId, imageId) {
    return await api.delete(`/admin/blogs/${blogId}/images/${imageId}`)
  }
}

