import { useState, useCallback } from 'react';
import OrderService from '@/services/customer/OrderService';

export const useOrder = () => {
  const [orders, setOrders] = useState([]);         
  const [pagination, setPagination] = useState(null); // ✅ Lưu info phân trang
  const [orderDetail, setOrderDetail] = useState(null); 
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);


  const createOrder = useCallback(async (payload) => {
    setLoading(true);
    setError(null);
    try {
      const data = await OrderService.createOrder(payload);
      return data;
    } catch (err) {
      console.error('❌ Create order error:', err);
      setError(err.message || 'Tạo đơn hàng thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // ✅ GET LIST (Có params)
  const getMyOrders = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const response = await OrderService.getMyOrders(params);
      
      // ⚠️ XỬ LÝ PHÂN TRANG CỦA LARAVEL
      // Response thường có dạng: { data: [...], current_page: 1, last_page: 5, ... }
      if (response && Array.isArray(response.data)) {
        setOrders(response.data); // Mảng đơn hàng nằm trong key 'data'
        setPagination({
            currentPage: response.current_page,
            lastPage: response.last_page,
            total: response.total,
            perPage: response.per_page
        });
      } else if (Array.isArray(response)) {
        // Fallback nếu API không phân trang
        setOrders(response);
      } else {
        setOrders([]);
      }
      
    } catch (err) {
      console.error('❌ Fetch orders error:', err);
      setError(err.message || 'Lỗi tải danh sách đơn hàng');
      setOrders([]);
    } finally {
      setLoading(false);
    }
  }, []);

  // ✅ CHECKOUT
  const checkout = useCallback(async (addressId, notes = '') => {
    setLoading(true);
    setError(null);
    try {
      const payload = { address_id: addressId, notes };
      const data = await OrderService.checkout(payload);
      return data; 
    } catch (err) {
      setError(err.message || 'Đặt hàng thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // ✅ GET DETAIL
  const getOrderDetail = useCallback(async (uuid) => {
    setLoading(true);
    setError(null);
    try {
      const data = await OrderService.getOrderDetail(uuid);
      setOrderDetail(data);
    } catch (err) {
      setError(err.message || 'Lỗi tải chi tiết đơn hàng');
    } finally {
      setLoading(false);
    }
  }, []);

  // ✅ CANCEL
  const cancelOrder = useCallback(async (uuid) => {
    if (!window.confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;
    
    setLoading(true);
    try {
      await OrderService.cancelOrder(uuid);
      // Reload lại detail hoặc list sau khi hủy
      // getOrderDetail(uuid); 
      return true;
    } catch (err) {
      setError(err.message || 'Hủy đơn thất bại');
      alert(err.message);
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
    cancelOrder
  };
};