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

  // ✅ Hàm request chung
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

      // 🛑 XỬ LÝ REFRESH TOKEN TỰ ĐỘNG
      // Logic: Nếu 401 VÀ chưa retry VÀ không phải đang login (sai pass)
      if (response.status === 401 && !isRetry && endpoint !== '/auth/login') {
        
        // Tránh loop vô tận
        if (endpoint === '/auth/refresh') {
            this.logout();
            throw new Error('Phiên đăng nhập hết hạn');
        }

        console.log('🔄 Token hết hạn, đang thử Refresh Token...');
        
        try {
            // Gọi hàm refreshToken bên dưới
            const refreshResponse = await this.refreshToken();
            
            // ⚠️ Backend trả về: { success: true, data: { access_token: "..." } }
            // Nên ta lấy token từ refreshResponse.data.access_token
            const newAccessToken = refreshResponse.data.access_token; 

            if (!newAccessToken) throw new Error('Không lấy được token mới');

            // Lưu token mới
            localStorage.setItem('access_token', newAccessToken);
            this.setToken(newAccessToken);

            // Gắn token mới vào header request cũ
            config.headers['Authorization'] = `Bearer ${newAccessToken}`;

            console.log('✅ Refresh thành công, gửi lại request cũ...');
            response = await fetch(url, config);

        } catch (refreshError) {
            console.error('❌ Refresh Token thất bại:', refreshError);
            this.logout();
            window.location.href = '/login'; 
            throw refreshError;
        }
      }

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || `Lỗi API: ${response.status}`);
      }

      // ⚠️ QUAN TRỌNG:
      // Với Auth, ta thường cần cả field 'success' hoặc 'message' để hiển thị UI
      // Nên ta trả về TOÀN BỘ result, thay vì chỉ result.data như CartService
      return result; 

    } catch (error) {
      console.error(`Auth Service Error (${endpoint}):`, error);
      throw error;
    }
  }

  // ================= APIs =================

  // ✅ Login: Khớp với LoginRequest (email, password, device_name)
  async login(email, password, device_name = 'web') {
    return this._request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password, device_name }),
    });
  }

  // ✅ Register: Khớp với RegisterRequest
  async register(payload) {
    // payload gồm: name, email, password, password_confirmation, device_name
    return this._request('/auth/register', {
      method: 'POST',
      body: JSON.stringify({
         ...payload,
         device_name: payload.device_name || 'web'
      }),
    });
  }

  // ✅ Refresh: Khớp với RefreshTokenRequest
  async refreshToken() {
    const refresh_token = localStorage.getItem('refresh_token');
    return this._request('/auth/refresh', {
      method: 'POST',
      body: JSON.stringify({ 
          refresh_token, 
          device_name: 'web' // BE cần device_name
      }),
    });
  }

  // ✅ Logout
  async logout() {
    const refresh_token = localStorage.getItem('refresh_token');
    
    // Xóa Client
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('user_info');
    this.setToken(null);
    
    // Gọi Server xóa token (Gửi bearer token trên header)
    return this._request('/auth/logout', {
      method: 'POST',
      body: JSON.stringify({ refresh_token }), // Gửi kèm cho chắc, dù BE dùng Bearer
    });
  }

  // ✅ Get Me
  async getMe() {
    return this._request('/auth/me', { method: 'GET' });
  }

  // Static Wrappers
  static login(email, password, device_name) { return AuthService.instance.login(email, password, device_name); }
  static register(payload) { return AuthService.instance.register(payload); }
  static refreshToken() { return AuthService.instance.refreshToken(); }
  static logout() { return AuthService.instance.logout(); }
  static getMe() { return AuthService.instance.getMe(); }
}

export default AuthService;