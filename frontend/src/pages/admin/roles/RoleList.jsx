import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    Shield, Plus, Edit, Trash2, Search, Users, Key,
    AlertCircle, RefreshCw, ChevronDown, ChevronUp
} from 'lucide-react';
import RoleService from '@/services/admin/RoleService';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './RoleManagement.css';

const RoleList = () => {
    const navigate = useNavigate();
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [expandedRole, setExpandedRole] = useState(null);
    const [deleteConfirm, setDeleteConfirm] = useState({ show: false, item: null });
    const [deleting, setDeleting] = useState(false);

    useEffect(() => {
        fetchRoles();
    }, []);

    const fetchRoles = async () => {
        try {
            setLoading(true);
            const response = await RoleService.getRoles();
            // Handle paginated response
            const rolesData = response.data?.data || response.data || [];
            setRoles(Array.isArray(rolesData) ? rolesData : []);
        } catch (err) {
            setError('Không thể tải danh sách vai trò');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!deleteConfirm.item) return;

        setDeleting(true);
        try {
            await RoleService.deleteRole(deleteConfirm.item.uuid);
            setDeleteConfirm({ show: false, item: null });
            fetchRoles();
        } catch (err) {
            setError('Không thể xóa vai trò');
        } finally {
            setDeleting(false);
        }
    };

    const toggleExpand = (uuid) => {
        setExpandedRole(expandedRole === uuid ? null : uuid);
    };

    const groupPermissionsByModule = (permissions) => {
        const groups = {};
        permissions?.forEach(perm => {
            const module = perm.module || perm.name.split('.')[0];
            if (!groups[module]) {
                groups[module] = [];
            }
            groups[module].push(perm);
        });
        return groups;
    };

    return (
        <div className="role_management">
            {/* Header */}
            <div className="role_page-header">
                <div className="role_header-content">
                    <h1>
                        <Shield size={28} />
                        Quản lý vai trò & phân quyền
                    </h1>
                    <p>{roles.length} vai trò trong hệ thống</p>
                </div>
                <button onClick={() => navigate('/admin/roles/create')} className="role_btn role_btn-primary">
                    <Plus size={20} />
                    Thêm vai trò
                </button>
            </div>

            {error && (
                <div className="role_alert role_alert-error">
                    <AlertCircle size={20} />
                    {error}
                    <button onClick={() => setError(null)} className="role_alert-close">&times;</button>
                </div>
            )}

            {/* Roles List */}
            <div className="roles_container">
                {loading ? (
                    <div className="role_loading-state">
                        <div className="spinner"></div>
                        <p>Đang tải...</p>
                    </div>
                ) : roles.length === 0 ? (
                    <div className="role_empty-state">
                        <Shield size={48} />
                        <h3>Chưa có vai trò nào</h3>
                        <button onClick={() => navigate('/admin/roles/create')} className="role_btn role_btn-primary">
                            <Plus size={16} />
                            Tạo vai trò đầu tiên
                        </button>
                    </div>
                ) : (
                    <div className="roles_list">
                        {roles.map(role => {
                            const isExpanded = expandedRole === role.uuid;
                            const permGroups = groupPermissionsByModule(role.permissions);

                            return (
                                <div key={role.uuid} className={`role_card ${isExpanded ? 'expanded' : ''}`}>
                                    <div className="role_header" onClick={() => toggleExpand(role.uuid)}>
                                        <div className="role_info">
                                            <div className="role_icon">
                                                <Shield size={20} />
                                            </div>
                                            <div className="role_details">
                                                <h3>{role.name}</h3>
                                                <p>{role.description || 'Không có mô tả'}</p>
                                            </div>
                                        </div>

                                        <div className="role_meta">
                                            <span className="role_meta-item">
                                                <Key size={14} />
                                                {role.permissions?.length || 0} quyền
                                            </span>
                                            <span className="role_meta-item">
                                                <Users size={14} />
                                                {role.users_count || 0} người dùng
                                            </span>
                                            {role.is_system && (
                                                <span className="role_badge role_badge-system">Hệ thống</span>
                                            )}
                                        </div>

                                        <div className="role_actions">
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    navigate(`/admin/roles/${role.uuid}/edit`);
                                                }}
                                                className="role_btn-icon role_btn-edit"
                                                title="Sửa"
                                            >
                                                <Edit size={16} />
                                            </button>
                                            {!role.is_system && (
                                                <button
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        setDeleteConfirm({ show: true, item: role });
                                                    }}
                                                    className="role_btn-icon role_btn-delete"
                                                    title="Xóa"
                                                >
                                                    <Trash2 size={16} />
                                                </button>
                                            )}
                                            <button className="role_btn-icon role_btn-expand">
                                                {isExpanded ? <ChevronUp size={16} /> : <ChevronDown size={16} />}
                                            </button>
                                        </div>
                                    </div>

                                    {isExpanded && (
                                        <div className="role_permissions">
                                            <h4>Danh sách quyền hạn</h4>
                                            {Object.keys(permGroups).length === 0 ? (
                                                <p className="no-permissions">Vai trò này chưa có quyền nào</p>
                                            ) : (
                                                <div className="permissions-grid">
                                                    {Object.entries(permGroups).map(([module, perms]) => (
                                                        <div key={module} className="role_permission-group">
                                                            <h5>{module}</h5>
                                                            <div className="role_role_permission-tags">
                                                                {perms.map(perm => (
                                                                    <span key={perm.id} className="role_permission-tag">
                                                                        {perm.name.split('.')[1] || perm.name}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* Delete Confirmation */}
            <ConfirmDialog
                isOpen={deleteConfirm.show}
                onClose={() => setDeleteConfirm({ show: false, item: null })}
                onConfirm={handleDelete}
                title="Xác nhận xóa vai trò"
                message={`Bạn có chắc muốn xóa vai trò "${deleteConfirm.item?.name}"? Hành động này không thể hoàn tác.`}
                confirmText="Xóa"
                type="danger"
                loading={deleting}
            />
        </div>
    );
};

export default RoleList;
