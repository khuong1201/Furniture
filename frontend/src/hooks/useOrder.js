import { useState, useCallback } from 'react';
import OrderService from '@/services/OrderService';

export const useOrder = () => {
  // ===============================
  // ========== STATE ==============
  // ===============================

  const [orders, setOrders] = useState([]);           // Danh sách đơn hàng
  const [orderDetail, setOrderDetail] = useState(null); // Chi tiết 1 đơn
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // ===============================
  // ✅ LẤY DANH SÁCH ĐƠN HÀNG
  // GET /orders
  // ===============================
  const getMyOrders = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await OrderService.getMyOrders();
      console.log('✅ Fetch orders success:', data);
      setOrders(data);
    } catch (err) {
      console.error('❌ Fetch orders error:', err);
      setError(err.message || 'Lỗi tải danh sách đơn hàng');
    } finally {
      setLoading(false);
    }
  }, []);

  // ===============================
  // ✅ LẤY CHI TIẾT ĐƠN HÀNG
  // GET /orders/{uuid}
  // ===============================
  const getOrderDetail = useCallback(async (uuid) => {
    setLoading(true);
    setError(null);
    try {
      const data = await OrderService.getOrderDetail(uuid);
      console.log('✅ Fetch order detail success:', data);
      setOrderDetail(data);
    } catch (err) {
      console.error('❌ Fetch order detail error:', err);
      setError(err.message || 'Lỗi tải chi tiết đơn hàng');
    } finally {
      setLoading(false);
    }
  }, []);

  // ===============================
  // ✅ CHECKOUT TỪ GIỎ HÀNG
  // POST /orders/checkout
  // ===============================
  const checkout = useCallback(async (payload) => {
    setLoading(true);
    setError(null);
    try {
      console.log('🚀 Checkout payload:', payload);

      const data = await OrderService.checkout(payload);

      console.log('✅ Checkout success:', data);

      return data; // thường component sẽ cần redirect → trả data ra ngoài
    } catch (err) {
      console.error('❌ Checkout error:', err);
      setError(err.message || 'Checkout thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // ===============================
  // ✅ TẠO ORDER THƯỜNG (custom)
  // POST /orders
  // ===============================
  const createOrder = useCallback(async (payload) => {
    setLoading(true);
    setError(null);
    try {
      console.log('🚀 Create order payload:', payload);

      const data = await OrderService.createOrder(payload);

      console.log('✅ Create order success:', data);

      return data;
    } catch (err) {
      console.error('❌ Create order error:', err);
      setError(err.message || 'Tạo đơn hàng thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // ===============================
  // ✅ HỦY ĐƠN HÀNG
  // POST /orders/{uuid}/cancel
  // ===============================
  const cancelOrder = useCallback(async (uuid) => {
    setLoading(true);
    setError(null);
    try {
      console.log('⚠️ Cancel order:', uuid);

      const data = await OrderService.cancelOrder(uuid);

      console.log('✅ Cancel order success:', data);

      return data;
    } catch (err) {
      console.error('❌ Cancel order error:', err);
      setError(err.message || 'Hủy đơn hàng thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // ===============================
  // ✅ PUBLIC API CHO COMPONENT
  // ===============================
  return {
    orders,         // Array danh sách đơn hàng
    orderDetail,    // Object chi tiết 1 đơn
    loading,        // Boolean loading
    error,          // Message lỗi

    getMyOrders,    // Lấy danh sách đơn
    getOrderDetail, // Lấy chi tiết đơn
    checkout,       // Checkout từ giỏ
    createOrder,    // Tạo order thường
    cancelOrder,    // Hủy đơn
  };
};
