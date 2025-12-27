import { useState, useCallback } from 'react';
import PermissionService from '@/services/admin/PermissionService';

export const usePermission = () => {
    const [allPermissions, setAllPermissions] = useState([]);
    const [loading, setLoading] = useState(false);

    const fetchAllPermissions = useCallback(async () => {
        setLoading(true);
        try {
            // Lấy tất cả permission (truyền limit lớn để tránh phân trang)
            const response = await PermissionService.getAll({ per_page: 9999 }); 
            if (response.success) {
                const data = Array.isArray(response.data) ? response.data : (response.data?.data || []);
                setAllPermissions(data);
            }
        } catch (err) {
            console.error('Error fetching permissions:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    return { allPermissions, loading, fetchAllPermissions };
};