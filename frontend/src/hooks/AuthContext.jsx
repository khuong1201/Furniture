import React, { createContext, useState, useEffect, useContext } from 'react';
import AuthService from '@/services/customer/AuthService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // 1. Check user khi F5 trang
  useEffect(() => {
    const initAuth = async () => {
      const token = localStorage.getItem('access_token');
      if (token) {
        AuthService.instance.setToken(token);
        try {
          // Lấy user từ localStorage để hiển thị ngay lập tức (cho nhanh)
          const storedUser = JSON.parse(localStorage.getItem('user_info'));
          if (storedUser) setUser(storedUser);
          
          // (Tùy chọn) Gọi API /me để chắc chắn token còn sống
          // await AuthService.getMe(); 
        } catch (err) {
          console.error("Token lỗi hoặc hết hạn", err);
          logout();
        }
      }
      setLoading(false);
    };
    initAuth();
  }, []);

  // 2. Hàm Login
  const login = async (email, password, device_name = 'web') => {
    setLoading(true);
    setError(null);
    try {
      // Gọi API
      const response = await AuthService.login(email, password, device_name || 'web');
      
      // ⚠️ Backend trả về: { success: true, data: { access_token, user, ... } }
      // Truy cập vào lớp .data
      const { access_token, refresh_token, user, roles } = response.data;

      if (!access_token) throw new Error('Không nhận được Access Token');

      // Lưu Storage
      localStorage.setItem('access_token', access_token);
      localStorage.setItem('refresh_token', refresh_token);
      
      const userInfo = { ...user, roles: roles || [] };
      localStorage.setItem('user_info', JSON.stringify(userInfo));

      // Cập nhật State & Service
      AuthService.instance.setToken(access_token);
      setUser(userInfo);

      console.log('✅ Login Success:', userInfo);
      return { success: true, user: userInfo };

    } catch (err) {
      console.error('Login Error:', err);
      // Lấy message từ API nếu có
      const msg = err.message || 'Đăng nhập thất bại';
      setError(msg);
      return { success: false, message: msg };
    } finally {
      setLoading(false);
    }
  };

  // 3. Hàm Register
  const register = async (payload) => {
    setLoading(true);
    setError(null);
    try {
      const response = await AuthService.register(payload);
      console.log('✅ Register Success:', response);
      return { success: true }; // Trả về object cho đồng bộ
    } catch (err) {
      const msg = err.message || 'Đăng ký thất bại';
      setError(msg);
      return { success: false, message: msg };
    } finally {
      setLoading(false);
    }
  };

  // 4. Hàm Logout
  const logout = async () => {
    try {
      await AuthService.logout();
    } catch (e) {
      console.log('Lỗi logout server, vẫn clear client');
    }
    
    // Xóa sạch mọi thứ
    AuthService.instance.setToken(null);
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token'); // 👈 Nhớ xóa cái này
    localStorage.removeItem('user_info');
    setUser(null);
  };

  const value = {
    user,
    loading,
    error,
    login,
    register,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => {
  return useContext(AuthContext);
};