class AddressService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!AddressService._instance) {
      AddressService._instance = new AddressService();
    }
    return AddressService._instance;
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
        // Ném lỗi kèm message từ Backend nếu có
        throw new Error(result.message || `Lỗi API (${response.status})`);
      }

      // Backend trả về: { success: true, data: ... }
      return result.data || result; 
    } catch (error) {
      console.error(`AddressService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- 1. LẤY DANH SÁCH ĐỊA CHỈ ---
  async getAddresses() {
    return this._request('/addresses', { method: 'GET' });
  }

  // --- 2. XEM CHI TIẾT ---
  async getAddressDetail(uuid) {
    return this._request(`/addresses/${uuid}`, { method: 'GET' });
  }

  // --- 3. THÊM MỚI ---
  async createAddress(data) {
    return this._request('/addresses', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // --- 4. CẬP NHẬT ---
  async updateAddress(uuid, data) {
    return this._request(`/addresses/${uuid}`, {
      method: 'PUT', // Controller dùng PUT
      body: JSON.stringify(data),
    });
  }

  // --- 5. XÓA ---
  async deleteAddress(uuid) {
    return this._request(`/addresses/${uuid}`, { method: 'DELETE' });
  }

  // Static helpers
  static async getAddresses() { return AddressService.instance.getAddresses(); }
  static async getAddressDetail(uuid) { return AddressService.instance.getAddressDetail(uuid); }
  static async createAddress(data) { return AddressService.instance.createAddress(data); }
  static async updateAddress(uuid, data) { return AddressService.instance.updateAddress(uuid, data); }
  static async deleteAddress(uuid) { return AddressService.instance.deleteAddress(uuid); }
}

export default AddressService;