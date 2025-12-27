import { useState, useCallback } from 'react';
import UserService from '@/services/admin/UserService';

export const useUser = () => {
    // State cho danh sách
    const [users, setUsers] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    
    // State cho chi tiết
    const [user, setUser] = useState(null);
    
    // State chung
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // 1. Lấy danh sách User (có phân trang & filter)
    const fetchUsers = useCallback(async (params = {}) => {
        setLoading(true);
        setError(null);
        try {
            const response = await UserService.getUsers(params);
            if (response.success) {
                // API trả về data là mảng user
                setUsers(response.data || []);
                // API trả về meta là thông tin phân trang
                if (response.meta) {
                    setPagination(response.meta);
                }
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách người dùng');
            console.error('Fetch Users Error:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    // 2. Lấy chi tiết User
    const fetchUser = useCallback(async (uuid) => {
        setLoading(true);
        setError(null);
        try {
            const response = await UserService.getUser(uuid);
            if (response.success) {
                setUser(response.data);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải thông tin người dùng');
            console.error('Fetch User Detail Error:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    // 3. Tạo User mới
    const createUser = async (data) => {
        setLoading(true);
        setError(null);
        try {
            const response = await UserService.createUser(data);
            return response;
        } catch (err) {
            setError(err.message || 'Lỗi khi tạo người dùng');
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // 4. Cập nhật User
    const updateUser = async (uuid, data) => {
        setLoading(true);
        setError(null);
        try {
            const response = await UserService.updateUser(uuid, data);
            return response;
        } catch (err) {
            setError(err.message || 'Lỗi khi cập nhật người dùng');
            throw err;
        } finally {
            setLoading(false);
        }
    };

    // 5. Xóa User
    const deleteUser = async (uuid) => {
        setLoading(true);
        try {
            await UserService.deleteUser(uuid);
            return true;
        } catch (err) {
            setError(err.message || 'Lỗi khi xóa người dùng');
            throw err;
        } finally {
            setLoading(false);
        }
    };

    return {
        users,
        user,
        pagination,
        loading,
        error,
        fetchUsers,
        fetchUser,
        createUser,
        updateUser,
        deleteUser
    };
};