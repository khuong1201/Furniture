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

  // 👇 Đã sửa đổi hàm này để xử lý Refresh Token
  async _request(endpoint, options = {}, isRetry = false) {
    try {
      const url = `${this.baseUrl}${endpoint}`;
      const config = {
        ...options,
        headers: {
          ...this.headers,
          ...options.headers,
        },
      };

      let response = await fetch(url, config);
      
      // Xử lý trường hợp 401 (Unauthorized)
      if (response.status === 401 && !isRetry) {
        // Tránh loop vô tận: Nếu đang gọi refresh mà lỗi thì không retry nữa
        if (endpoint === '/auth/refresh' || endpoint === '/auth/login') {
            throw new Error('Phiên đăng nhập hết hạn');
        }

        console.log('🔄 Token hết hạn, đang thử Refresh Token...');
        
        try {
            // 1. Gọi refresh token
            const refreshData = await this.refreshToken();
            const newAccessToken = refreshData.data.access_token; // Cấu trúc tùy API trả về

            // 2. Lưu token mới vào LocalStorage và instance
            localStorage.setItem('access_token', newAccessToken);
            this.setToken(newAccessToken);

            // 3. Cập nhật header cho request hiện tại
            config.headers['Authorization'] = `Bearer ${newAccessToken}`;

            // 4. GỌI LẠI request cũ (Retry)
            console.log('✅ Refresh thành công, gửi lại request cũ...');
            response = await fetch(url, config);

        } catch (refreshError) {
            console.error('❌ Refresh Token thất bại:', refreshError);
            // Nếu refresh thất bại thì logout luôn
            this.logout();
            window.location.href = '/login';
            throw refreshError;
        }
      }

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
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('user_info');
    
    return this._request('/auth/logout', {
      method: 'POST',
      body: JSON.stringify({ refresh_token }),
    });
  }

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
}

export default AuthService;