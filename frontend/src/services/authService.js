import api from './api'

export const authService = {
  async login(credentials) {
    return await api.post('/login', credentials)
  },

  // Registration disabled
  // async register(userData) {
  //   return await api.post('/register', userData)
  // },

  async logout() {
    return await api.post('/logout')
  },

  async getUser() {
    return await api.get('/user')
  },

  async updateProfile(profileData) {
    return await api.put('/user/profile', profileData)
  },

  async updatePassword(passwordData) {
    return await api.put('/user/password', passwordData)
  }
}