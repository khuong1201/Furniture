class OrderService {
  // 1. Singleton Instance
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  // 2. Lấy instance duy nhất
  static get instance() {
    if (!OrderService._instance) {
      OrderService._instance = new OrderService();
    }
    return OrderService._instance;
  }

  // 3. Set / Remove Token
  setToken(token) {
    if (token) {
      this.headers['Authorization'] = `Bearer ${token}`;
    } else {
      delete this.headers['Authorization'];
    }
  }

  // 4. Hàm request dùng chung
  async _request(endpoint, options = {}) {
    try {
      const token = localStorage.getItem('access_token');
      const headers = { ...this.headers, ...options.headers };

      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const url = `${this.baseUrl}${endpoint}`;
      const response = await fetch(url, { ...options, headers });
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || `Lỗi API: ${response.status}`);
      }

      return result.data || result;
    } catch (error) {
      console.error(`Order Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ================================
  // ========== APIs =================
  // ================================

  // 1. Lấy danh sách đơn hàng: GET /orders
  async getMyOrders(params = {}) {
    const validParams = Object.fromEntries(
      Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );
    const queryString = new URLSearchParams(validParams).toString();
    
    return this._request(`/orders?${queryString}`, {
      method: 'GET',
    });
  }

  // 2. Checkout từ giỏ hàng: POST /orders/checkout
  async checkout(data) {
    return this._request('/orders/checkout', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // 3. Mua ngay: POST /orders/buy-now
  async buyNow(data) {
    return this._request('/orders/buy-now', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // 4. Lấy chi tiết đơn: GET /orders/{uuid}
  async getOrderDetail(uuid) {
    return this._request(`/orders/${uuid}`, {
      method: 'GET',
    });
  }

  // 5. Hủy đơn: POST /orders/{uuid}/cancel
  async cancelOrder(uuid) {
    return this._request(`/orders/${uuid}/cancel`, {
      method: 'POST',
    });
  }

  // 6. Tạo thủ công (Ít dùng cho Customer): POST /orders
  async createOrder(data) {
    return this._request('/orders', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // ================================
  // ========== Static Call =========
  // ================================

  static getMyOrders(params) { return OrderService.instance.getMyOrders(params); }
  static checkout(data) { return OrderService.instance.checkout(data); }
  static buyNow(data) { return OrderService.instance.buyNow(data); } // [BỔ SUNG]
  static getOrderDetail(uuid) { return OrderService.instance.getOrderDetail(uuid); }
  static cancelOrder(uuid) { return OrderService.instance.cancelOrder(uuid); }
  static createOrder(data) { return OrderService.instance.createOrder(data); }
}

export default OrderService;