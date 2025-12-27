import { useState, useCallback } from 'react';
import UserService from '@/services/customer/UserService';

export const useUser = () => {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // --- 1. LẤY THÔNG TIN PROFILE ---
  const getProfile = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await UserService.getProfile();
      setProfile(data);
      return data;
    } catch (err) {
      setError(err.message || 'Lỗi tải thông tin cá nhân');
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 2. CẬP NHẬT PROFILE ---
  const updateProfile = useCallback(async (data) => {
    setLoading(true);
    setError(null);
    try {
      const updatedData = await UserService.updateProfile(data);
      setProfile(updatedData); // Cập nhật state nội bộ luôn để UI đổi ngay
      return updatedData;
    } catch (err) {
      setError(err.message || 'Lỗi cập nhật hồ sơ');
      throw err; // Ném lỗi ra để Component hiện alert
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 3. ĐỔI MẬT KHẨU ---
  const changePassword = useCallback(async (data) => {
    setLoading(true);
    setError(null);
    try {
      await UserService.changePassword(data);
      return true;
    } catch (err) {
      setError(err.message || 'Lỗi đổi mật khẩu');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    profile,
    loading,
    error,
    getProfile,
    updateProfile,
    changePassword,
  };
};