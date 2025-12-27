import { useState, useCallback, useRef } from 'react';
import OrderService from '@/services/customer/OrderService';

export const useOrder = () => {
    const [orders, setOrders] = useState([]);        
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
    });
    const [orderDetail, setOrderDetail] = useState(null); 
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Dùng để hủy request cũ nếu user switch tab liên tục
    const abortControllerRef = useRef(null);

    // --- 1. GET ORDERS (Fix Race Condition & Append Logic) ---
    const getOrders = useCallback(async (params = {}) => {
        const isLoadMore = params.page > 1;

        // Nếu load trang 1 (hoặc filter mới), hủy request cũ đang chạy
        if (!isLoadMore && abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        abortControllerRef.current = new AbortController();
        const signal = abortControllerRef.current.signal;

        setLoading(true);
        setError(null);

        try {
            const response = await OrderService.getMyOrders(params, signal);
            
            // Lấy data từ response chuẩn Laravel Resource
            const dataList = response.data || [];
            const meta = response.meta || {}; 

            // ✅ FIX: Nối mảng nếu là Load More, ngược lại thì Replace
            setOrders(prev => isLoadMore ? [...prev, ...dataList] : dataList);

            // Set Pagination Info
            if (meta.current_page) {
                setPagination({
                    current_page: meta.current_page,
                    last_page: meta.last_page,
                    total: meta.total,
                    per_page: meta.per_page
                });
            }
            
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error('❌ Get orders failed:', err);
            setError(err.message || 'Failed to load orders');
            if (!isLoadMore) setOrders([]); // Clear list nếu lỗi trang 1
        } finally {
            // Chỉ tắt loading nếu request chưa bị hủy
            if (!signal?.aborted) setLoading(false);
        }
    }, []);

    // --- 2. GET ORDER DETAIL ---
    const getOrderDetail = useCallback(async (uuid) => {
        setLoading(true); 
        setError(null);
        try { 
            const data = await OrderService.getOrderDetail(uuid); 
            setOrderDetail(data); 
            return data;
        } catch(e) { 
            setError(e.message); 
            console.error(e);
        } finally { 
            setLoading(false); 
        }
    }, []);

    // --- 3. ACTIONS ---
    
    const checkout = useCallback(async (data) => {
        setLoading(true); 
        try { 
            return await OrderService.checkout(data); 
        } catch(e) { 
            setError(e.message); 
            throw e; // Ném lỗi để component UI hiển thị toast
        } finally { 
            setLoading(false); 
        }
    }, []);

    const buyNow = useCallback(async (data) => {
        setLoading(true); 
        try { 
            return await OrderService.buyNow(data); 
        } catch(e) { 
            setError(e.message); 
            throw e; 
        } finally { 
            setLoading(false); 
        }
    }, []);

    const cancelOrder = useCallback(async (uuid) => {
        setLoading(true); 
        try { 
            await OrderService.cancelOrder(uuid); 
            return true; 
        } catch(e) { 
            setError(e.message); 
            throw e; 
        } finally { 
            setLoading(false); 
        }
    }, []);

    return {
        orders, 
        setOrders, // Expose để component có thể clear thủ công nếu cần
        pagination, 
        orderDetail, 
        loading, 
        error,
        getOrders, 
        getOrderDetail, 
        checkout, 
        buyNow, 
        cancelOrder
    };
};