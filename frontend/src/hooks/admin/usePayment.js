import { useState, useCallback } from 'react';
import PaymentService from '@/services/admin/PaymentService';

export const usePayment = () => {
    const [payments, setPayments] = useState([]);
    const [payment, setPayment] = useState(null); 
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15,
        from: 0,
        to: 0
    });

    const fetchPayments = useCallback(async (params = {}) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PaymentService.getAll(params);
            
            // --- FIX LOGIC MAP DATA TỪ RESOURCE COLLECTION ---
            // Laravel Resource trả về: { data: [...], meta: { current_page: ... }, links: {...} }
            
            // 1. Lấy danh sách items
            const items = response.data || []; 
            setPayments(items);
            
            // 2. Lấy thông tin phân trang từ `meta`
            if (response.meta) {
                setPagination({
                    current_page: response.meta.current_page,
                    last_page: response.meta.last_page,
                    total: response.meta.total,
                    per_page: response.meta.per_page,
                    from: response.meta.from,
                    to: response.meta.to
                });
            } else {
                // Fallback nếu API chưa chuẩn Resource (hiếm gặp)
                setPagination(prev => ({ ...prev, total: items.length }));
            }

        } catch (err) {
            console.error('Fetch Payments Error:', err);
            setError(err.message || 'Không thể tải danh sách thanh toán.');
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchPaymentDetail = useCallback(async (uuid) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PaymentService.getById(uuid);
            // Resource Single trả về: { data: {...} } -> Lấy .data
            const data = response.data || response;
            setPayment(data);
            return data;
        } catch (err) {
            console.error('Fetch Payment Detail Error:', err);
            setError(err.message || 'Không thể tải chi tiết thanh toán.');
            return null;
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        payments,
        payment,
        loading,
        error,
        pagination,
        fetchPayments,
        fetchPaymentDetail,
        setPayment
    };
};