import { useState, useCallback } from 'react';
import AddressService from '@/services/customer/AddressService';

export const useAddress = () => {
  const [addresses, setAddresses] = useState([]);
  const [addressDetail, setAddressDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // --- 1. LẤY DANH SÁCH ---
  const fetchAddresses = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await AddressService.getAddresses();
      if (Array.isArray(data)) {
        setAddresses(data);
      } else {
        setAddresses([]);
      }
    } catch (err) {
      setError(err.message || 'Lỗi tải danh sách địa chỉ');
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 2. LẤY CHI TIẾT ---
  const getAddressDetail = useCallback(async (uuid) => {
    setLoading(true);
    try {
      const data = await AddressService.getAddressDetail(uuid);
      setAddressDetail(data);
      return data;
    } catch (err) {
      setError(err.message || 'Lỗi tải chi tiết địa chỉ');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 3. THÊM MỚI ---
  const createAddress = useCallback(async (payload) => {
    setLoading(true);
    try {
      const newAddress = await AddressService.createAddress(payload);
      // Option A: Thêm thẳng vào list hiện tại (Nhanh)
      setAddresses(prev => [newAddress, ...prev]); 
      // Option B: Gọi lại fetchAddresses() để đồng bộ server (An toàn hơn nếu có sort/filter)
      // await fetchAddresses();
      return newAddress;
    } catch (err) {
      setError(err.message || 'Lỗi thêm địa chỉ');
      throw err; // Ném lỗi để Component xử lý (vd: hiện thông báo)
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 4. CẬP NHẬT ---
  const updateAddress = useCallback(async (uuid, payload) => {
    setLoading(true);
    try {
      const updated = await AddressService.updateAddress(uuid, payload);
      
      // Cập nhật list local
      setAddresses(prev => prev.map(addr => addr.uuid === uuid ? updated : addr));
      return updated;
    } catch (err) {
      setError(err.message || 'Lỗi cập nhật địa chỉ');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 5. XÓA ---
  const deleteAddress = useCallback(async (uuid) => {
    setLoading(true);
    try {
      await AddressService.deleteAddress(uuid);
      
      // Xóa khỏi list local ngay lập tức
      setAddresses(prev => prev.filter(addr => addr.uuid !== uuid));
    } catch (err) {
      setError(err.message || 'Lỗi xóa địa chỉ');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    addresses,
    addressDetail,
    loading,
    error,
    fetchAddresses,
    getAddressDetail,
    createAddress,
    updateAddress,
    deleteAddress
  };
};