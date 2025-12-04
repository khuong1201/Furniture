import React, { createContext, useState, useEffect, useContext } from 'react';
import AuthService from '@/services/AuthService';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true); // Loading lúc mới vào app (check token)
  const [error, setError] = useState(null);

  // 1. Check xem user đã đăng nhập chưa khi F5 trang
  useEffect(() => {
    const initAuth = async () => {
      const token = localStorage.getItem('access_token');
      if (token) {
        // Set token lại cho Service 
        AuthService.instance.setToken(token);
        try {
          // Gọi API lấy thông tin user (profile) nếu cần
          // Hoặc tạm thời lấy user từ localStorage nếu bạn có lưu
          const storedUser = JSON.parse(localStorage.getItem('user_info'));
          if (storedUser) setUser(storedUser);
        } catch (err) {
          console.error("Token hết hạn", err);
          logout();
        }
      }
      setLoading(false);
    };
    initAuth();
  }, []);

  // 2. Hàm Login (Gọi Service)
  const login = async (email, password, device_name) => {
    setLoading(true);
    setError(null);
    try {

      const data = await AuthService.login(email, password, device_name);

      // Lưu trữ
      localStorage.setItem('access_token', data.data.access_token);
      localStorage.setItem('refresh_token', data.data.refresh_token);

      // Lưu user info cùng với roles
      const userInfo = {
        ...data.data.user,
        roles: data.data.roles || []
      };
      localStorage.setItem('user_info', JSON.stringify(userInfo));

      console.log('🔑 Access Token:', data.data.access_token);
      console.log('👤 User Roles:', data.data.roles);
      console.log('✅Login success:', data);

      AuthService.instance.setToken(data.data.access_token);
      setUser(userInfo);

      return { success: true, user: userInfo };
    } catch (err) {
      setError(err.message || '❌Đăng nhập thất bại');
      return { success: false };
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

      console.log('✅Form Submitted:', response);
      return true;
    } catch (err) {
      setError(err.message || '❌ ký thất bại');
      return false;
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
    // Xóa sạch client
    AuthService.instance.setToken(null);
    localStorage.removeItem('access_token');
    localStorage.removeItem('user_info');
    setUser(null);
  };

  // Giá trị trả về cho các Component con dùng
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

// Hook nhỏ để các component gọi nhanh
export const useAuth = () => {
  return useContext(AuthContext);
};