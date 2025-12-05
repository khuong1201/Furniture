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

      // ✅ Trả về data cốt lõi để Hook dễ xử lý
      return result.data || result;
    } catch (error) {
      console.error(`Order Service Error (${endpoint}):`, error);
      throw error;
    }
  }
  // ================================
  // ========== APIs =================
  // ================================

  // ✅ Tạo order thường: POST /orders
  async createOrder(data) {
    return this._request('/orders', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // ✅ Checkout từ giỏ hàng: POST /orders/checkout
  async checkout(data) {
    return this._request('/orders/checkout', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // ✅ Lấy danh sách đơn hàng: GET /orders
  async getMyOrders(params = {}) {
    // Lọc bỏ param rỗng
    const validParams = Object.fromEntries(
      Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );
    const queryString = new URLSearchParams(validParams).toString();
    
    return this._request(`/orders?${queryString}`, {
      method: 'GET',
    });
  }

  // ✅ Lấy chi tiết đơn theo UUID: GET /orders/{uuid}
  async getOrderDetail(uuid) {
    return this._request(`/orders/${uuid}`, {
      method: 'GET',
    });
  }

  // ✅ Hủy đơn: POST /orders/{uuid}/cancel
  async cancelOrder(uuid) {
    return this._request(`/orders/${uuid}/cancel`, {
      method: 'POST',
    });
  }

  // ================================
  // ========== Static Call =========
  // ================================

  static createOrder(data) {
    return OrderService.instance.createOrder(data);
  }

  static checkout(data) {
    return OrderService.instance.checkout(data);
  }

  static getMyOrders() {
    return OrderService.instance.getMyOrders();
  }

  static getOrderDetail(uuid) {
    return OrderService.instance.getOrderDetail(uuid);
  }

  static cancelOrder(uuid) {
    return OrderService.instance.cancelOrder(uuid);
  }
}

export default OrderService;
