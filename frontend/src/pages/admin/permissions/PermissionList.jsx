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
import PermissionService from '@/services/PermissionService';
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
            setPermissions(response.data || []);
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
            <div className="loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="permission-list-page">
            {/* Header */}
            <div className="page-header">
                <div className="page-title">
                    <h1>Quản lý Quyền</h1>
                    <p className="page-subtitle">Quản lý các quyền hạn trong hệ thống</p>
                </div>
                <button className="btn-add" onClick={handleOpenCreate}>
                    <Plus size={20} />
                    Thêm quyền
                </button>
            </div>

            {/* Error */}
            {error && (
                <div className="error-alert">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                    <button onClick={() => setError('')}>×</button>
                </div>
            )}

            {/* Search */}
            <div className="search-filters">
                <div className="search-box">
                    <Search size={20} className="search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm quyền..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* Permissions Grid by Module */}
            <div className="permissions-grid">
                {Object.keys(groupedPermissions).length === 0 ? (
                    <div className="empty-state">
                        <Shield size={64} />
                        <h3>Chưa có quyền nào</h3>
                        <p>Bắt đầu bằng cách thêm quyền mới</p>
                    </div>
                ) : (
                    Object.entries(groupedPermissions).map(([module, perms]) => (
                        <div key={module} className="permission-group">
                            <div className="group-header">
                                <Key size={18} />
                                <h3>{module.charAt(0).toUpperCase() + module.slice(1)}</h3>
                                <span className="count">{perms.length}</span>
                            </div>
                            <div className="group-content">
                                {perms.map((permission) => (
                                    <div key={permission.uuid} className="permission-item">
                                        <div className="permission-info">
                                            <span className="permission-name">{permission.name}</span>
                                            {permission.description && (
                                                <span className="permission-desc">{permission.description}</span>
                                            )}
                                        </div>
                                        <div className="permission-actions">
                                            <button
                                                className="action-btn btn-edit"
                                                onClick={() => handleOpenEdit(permission)}
                                                title="Sửa"
                                            >
                                                <Edit2 size={16} />
                                            </button>
                                            <button
                                                className="action-btn btn-delete"
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
                    <div className="form-group">
                        <label>Tên quyền *</label>
                        <input
                            type="text"
                            value={formData.name}
                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                            placeholder="vd: product.create"
                            required
                        />
                        <span className="help-text">Format: module.action (vd: order.view)</span>
                    </div>
                    <div className="form-group">
                        <label>Mô tả</label>
                        <textarea
                            value={formData.description}
                            onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                            placeholder="Mô tả quyền..."
                            rows={3}
                        />
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="modal-btn modal-btn-secondary" onClick={() => setIsModalOpen(false)}>
                            Hủy
                        </button>
                        <button type="submit" className="modal-btn modal-btn-primary" disabled={saving}>
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
