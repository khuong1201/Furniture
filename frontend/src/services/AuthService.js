class AuthService {
  static _instance = null;

  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  static get instance() {
    if (!AuthService._instance) {
      AuthService._instance = new AuthService();
    }
    return AuthService._instance;
  }

  setToken(token) {
    if (token) {
      this.headers['Authorization'] = `Bearer ${token}`;
    } else {
      delete this.headers['Authorization'];
    }
  }

  async _request(endpoint, options = {}) {
    try {
      const url = `${this.baseUrl}${endpoint}`;
      const config = {
        ...options,
        headers: {
          ...this.headers,
          ...options.headers,
        },
      };

      const response = await fetch(url, config);
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || `Lỗi API: ${response.status}`);
      }

      return result;
    } catch (error) {
      console.error(`Auth Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ✅ LOGIN
  async login(email, password, device_name) {
    return this._request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, device_name }),
    });
  }

  // ✅ REGISTER
  async register(payload) {
    return this._request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
  }

  // ✅ VERIFY OTP
  async verifyOtp(email, otp) {
    return this._request('/auth/verify', {
      method: 'POST',
      body: JSON.stringify({ email, otp }),
    });
  }

  // ✅ REFRESH TOKEN
  async refreshToken() {
    const refresh_token = localStorage.getItem('refresh_token');

    return this._request('/auth/refresh', {
      method: 'POST',
      body: JSON.stringify({ refresh_token }),
    });
  }

  // ✅ LOGOUT
  async logout() {
    const refresh_token = localStorage.getItem('refresh_token');

    return this._request('/auth/logout', {
      method: 'POST',
      body: JSON.stringify({ refresh_token }),
    });
  }

  // ✅ GET PROFILE
  async getProfile() {
    return this._request('/profile', {
      method: 'GET',
    });
  }

  // ✅ UPDATE PROFILE
  async updateProfile(data) {
    return this._request('/profile', {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  // ✅ CHANGE PASSWORD
  async changePassword(data) {
    return this._request('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // Static wrappers
  static login(email, password, device_name) {
    return AuthService.instance.login(email, password, device_name);
  }

  static register(payload) {
    return AuthService.instance.register(payload);
  }

  static verifyOtp(email, otp) {
    return AuthService.instance.verifyOtp(email, otp);
  }

  static refreshToken() {
    return AuthService.instance.refreshToken();
  }

  static logout() {
    return AuthService.instance.logout();
  }

  static getProfile() {
    return AuthService.instance.getProfile();
  }

  static updateProfile(data) {
    return AuthService.instance.updateProfile(data);
  }

  static changePassword(data) {
    return AuthService.instance.changePassword(data);
  }
}

export default AuthService;
