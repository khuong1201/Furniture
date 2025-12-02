class AuthService {
  // 1. Singleton Instance
  static _instance = null;

  constructor() {
    // Lấy URL từ biến môi trường hoặc dùng mặc định
    this.baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    this.headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  // 2. Hàm static để lấy instance duy nhất
  static get instance() {
    if (!AuthService._instance) {
      AuthService._instance = new AuthService();
    }
    return AuthService._instance;
  }

  // 3. Cập nhật Token (Dùng khi user đã đăng nhập)
  setToken(token) {
    if (token) {
      this.headers['Authorization'] = `Bearer ${token}`;
    } else {
      delete this.headers['Authorization'];
    }
  }

  // 4. Hàm Private xử lý request chung
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

      return result; // Auth thường cần lấy full response (token, user info...)
    } catch (error) {
      console.error(`Auth Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  async login(email, password,device_name) {
    const data = await this._request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({email, password,device_name }),
    });
    
    // Tự động set token vào instance sau khi login thành công
    if (data.access_token) {
      this.setToken(data.access_token);
    }
    return data;
  }

  async register(name, email, password, password_confirmation, device_name) {
    return this._request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(name, email, password, password_confirmation, device_name),
    });
  }

  async verifyOtp(email, otp) {
    const data = await this._request('/auth/verify-otp', {
      method: 'POST',
      body: JSON.stringify({ email, otp }),
    });
    
    if (data.access_token) {
      this.setToken(data.access_token);
    }
    return data;
  }

  async logout() {
    // Cần token để logout
    return this._request('/auth/logout', { method: 'POST' });
  }

  async refreshToken(token) {
    return this._request('/auth/refresh-token', {
        method: 'POST',
        body: JSON.stringify({ token })
    });
  }

  
  static async login(email, password) {
    return AuthService.instance.login(email, password);
  }

  static async register(name, email, password, password_confirmation, device_name) {
    return AuthService.instance.register(name, email, password, password_confirmation, device_name);
  }

  static async verifyOtp(email, otp) {
    return AuthService.instance.verifyOtp(email, otp);
  }

  static async logout() {
    return AuthService.instance.logout();
  }
}

export default AuthService;