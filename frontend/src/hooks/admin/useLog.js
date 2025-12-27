import { useState, useCallback } from 'react';
import LogService from '@/services/admin/LogService';

export const useLog = () => {
    const [logs, setLogs] = useState([]);
    const [selectedLog, setSelectedLog] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [meta, setMeta] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 20
    });

    // Lấy danh sách logs
    const fetchLogs = useCallback(async (params = {}) => {
        setLoading(true);
        setError(null);
        try {
            const response = await LogService.getAll(params);
            setLogs(response.data || []);
            setMeta(response.meta || {});
        } catch (err) {
            setError(err.message || 'Không thể tải nhật ký hoạt động');
            setLogs([]);
        } finally {
            setLoading(false);
        }
    }, []);

    // Lấy chi tiết log
    const fetchLogById = useCallback(async (uuid) => {
        setLoading(true);
        try {
            const response = await LogService.getById(uuid);
            setSelectedLog(response.data);
            return response.data;
        } catch (err) {
            setError(err.message || 'Không thể tải chi tiết');
            return null;
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        logs,
        selectedLog,
        meta,
        loading,
        error,
        fetchLogs,
        fetchLogById,
        setError,
        setSelectedLog 
    };
};