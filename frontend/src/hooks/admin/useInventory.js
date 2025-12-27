import { useState, useCallback } from 'react';
import InventoryService from '@/services/admin/InventoryService';

export const useInventory = () => {
    const [stocks, setStocks] = useState([]);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(false);

    // 1. Lấy danh sách (List)
    const fetchStocks = useCallback(async (params = {}) => {
        setLoading(true);
        try {
            const res = await InventoryService.getInventories(params);
            setStocks(res.data || []);
            setMeta(res.meta || {});
        } catch (err) {
            console.error("Fetch stocks error:", err);
            setStocks([]);
        } finally {
            setLoading(false);
        }
    }, []);

    // 2. Lấy chi tiết (Detail)
    const getInventory = useCallback(async (uuid) => {
        setLoading(true);
        try {
            const res = await InventoryService.getInventory(uuid);
            return res.data;
        } catch (err) {
            console.error("Fetch detail error:", err);
            return null;
        } finally {
            setLoading(false);
        }
    }, []);

    // 3. Điều chỉnh kho (+/-)
    const adjustStock = async (data) => {
        setLoading(true);
        try {
            return await InventoryService.adjustStock(data);
        } catch (err) {
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // --- [MỚI] 4. Kiểm kê (Set cứng số lượng) ---
    const upsertStock = async (data) => {
        setLoading(true);
        try {
            return await InventoryService.upsertStock(data);
        } catch (err) {
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // --- [MỚI] 5. Lấy Stats Dashboard ---
    const fetchDashboardStats = useCallback(async (warehouseUuid) => {
        try {
            const res = await InventoryService.getDashboardStats(warehouseUuid);
            return res.success ? res.data : null;
        } catch (err) {
            console.error("Fetch stats error:", err);
            return null;
        }
    }, []);

    // --- [MỚI] 6. Lấy Chart Dashboard ---
    const fetchMovementChart = useCallback(async (warehouseUuid, period, month, year) => {
        try {
            const res = await InventoryService.getMovementChart(warehouseUuid, period, month, year);
            return res.success ? res.data : [];
        } catch (err) {
            console.error("Fetch chart error:", err);
            return [];
        }
    }, []);

    return { 
        stocks, 
        meta, 
        loading, 
        fetchStocks, 
        getInventory, 
        adjustStock,
        upsertStock,         // Export
        fetchDashboardStats, // Export
        fetchMovementChart   // Export
    };
};