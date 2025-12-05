import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Plus,
    Search,
    Edit2,
    Trash2,
    Shield,
    Loader,
    AlertCircle,
    Key
} from 'lucide-react';
import PermissionService from '@/services/admin/PermissionService';
import Modal from '@/components/admin/shared/Modal';
import ConfirmDialog from '@/components/admin/shared/ConfirmDialog';
import './PermissionList.css';

const PermissionList = () => {
    const [permissions, setPermissions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [searchTerm, setSearchTerm] = useState('');

    // Modal states
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);
    const [selectedPermission, setSelectedPermission] = useState(null);
    const [formData, setFormData] = useState({ name: '', description: '' });
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        fetchPermissions();
    }, []);

    const fetchPermissions = async () => {
        try {
            setLoading(true);
            const response = await PermissionService.getAll();
            // Handle paginated response
            const permsData = response.data?.data || response.data || [];
            setPermissions(Array.isArray(permsData) ? permsData : []);
        } catch (err) {
            setError('Không thể tải danh sách quyền');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleOpenCreate = () => {
        setSelectedPermission(null);
        setFormData({ name: '', description: '' });
        setIsModalOpen(true);
    };

    const handleOpenEdit = (permission) => {
        setSelectedPermission(permission);
        setFormData({
            name: permission.name || '',
            description: permission.description || ''
        });
        setIsModalOpen(true);
    };

    const handleOpenDelete = (permission) => {
        setSelectedPermission(permission);
        setIsDeleteOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (selectedPermission) {
                await PermissionService.update(selectedPermission.uuid, formData);
            } else {
                await PermissionService.create(formData);
            }
            setIsModalOpen(false);
            fetchPermissions();
        } catch (err) {
            setError(err.message || 'Có lỗi xảy ra');
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async () => {
        try {
            await PermissionService.delete(selectedPermission.uuid);
            setIsDeleteOpen(false);
            fetchPermissions();
        } catch (err) {
            setError(err.message || 'Không thể xóa quyền');
        }
    };

    const filteredPermissions = permissions.filter(p =>
        p.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.description?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Group permissions by module
    const groupedPermissions = filteredPermissions.reduce((acc, perm) => {
        const module = perm.name?.split('.')[0] || 'other';
        if (!acc[module]) acc[module] = [];
        acc[module].push(perm);
        return acc;
    }, {});

    if (loading) {
        return (
            <div className="permission_loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="permission_list-page">
            {/* Header */}
            <div className="permission_page-header">
                <div className="permission_page-title">
                    <h1>Quản lý Quyền</h1>
                    <p className="permission_page-subtitle">Quản lý các quyền hạn trong hệ thống</p>
                </div>
                <button className="permission_btn-add" onClick={handleOpenCreate}>
                    <Plus size={20} />
                    Thêm quyền
                </button>
            </div>

            {/* Error */}
            {error && (
                <div className="permission_error-alert">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                    <button onClick={() => setError('')}>×</button>
                </div>
            )}

            {/* Search */}
            <div className="permission_search-filters">
                <div className="permission_search-box">
                    <Search size={20} className="permission_search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm quyền..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* Permissions Grid by Module */}
            <div className="permissions_grid">
                {Object.keys(groupedPermissions).length === 0 ? (
                    <div className="permission_empty-state">
                        <Shield size={64} />
                        <h3>Chưa có quyền nào</h3>
                        <p>Bắt đầu bằng cách thêm quyền mới</p>
                    </div>
                ) : (
                    Object.entries(groupedPermissions).map(([module, perms]) => (
                        <div key={module} className="permission_group">
                            <div className="permission_group-header">
                                <Key size={18} />
                                <h3>{module.charAt(0).toUpperCase() + module.slice(1)}</h3>
                                <span className="count">{perms.length}</span>
                            </div>
                            <div className="permission_group-content">
                                {perms.map((permission) => (
                                    <div key={permission.uuid} className="permission_item">
                                        <div className="permission_info">
                                            <span className="permission_name">{permission.name}</span>
                                            {permission.description && (
                                                <span className="permission_desc">{permission.description}</span>
                                            )}
                                        </div>
                                        <div className="permission_actions">
                                            <button
                                                className="permission_action-btn permission_btn-edit"
                                                onClick={() => handleOpenEdit(permission)}
                                                title="Sửa"
                                            >
                                                <Edit2 size={16} />
                                            </button>
                                            <button
                                                className="permission_action-btn permission_btn-delete"
                                                onClick={() => handleOpenDelete(permission)}
                                                title="Xóa"
                                            >
                                                <Trash2 size={16} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Create/Edit Modal */}
            <Modal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                title={selectedPermission ? 'Sửa quyền' : 'Thêm quyền mới'}
                size="sm"
            >
                <form onSubmit={handleSubmit}>
                    <div className="permission_form-group">
                        <label>Tên quyền *</label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            placeholder="vd: product.create"
                            required
                        />
                        <span className="permission_help-text">Format: module.action (vd: order.view)</span>
                    </div>
                    <div className="permission_form-group">
                        <label>Mô tả</label>
                        <textarea
                            value={formData.description}
                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            placeholder="Mô tả quyền..."
                            rows={3}
                        />
                    </div>
                    <div className="permission_modal-footer">
                        <button type="button" className="permission_modal-btn permission_modal-btn-secondary" onClick={() => setIsModalOpen(false)}>
                            Hủy
                        </button>
                        <button type="submit" className="permission_modal-btn permission_modal-btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : (selectedPermission ? 'Cập nhật' : 'Tạo mới')}
                        </button>
                    </div>
                </form>
            </Modal>

            {/* Delete Confirmation */}
            <ConfirmDialog
                isOpen={isDeleteOpen}
                onClose={() => setIsDeleteOpen(false)}
                onConfirm={handleDelete}
                title="Xác nhận xóa"
                message={`Bạn có chắc muốn xóa quyền "${selectedPermission?.name}"?`}
                type="danger"
            />
        </div>
    );
};

export default PermissionList;
