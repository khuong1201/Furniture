import { useState, useCallback } from 'react';
import PromotionService from '@/services/admin/PromotionService';

export const usePromotion = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [promotions, setPromotions] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0
    });

    // Lấy danh sách (có phân trang & search)
    const fetchPromotions = useCallback(async (params = {}) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PromotionService.getAll(params);
            // Support cả cấu trúc { data: [...], meta: {...} } hoặc mảng trực tiếp
            const data = response.data?.data || response.data || [];
            setPromotions(data);
            
            if (response.data?.meta) {
                setPagination(prev => ({ ...prev, ...response.data.meta }));
            }
        } catch (err) {
            console.error(err);
            setError(err.message || 'Lỗi tải danh sách khuyến mãi');
        } finally {
            setLoading(false);
        }
    }, []);

    // Lấy chi tiết 1 khuyến mãi
    const getPromotion = useCallback(async (uuid) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PromotionService.getById(uuid);
            return response.data;
        } catch (err) {
            setError(err.message || 'Lỗi tải chi tiết khuyến mãi');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    // Tạo mới
    const createPromotion = useCallback(async (data) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PromotionService.create(data);
            return response.data;
        } catch (err) {
            setError(err.message || 'Lỗi tạo khuyến mãi');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    // Cập nhật
    const updatePromotion = useCallback(async (uuid, data) => {
        setLoading(true);
        setError(null);
        try {
            const response = await PromotionService.update(uuid, data);
            return response.data;
        } catch (err) {
            setError(err.message || 'Lỗi cập nhật khuyến mãi');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    // Xóa
    const deletePromotion = useCallback(async (uuid) => {
        setLoading(true);
        setError(null);
        try {
            await PromotionService.delete(uuid);
            setPromotions(prev => prev.filter(p => p.uuid !== uuid));
        } catch (err) {
            setError(err.message || 'Lỗi xóa khuyến mãi');
            throw err;
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        loading,
        error,
        promotions,
        pagination,
        fetchPromotions,
        getPromotion,
        createPromotion,
        updatePromotion,
        deletePromotion
    };
};