import { useState, useCallback } from 'react';
import OrderService from '@/services/admin/OrderService';

export const useOrder = () => {
    const [loading, setLoading] = useState(false);
    const [orders, setOrders] = useState([]);
    const [order, setOrder] = useState(null);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [error, setError] = useState(null);

    const fetchOrders = useCallback(async (params = {}) => {
        setLoading(true);
        setError(null);
        try {
            // Lọc bỏ các params rỗng/null/undefined trước khi gửi
            const cleanParams = Object.fromEntries(
                Object.entries(params).filter(([_, v]) => v !== null && v !== undefined && v !== '')
            );

            const res = await OrderService.getOrders(cleanParams);
            
            if (res.success) {
                setOrders(Array.isArray(res.data) ? res.data : []);
                if (res.meta) {
                    setPagination({
                        current_page: res.meta.current_page,
                        last_page: res.meta.last_page,
                        total: res.meta.total,
                        per_page: res.meta.per_page
                    });
                }
            } else {
                setOrders([]);
            }
        } catch (err) {
            console.error("Fetch Orders Error:", err);
            setError(err.message || 'Failed to fetch orders');
            setOrders([]); 
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchOrderDetail = useCallback(async (uuid) => {
        setLoading(true);
        setError(null);
        try {
            const res = await OrderService.getOrder(uuid);
            if (res.success) {
                setOrder(res.data);
            }
        } catch (err) {
            setError(err.message || 'Failed to fetch order detail');
        } finally {
            setLoading(false);
        }
    }, []);

    const updateOrderStatus = async (uuid, status) => {
        setLoading(true);
        try {
            const res = await OrderService.updateStatus(uuid, status);
            return res; 
        } catch (err) {
            return { success: false, message: err.message };
        } finally {
            setLoading(false);
        }
    };

    return {
        loading,
        error,
        orders,
        order,
        pagination,
        fetchOrders,
        fetchOrderDetail,
        updateOrderStatus
    };
};