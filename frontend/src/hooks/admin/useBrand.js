import { useState, useCallback } from 'react';
import BrandService from '@/services/admin/BrandService';

export const useBrand = () => {
    const [loading, setLoading] = useState(false);
    const [brands, setBrands] = useState([]);
    
    // Khởi tạo state pagination mặc định
    const [pagination, setPagination] = useState({ 
        current_page: 1, 
        last_page: 1, 
        total: 0,
        per_page: 15
    });

    const fetchBrands = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            // Mặc định gọi trang 1, per_page 15
            const queryParams = { page: 1, per_page: 15, ...params };
            
            const res = await BrandService.instance.getBrands(queryParams);
            
            // LOGIC CHUẨN: Thay thế dữ liệu cũ bằng dữ liệu mới (cho Pagination)
            setBrands(res.data || []);
            
            if (res.meta) {
                setPagination({
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    total: res.meta.total,
                    per_page: res.meta.per_page
                });
            }
        } catch (error) {
            console.error("Lỗi fetch brands:", error);
            setBrands([]);
        } finally {
            setLoading(false);
        }
    }, []);

    const deleteBrand = async (uuid) => {
        setLoading(true);
        try {
            await BrandService.instance.deleteBrand(uuid);
            return true;
        } catch (error) {
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return { 
        brands, 
        pagination, 
        loading, 
        fetchBrands, 
        deleteBrand 
    };
};