import AuthService from './AuthService';

class ReviewService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!ReviewService._instance) {
      ReviewService._instance = new ReviewService();
    }
    return ReviewService._instance;
  }

  async _request(endpoint, options = {}, isRetry = false, requireAuth = true) {
    try {
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      // ✅ Chỉ gắn token khi cần
      if (requireAuth && token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;

      const config = {
        ...options,
        headers,
      };

      const response = await fetch(url, config);

      // ✅ Tự refresh token nếu hết hạn
      if (response.status === 401 && !isRetry && requireAuth) {
        console.log('🔄 ReviewService: Token hết hạn, đang refresh...');

        try {
          await AuthService.refreshToken();

          const newToken = localStorage.getItem('access_token');
          config.headers['Authorization'] = `Bearer ${newToken}`;

          console.log('✅ Refresh thành công, gửi lại request Review...');
          return this._request(endpoint, options, true, requireAuth);

        } catch (refreshError) {
          console.error('❌ Refresh thất bại, logout...', refreshError);
          AuthService.logout();
          window.location.href = '/customer/login';
          throw refreshError;
        }
      }

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result?.message || 'API Error');
      }

      return result.data;
    } catch (error) {
      console.error(`ReviewService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ✅ GET REVIEW — KHÔNG CẦN TOKEN
  async getReviews({ product_uuid, page = 1, rating } = {}) {
    const params = new URLSearchParams();
    if (product_uuid) params.append('product_uuid', product_uuid);
    if (page) params.append('page', page);
    if (rating) params.append('rating', rating);

    return this._request(`/reviews?${params.toString()}`, {
      method: 'GET',
    }, false, false); // ❌ Không cần auth
  }

  // ✅ TẠO REVIEW — CẦN TOKEN
  async createReview(data) {
    return this._request('/reviews', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // ✅ UPDATE REVIEW — CẦN TOKEN
  async updateReview(uuid, data) {
    return this._request(`/reviews/${uuid}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  // ✅ DELETE REVIEW — CẦN TOKEN
  async deleteReview(uuid) {
    return this._request(`/reviews/${uuid}`, {
      method: 'DELETE',
    });
  }


  static async getReviews(params) {
    return ReviewService.instance.getReviews(params);
  }

  static async createReview(data) {
    return ReviewService.instance.createReview(data);
  }

  static async updateReview(uuid, data) {
    return ReviewService.instance.updateReview(uuid, data);
  }

  static async deleteReview(uuid) {
    return ReviewService.instance.deleteReview(uuid);
  }
}

export default ReviewService;
