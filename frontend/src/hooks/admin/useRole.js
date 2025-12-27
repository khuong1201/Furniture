import { useState, useCallback } from 'react';
import RoleService from '@/services/admin/RoleService';

export const useRole = () => {
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Lấy tất cả roles (thường dùng cho dropdown chọn role)
    const fetchAllRoles = useCallback(async () => {
        setLoading(true);
        try {
            const response = await RoleService.getRoles({ per_page: 100 }); // Lấy nhiều để hiển thị hết
            if (response.success) {
                // Xử lý trường hợp API trả về phân trang hoặc mảng trực tiếp
                const roleData = Array.isArray(response.data) ? response.data : (response.data?.data || []);
                setRoles(roleData);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách vai trò');
            console.error('Fetch Roles Error:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    return {
        roles,
        loading,
        error,
        fetchAllRoles
    };
};