class CartService {
  static _instance = null;

  constructor() {
    // Lưu ý: Endpoint thường là /cart
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

  // Hàm request chung (giống ProductService)
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

      if (!response.ok) {
        throw new Error(`API Error: ${response.statusText}`);
      }

      const result = await response.json();
      return result.data || result; // Laravel thường trả về { data: ... }
    } catch (error) {
      console.error(`CartService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- API METHODS ---

  // 1. Lấy danh sách giỏ hàng
  async getCart() {
    return this._request('/carts', { method: 'GET' });
  }

  // 2. Thêm vào giỏ (Thường dùng ở trang ProductDetail)
  async addToCart(productId, quantity = 1) {
    return this._request('/carts', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, quantity }),
    });
  }

  // 3. Cập nhật số lượng (quantity)
  async updateItem(itemId, quantity) {
    // itemId: ID của dòng trong giỏ hàng (cart_items.id) chứ không phải product_id
    return this._request(`/cart/${itemId}`, {
      method: 'PUT', // Hoặc POST tùy backend bạn viết
      body: JSON.stringify({ quantity }),
    });
  }

  // 4. Xóa sản phẩm khỏi giỏ
  async removeItem(itemId) {
    return this._request(`/cart/remove/${itemId}`, { method: 'DELETE' });
  }

  // --- STATIC WRAPPERS ---
  static async getCart() { return CartService.instance.getCart(); }
  static async addToCart(pId, qty) { return CartService.instance.addToCart(pId, qty); }
  static async updateItem(itemId, qty) { return CartService.instance.updateItem(itemId, qty); }
  static async removeItem(itemId) { return CartService.instance.removeItem(itemId); }
}

export default CartService;