// hooks/usePayment.js
import { useState, useCallback } from 'react';
import PaymentService from '@/services/customer/PaymentService';

export const usePayment = () => {
  const [payments, setPayments] = useState([]);         
  const [pagination, setPagination] = useState(null);
  const [paymentDetail, setPaymentDetail] = useState(null);
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // --- 1. LẤY LỊCH SỬ GIAO DỊCH ---
  const fetchPayments = useCallback(async (params = {}) => {
    setLoading(true);
    setError(null);
    try {
      const response = await PaymentService.getPayments(params);
      
      // Xử lý phân trang (tương tự useOrder)
      if (response && Array.isArray(response.data)) {
        setPayments(response.data);
        setPagination({
            currentPage: response.current_page || response.meta?.current_page,
            lastPage: response.last_page || response.meta?.last_page,
            total: response.total || response.meta?.total,
            perPage: response.per_page || response.meta?.per_page
        });
      } else if (Array.isArray(response)) {
        setPayments(response);
      } else {
        setPayments([]);
      }
    } catch (err) {
      console.error('❌ Fetch payments error:', err);
      setError(err.message || 'Lỗi tải lịch sử giao dịch');
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 2. KHỞI TẠO THANH TOÁN (Quan trọng) ---
  const initiatePayment = useCallback(async (orderUuid, method) => {
    setLoading(true);
    setError(null);
    try {
      const payload = { 
        order_uuid: orderUuid, 
        method: method // 'cod', 'momo', 'vnpay'
      };
      
      console.log('🚀 Initiating payment:', payload);
      const result = await PaymentService.initiatePayment(payload);
      
      console.log('✅ Payment initiated:', result);
      
      // Backend thường trả về { payment_url: "..." } nếu là Momo/VNPay
      return result; 

    } catch (err) {
      console.error('❌ Payment initiation failed:', err);
      setError(err.message || 'Khởi tạo thanh toán thất bại');
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  // --- 3. LẤY CHI TIẾT GIAO DỊCH ---
  const getPaymentDetail = useCallback(async (uuid) => {
    setLoading(true);
    setError(null);
    try {
      const data = await PaymentService.getPaymentDetail(uuid);
      setPaymentDetail(data);
      return data;
    } catch (err) {
      setError(err.message || 'Lỗi tải chi tiết giao dịch');
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    payments,
    pagination,
    paymentDetail,
    loading,
    error,
    fetchPayments,
    initiatePayment,
    getPaymentDetail
  };
};