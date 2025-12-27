import AuthService from './AuthService';

class CartService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!CartService._instance) {
      CartService._instance = new CartService();
    }
    return CartService._instance;
  }

  async _request(endpoint, options = {}, isRetry = false) {
    try {
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;
      
      const config = {
        ...options,
        headers,
      };

      const response = await fetch(url, config);

      // Xử lý khi Token hết hạn (401)
      if (response.status === 401 && !isRetry) {
        console.log('🔄 CartService: Token hết hạn, đang gọi Refresh...');
        
        try {
          // 1. Gọi API refresh
          await AuthService.refreshToken();
          
          // 2. Lấy token mới
          const newToken = localStorage.getItem('access_token');
          
          // 3. Cập nhật header cho config (Giờ biến config đã tồn tại nên không lỗi nữa)
          config.headers['Authorization'] = `Bearer ${newToken}`;
          
          console.log('✅ Refresh thành công, gửi lại request Cart...');
          
          // 4. Gọi lại hàm chính nó với cờ isRetry = true
          return this._request(endpoint, options, true); 

        } catch (refreshError) {
          console.error('❌ Refresh thất bại, logout...', refreshError);
          AuthService.logout();
          window.location.href = '/login';
          throw refreshError;
        }
      }

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result?.message || 'API Error');
      }

      return result.data; 
    } catch (error) {
      console.error(`CartService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- CÁC HÀM KHÁC GIỮ NGUYÊN ---

  async getCart() {
    return this._request('/carts', { method: 'GET' });
  }

  async addToCart(variantUuid, quantity = 1) {
    return this._request('/carts', {
      method: 'POST',
      body: JSON.stringify({
        variant_uuid: variantUuid,
        quantity,
      }),
    });
  }

  async updateItem(itemUuid, quantity) {
    return this._request(`/carts/${itemUuid}`, {
      method: 'PUT',
      body: JSON.stringify({ quantity }),
    });
  }

  async removeItem(itemUuid) {
    return this._request(`/carts/${itemUuid}`, {
      method: 'DELETE',
    });
  }

  async clearCart() {
    return this._request('/carts', {
      method: 'DELETE',
    });
  }

  static async getCart() { return CartService.instance.getCart(); }
  static async addToCart(variantUuid, qty) { return CartService.instance.addToCart(variantUuid, qty); }
  static async updateItem(itemUuid, qty) { return CartService.instance.updateItem(itemUuid, qty); }
  static async removeItem(itemUuid) { return CartService.instance.removeItem(itemUuid); }
  static async clearCart() { return CartService.instance.clearCart(); }
}

export default CartService;