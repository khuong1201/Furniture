import { useState, useCallback } from 'react';
import OrderService from '@/services/customer/OrderService';

export const useOrder = () => {
  const [orders, setOrders] = useState([]);         
  const [pagination, setPagination] = useState(null);
  const [orderDetail, setOrderDetail] = useState(null); 
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // --- 1. TẠO ĐƠN THỦ CÔNG ---
  const createOrder = useCallback(async (payload) => {
    console.log('🚀 [useOrder] createOrder called:', payload);
    setLoading(true);
    setError(null);
    try {
      const data = await OrderService.createOrder(payload);
      console.log('✅ [useOrder] createOrder success:', data);
      return data;
    } catch (err) {
      console.error('❌ [useOrder] createOrder failed:', err);
      setError(err.message || 'Tạo đơn hàng thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 2. LẤY DANH SÁCH ĐƠN HÀNG ---
  const getMyOrders = useCallback(async (params = {}) => {
    console.log('🚀 [useOrder] getMyOrders called with params:', params);
    setLoading(true);
    setError(null);
    try {
      const response = await OrderService.getMyOrders(params);
      console.log('✅ [useOrder] getMyOrders response:', response);

      // Xử lý phân trang
      if (response && Array.isArray(response.data)) {
        setOrders(response.data); 
        setPagination({
            currentPage: response.current_page || response.meta?.current_page,
            lastPage: response.last_page || response.meta?.last_page,
            total: response.total || response.meta?.total,
            perPage: response.per_page || response.meta?.per_page
        });
      } 
      // Trường hợp trả về mảng trực tiếp
      else if (Array.isArray(response)) {
        setOrders(response);
        setPagination(null);
      } 
      else {
        setOrders([]);
      }
      
    } catch (err) {
      console.error('❌ [useOrder] getMyOrders failed:', err);
      setError(err.message || 'Lỗi tải danh sách đơn hàng');
      setOrders([]);
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 3. CHECKOUT (TỪ GIỎ HÀNG) ---
  // ⚠️ QUAN TRỌNG: Đã sửa tham số thành 'payload' để nhận object đầy đủ
  const checkout = useCallback(async (payload) => {
    console.log('🚀 [useOrder] checkout called with payload:', payload);
    setLoading(true);
    setError(null);
    try {
      // payload cấu trúc: { address_id, notes, selected_item_uuids: [...] }
      const data = await OrderService.checkout(payload);
      console.log('✅ [useOrder] checkout success:', data);
      return data; 
    } catch (err) {
      console.error('❌ [useOrder] checkout failed:', err);
      setError(err.message || 'Đặt hàng thất bại'); 
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 4. BUY NOW (MUA NGAY) ---
  const buyNow = useCallback(async (payload) => {
    console.log('🚀 [useOrder] buyNow called with payload:', payload);
    setLoading(true);
    setError(null);
    try {
        const data = await OrderService.buyNow(payload);
        console.log('✅ [useOrder] buyNow success:', data);
        return data;
    } catch (err) {
        console.error('❌ [useOrder] buyNow failed:', err);
        setError(err.message || 'Mua ngay thất bại');
        throw err;
    } finally {
        setLoading(false);
    }
  }, []);

  // --- 5. LẤY CHI TIẾT ĐƠN HÀNG ---
  const getOrderDetail = useCallback(async (uuid) => {
    console.log('🚀 [useOrder] getOrderDetail called for UUID:', uuid);
    setLoading(true);
    setError(null);
    // setOrderDetail(null); // Optional: Clear data cũ nếu muốn hiện loading trắng trang
    try {
      const data = await OrderService.getOrderDetail(uuid);
      console.log('✅ [useOrder] getOrderDetail success:', data);
      setOrderDetail(data);
      return data;
    } catch (err) {
      console.error('❌ [useOrder] getOrderDetail failed:', err);
      setError(err.message || 'Lỗi tải chi tiết đơn hàng');
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 6. HỦY ĐƠN HÀNG ---
  const cancelOrder = useCallback(async (uuid) => {
    if (!window.confirm('Bạn có chắc muốn hủy đơn hàng này?')) return false;
    
    console.log('🚀 [useOrder] cancelOrder called for UUID:', uuid);
    setLoading(true);
    setError(null);
    try {
      await OrderService.cancelOrder(uuid);
      console.log('✅ [useOrder] cancelOrder success');
      return true; 
    } catch (err) {
      console.error('❌ [useOrder] cancelOrder failed:', err);
      const msg = err.message || 'Hủy đơn thất bại';
      setError(msg);
      alert(msg); 
      return false;
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    orders,
    pagination,
    orderDetail,
    loading,
    error,
    createOrder,
    getMyOrders,
    getOrderDetail,
    checkout,
    buyNow,     
    cancelOrder
  };
};