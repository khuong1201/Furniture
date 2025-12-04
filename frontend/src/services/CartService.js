class CartService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
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

  async _request(endpoint, options = {}) {
    try {
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const response = await fetch(`${this.baseUrl}${endpoint}`, {
        ...options,
        headers,
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result?.message || 'API Error');
      }

      return result.data; // đúng chuẩn ApiResponse::success
    } catch (error) {
      console.error(`CartService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ✅ 1. Lấy giỏ hàng
  async getCart() {
    return this._request('/carts', { method: 'GET' });
  }

  // ✅ 2. Thêm vào giỏ (PHẢI là variant_uuid)
  async addToCart(variantUuid, quantity = 1) {
    return this._request('/carts', {
      method: 'POST',
      body: JSON.stringify({
        variant_uuid: variantUuid,
        quantity,
      }),
    });
  }

  // ✅ 3. Cập nhật số lượng item
  async updateItem(itemUuid, quantity) {
    return this._request(`/carts/${itemUuid}`, {
      method: 'PUT',
      body: JSON.stringify({ quantity }),
    });
  }

  // ✅ 4. Xóa item
  async removeItem(itemUuid) {
    return this._request(`/carts/${itemUuid}`, {
      method: 'DELETE',
    });
  }

  // ✅ 5. Làm trống giỏ
  async clearCart() {
    return this._request('/carts', {
      method: 'DELETE',
    });
  }

  // --- STATIC WRAPPERS ---
  static async getCart() {
    return CartService.instance.getCart();
  }

  static async addToCart(variantUuid, qty) {
    return CartService.instance.addToCart(variantUuid, qty);
  }

  static async updateItem(itemUuid, qty) {
    return CartService.instance.updateItem(itemUuid, qty);
  }

  static async removeItem(itemUuid) {
    return CartService.instance.removeItem(itemUuid);
  }

  static async clearCart() {
    return CartService.instance.clearCart();
  }
}

export default CartService;
