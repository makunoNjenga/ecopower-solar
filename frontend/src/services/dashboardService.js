import api from './api';

export const dashboardService = {
  /**
   * Get dashboard statistics
   */
  async getStats() {
    return await api.get('/admin/dashboard/stats');
  },
};


