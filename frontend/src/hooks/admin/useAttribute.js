import { useState, useCallback } from 'react';
import AttributeService from '@/services/admin/AttributeService';

export const useAttribute = () => {
    const [loading, setLoading] = useState(false);
    const [attributes, setAttributes] = useState([]);
    
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0
    });

    const fetchAttributes = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            // Lấy 100 dòng để dropdown hiển thị đủ
            const response = await AttributeService.getAll({ ...params, per_page: 100 });
            
            const dataList = Array.isArray(response.data) ? response.data : (response.data?.data || []);
            setAttributes(dataList);

            if (response.meta) {
                setPagination({
                    current_page: response.meta.current_page,
                    last_page: response.meta.last_page,
                    total: response.meta.total
                });
            }
            
            // [QUAN TRỌNG] Return data để component sử dụng ngay (ví dụ trong Promise.all)
            return dataList;

        } catch (err) {
            console.error("Fetch Attributes Error:", err);
            return [];
        } finally {
            setLoading(false);
        }
    }, []);

    const createAttribute = async (data) => {
        try {
            const res = await AttributeService.create(data);
            return res.data;
        } catch (error) { throw error; }
    };

    return { 
        loading, 
        attributes, 
        pagination, 
        fetchAttributes,
        createAttribute 
    };
};