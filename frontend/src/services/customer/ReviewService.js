import AuthService from './AuthService';

class ReviewService {
  static _instance = null;

  constructor() {
    // Lưu ý: baseUrl đã có dấu '/' ở cuối
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

      // ✅ Chỉ gắn token khi cần (requireAuth = true)
      if (requireAuth && token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      // Nối URL: baseUrl (có /) + endpoint (không nên có / ở đầu)
      const url = `${this.baseUrl}${endpoint}`;

      const config = {
        ...options,
        headers,
      };

      const response = await fetch(url, config);

      // ✅ Tự refresh token nếu hết hạn (401)
      if (response.status === 401 && !isRetry && requireAuth) {
        console.log('🔄 ReviewService: Token hết hạn, đang refresh...');

        try {
          await AuthService.refreshToken();
          const newToken = localStorage.getItem('access_token');
          // Update lại token cho request hiện tại
          config.headers['Authorization'] = `Bearer ${newToken}`;
          console.log('✅ Refresh thành công, gửi lại request Review...');
          // Gọi lại request với isRetry = true
          return this._request(endpoint, options, true, requireAuth);

        } catch (refreshError) {
          console.error('❌ Refresh thất bại, logout...', refreshError);
          AuthService.logout();
          window.location.href = '/login';
          throw refreshError;
        }
      }

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result?.message || `API Error: ${response.status}`);
      }

      // Trả về data (thường Backend trả về { success: true, data: ... })
      return result.data;

    } catch (error) {
      console.error(`ReviewService Error (${endpoint}):`, error);
      throw error;
    }
  }

  /* ==============================================
     NHÓM PUBLIC (Không cần Token) - Prefix: public/reviews
  ============================================== */

  // 1. Lấy danh sách đánh giá (Có lọc & phân trang)
  // Route: GET api/public/reviews
  async getReviews({ product_uuid, page = 1, rating, sort_by } = {}) {
    const params = new URLSearchParams();
    if (product_uuid) params.append('product_uuid', product_uuid);
    if (page) params.append('page', page);
    if (rating) params.append('rating', rating);
    if (sort_by) params.append('sort_by', sort_by);

    // Endpoint không có dấu '/' ở đầu để tránh double slash
    return this._request(`/public/reviews?${params.toString()}`, {
      method: 'GET',
    }, false, false); // requireAuth = false
  }

  // 2. Lấy thống kê sao (5 sao bao nhiêu %, 4 sao...)
  // Route: GET api/public/reviews/stats
  async getReviewStats(product_uuid) {
    if (!product_uuid) throw new Error('Product UUID is required for stats');
    
    return this._request(`/public/reviews/stats?product_uuid=${product_uuid}`, {
        method: 'GET'
    }, false, false);
  }

  // 3. Xem chi tiết 1 review (Nếu cần)
  // Route: GET api/public/reviews/{uuid}
  async getReviewDetail(uuid) {
    return this._request(`/public/reviews/${uuid}`, {
      method: 'GET',
    }, false, false);
  }

  /* ==============================================
     NHÓM PROTECTED (Cần Token) - Prefix: reviews
  ============================================== */

  // 4. Tạo đánh giá mới
  // Route: POST api/reviews
  async createReview(data) {
    return this._request('/reviews', {  
      method: 'POST',
      body: JSON.stringify(data),
    }, false, true); // requireAuth = true
  }

  // 5. Cập nhật đánh giá
  // Route: PUT api/reviews/{uuid}
  async updateReview(uuid, data) {
    return this._request(`/reviews/${uuid}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    }, false, true);
  }

  // 6. Xóa đánh giá
  // Route: DELETE api/reviews/{uuid}
  async deleteReview(uuid) {
    return this._request(`/reviews/${uuid}`, {
      method: 'DELETE',
    }, false, true);
  }

  /* ==============================================
     STATIC HELPERS (Để gọi nhanh không cần new)
  ============================================== */
  
  static async getReviews(params) {
    return ReviewService.instance.getReviews(params);
  }

  static async getReviewStats(product_uuid) {
    return ReviewService.instance.getReviewStats(product_uuid);
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