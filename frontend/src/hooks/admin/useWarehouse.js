import { useState, useCallback } from 'react';
import WarehouseService from '@/services/admin/WarehouseService';

export const useWarehouse = () => {
    const [warehouses, setWarehouses] = useState([]);
    const [loading, setLoading] = useState(false);

    const fetchWarehouses = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            const res = await WarehouseService.getWarehouses(params);
            const data = res.data || [];
            setWarehouses(data);
            
            // [QUAN TRỌNG] Return data
            return data;
        } catch (err) {
            console.error(err);
            return [];
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        warehouses,
        loading,
        fetchWarehouses,
    };
};