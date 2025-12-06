import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Plus, Search, Filter, Edit, Trash2, Eye, UserCircle } from 'lucide-react';
import UserService from '@/services/admin/UserService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './UserList.css';

const UserList = () => {
    const navigate = useNavigate();
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({
        isOpen: false,
        title: '',
        message: '',
        onConfirm: null,
        isLoading: false
    });
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');

    const fetchUsers = async () => {
        try {
            setLoading(true);
            setError(null);

            const params = {};
            if (searchTerm) params.search = searchTerm;
            if (filterStatus !== 'all') params.is_active = filterStatus === 'active';

            const response = await UserService.getUsers(params);

            if (response.success && response.data) {
                // Check if response.data is paginated (has .data property) or is direct array
                const usersData = Array.isArray(response.data) ? response.data : (response.data.data || []);
                setUsers(usersData);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải danh sách người dùng');
            console.error('Error fetching users:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, [filterStatus]);

    const handleSearch = (e) => {
        e.preventDefault();
        fetchUsers();
    };

    const handleDelete = (uuid, name) => {
        setConfirmDialog({
            isOpen: true,
            title: 'Xóa người dùng',
            message: `Bạn có chắc muốn xóa người dùng "${name}"? Hành động này không thể hoàn tác.`,
            onConfirm: async () => {
                setConfirmDialog(prev => ({ ...prev, isLoading: true }));
                try {
                    await UserService.deleteUser(uuid);
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    fetchUsers();
                } catch (err) {
                    setConfirmDialog(prev => ({ ...prev, isOpen: false }));
                    alert('Lỗi khi xóa người dùng: ' + err.message);
                }
            }
        });
    };

    return (
        <div className="user_list">
            <div className="page-header">
                <div>
                    <h1>Quản lý Người dùng</h1>
                    <p className="page-subtitle">Danh sách tất cả người dùng trong hệ thống</p>
                </div>
                <button
                    className="btn-primary-user"
                    onClick={() => navigate('/admin/users/create')}
                >
                    <Plus size={20} />
                    Thêm người dùng
                </button>
            </div>

            {/* Filters & Search */}
            <div className="filters-bar">
                <form onSubmit={handleSearch} className="search-form">
                    <div className="search-input-group">
                        <Search size={18} />
                        <input
                            type="text"
                            placeholder="Tìm kiếm theo tên hoặc email..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                    <button type="submit" className="btn btn-secondary">
                        Tìm kiếm
                    </button>
                </form>

                <div className="filter-group">
                    <Filter size={18} />
                    <select
                        value={filterStatus}
                        onChange={(e) => setFilterStatus(e.target.value)}
                        className="filter-select"
                    >
                        <option value="all">Tất cả trạng thái</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Đã khóa</option>
                    </select>
                </div>
            </div>

            {/* Table */}
            <div className="table-container">
                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải dữ liệu...</p>
                    </div>
                ) : error ? (
                    <div className="error-state">
                        <p>{error}</p>
                        <button onClick={fetchUsers} className="btn btn-secondary">
                            Thử lại
                        </button>
                    </div>
                ) : users.length === 0 ? (
                    <div className="empty-state">
                        <UserCircle size={48} color="#9ca3af" />
                        <p>Không tìm thấy người dùng nào</p>
                    </div>
                ) : (
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Tên người dùng</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th className="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.uuid}>
                                    <td>
                                        <strong>{user.name}</strong>
                                    </td>
                                    <td>{user.email}</td>
                                    <td>{user.phone || '-'}</td>
                                    <td>
                                        {user.roles && user.roles.length > 0 ? (
                                            user.roles.map(role => (
                                                <span key={role.id} className="badge badge-info" style={{ marginRight: '4px' }}>
                                                    {role.name}
                                                </span>
                                            ))
                                        ) : (
                                            <span className="badge badge-secondary">User</span>
                                        )}
                                    </td>
                                    <td>
                                        <span className={`badge ${user.is_active ? 'badge-success' : 'badge-danger'}`}>
                                            {user.is_active ? 'Hoạt động' : 'Đã khóa'}
                                        </span>
                                    </td>
                                    <td>
                                        {user.created_at
                                            ? new Date(user.created_at).toLocaleDateString('vi-VN')
                                            : '-'
                                        }
                                    </td>
                                    <td className="text-right">
                                        <div className="action-buttons">
                                            <button
                                                className="btn-icon"
                                                onClick={() => navigate(`/admin/users/${user.uuid}`)}
                                                title="Xem chi tiết"
                                            >
                                                <Eye size={16} />
                                            </button>
                                            <button
                                                className="btn-icon"
                                                onClick={() => navigate(`/admin/users/${user.uuid}/edit`)}
                                                title="Chỉnh sửa"
                                            >
                                                <Edit size={16} />
                                            </button>
                                            <button
                                                className="btn-icon btn-danger"
                                                onClick={() => handleDelete(user.uuid, user.name)}
                                                title="Xóa"
                                            >
                                                <Trash2 size={16} />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>


            <ConfirmDialog
                isOpen={confirmDialog.isOpen}
                onClose={() => setConfirmDialog(prev => ({ ...prev, isOpen: false }))}
                onConfirm={confirmDialog.onConfirm}
                title={confirmDialog.title}
                message={confirmDialog.message}
                isLoading={confirmDialog.isLoading}
            />
        </div >
    );
};

export default UserList;
