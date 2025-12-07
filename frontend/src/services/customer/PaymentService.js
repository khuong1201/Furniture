// services/customer/PaymentService.js

class PaymentService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!PaymentService._instance) {
      PaymentService._instance = new PaymentService();
    }
    return PaymentService._instance;
  }

  // --- HÀM PRIVATE XỬ LÝ REQUEST ---
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
        throw new Error(result.message || `Lỗi API Payment: ${response.status}`);
      }

      return result.data || result;
    } catch (error) {
      console.error(`Payment Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ================================
  // ========== APIs =================
  // ================================

  // 1. Lấy lịch sử giao dịch: GET /payments
  // Params: { page, status }
  async getPayments(params = {}) {
    const validParams = Object.fromEntries(
      Object.entries(params).filter(([_, v]) => v != null && v !== '')
    );
    const queryString = new URLSearchParams(validParams).toString();

    return this._request(`/payments?${queryString}`, {
      method: 'GET',
    });
  }

  // 2. Tạo yêu cầu thanh toán: POST /payments
  // Body: { order_uuid, method } (method: 'cod', 'momo', 'vnpay')
  async initiatePayment(data) {
    return this._request('/payments', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // 3. Xem chi tiết giao dịch: GET /payments/{uuid}
  async getPaymentDetail(uuid) {
    return this._request(`/payments/${uuid}`, {
      method: 'GET',
    });
  }

  // 4. Cập nhật trạng thái (Thường dùng cho Admin hoặc Testing): PUT /payments/{uuid}
  async updatePayment(uuid, data) {
    return this._request(`/payments/${uuid}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  // ================================
  // ========== Static Call =========
  // ================================

  static getPayments(params) { return PaymentService.instance.getPayments(params); }
  static initiatePayment(data) { return PaymentService.instance.initiatePayment(data); }
  static getPaymentDetail(uuid) { return PaymentService.instance.getPaymentDetail(uuid); }
}

export default PaymentService;