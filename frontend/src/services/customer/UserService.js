class UserService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!UserService._instance) {
      UserService._instance = new UserService();
    }
    return UserService._instance;
  }

  // Helper request giống AddressService
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
        throw new Error(result.message || `Lỗi API (${response.status})`);
      }

      return result.data || result; 
    } catch (error) {
      console.error(`UserService Error (${endpoint}):`, error);
      throw error;
    }
  }

  // --- API ---

  // 1. GET /profile
  async getProfile() {
    return this._request('/profile', { method: 'GET' });
  }

  // 2. PUT /profile
  async updateProfile(data) {
    return this._request('/profile', {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  // 3. POST /auth/change-password
  async changePassword(data) {
    // data: { current_password, new_password, new_password_confirmation }
    return this._request('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // Static helpers
  static async getProfile() { return UserService.instance.getProfile(); }
  static async updateProfile(data) { return UserService.instance.updateProfile(data); }
  static async changePassword(data) { return UserService.instance.changePassword(data); }
}

export default UserService;