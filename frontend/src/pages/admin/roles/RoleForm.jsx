import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    ArrowLeft, Shield, Save, AlertCircle, Key, CheckSquare, Square
} from 'lucide-react';
import RoleService from '@/services/RoleService';
import './RoleManagement.css';

const RoleForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEdit = !!uuid;

    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);
    const [allPermissions, setAllPermissions] = useState([]);

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        permissions: []
    });

    useEffect(() => {
        fetchPermissions();
        if (isEdit) {
            fetchRole();
        }
    }, [uuid]);

    const fetchPermissions = async () => {
        try {
            // Get all available permissions
            const response = await fetch(
                `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'}/admin/permissions`,
                {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
                        'Accept': 'application/json'
                    }
                }
            );
            const result = await response.json();
            // Handle both direct array and paginated response
            const permissionsData = Array.isArray(result.data)
                ? result.data
                : (result.data?.data || []);
            setAllPermissions(permissionsData);
        } catch (err) {
            console.error('Error fetching permissions:', err);
        }
    };

    const fetchRole = async () => {
        try {
            setLoading(true);
            const response = await RoleService.getRole(uuid);
            const role = response.data;
            setFormData({
                name: role.name || '',
                description: role.description || '',
                permissions: role.permissions?.map(p => p.id) || []
            });
        } catch (err) {
            setError('Không thể tải thông tin vai trò');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const togglePermission = (permId) => {
        setFormData(prev => ({
            ...prev,
            permissions: prev.permissions.includes(permId)
                ? prev.permissions.filter(id => id !== permId)
                : [...prev.permissions, permId]
        }));
    };

    const toggleModulePermissions = (modulePerms) => {
        const modulePermIds = modulePerms.map(p => p.id);
        const allSelected = modulePermIds.every(id => formData.permissions.includes(id));

        setFormData(prev => ({
            ...prev,
            permissions: allSelected
                ? prev.permissions.filter(id => !modulePermIds.includes(id))
                : [...new Set([...prev.permissions, ...modulePermIds])]
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!formData.name.trim()) {
            setError('Vui lòng nhập tên vai trò');
            return;
        }

        setSaving(true);
        try {
            if (isEdit) {
                await RoleService.updateRole(uuid, formData);
            } else {
                await RoleService.createRole(formData);
            }
            navigate('/admin/roles');
        } catch (err) {
            setError(err.message || 'Không thể lưu vai trò');
        } finally {
            setSaving(false);
        }
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

    const permissionGroups = groupPermissionsByModule(allPermissions);

    if (loading) {
        return (
            <div className="loading-state">
                <div className="spinner"></div>
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="role-form-page">
            <div className="form-container">
                {/* Header */}
                <div className="form-header">
                    <button onClick={() => navigate('/admin/roles')} className="btn-back">
                        <ArrowLeft size={20} />
                        Quay lại
                    </button>
                    <h1>
                        <Shield size={24} />
                        {isEdit ? 'Sửa vai trò' : 'Tạo vai trò mới'}
                    </h1>
                </div>

                {error && (
                    <div className="alert alert-error">
                        <AlertCircle size={20} />
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    {/* Basic Info */}
                    <div className="form-section">
                        <h3>Thông tin cơ bản</h3>

                        <div className="form-group">
                            <label>Tên vai trò *</label>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="VD: Manager, Staff, Editor..."
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label>Mô tả</label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                className="form-textarea"
                                placeholder="Mô tả vai trò này..."
                                rows="3"
                            />
                        </div>
                    </div>

                    {/* Permissions */}
                    <div className="form-section">
                        <h3>
                            <Key size={18} />
                            Phân quyền ({formData.permissions.length} quyền đã chọn)
                        </h3>

                        <div className="permissions-selector">
                            {Object.entries(permissionGroups).map(([module, perms]) => {
                                const allSelected = perms.every(p => formData.permissions.includes(p.id));
                                const someSelected = perms.some(p => formData.permissions.includes(p.id));

                                return (
                                    <div key={module} className="permission-module">
                                        <div
                                            className="module-header"
                                            onClick={() => toggleModulePermissions(perms)}
                                        >
                                            <div className={`checkbox ${allSelected ? 'checked' : someSelected ? 'partial' : ''}`}>
                                                {allSelected ? <CheckSquare size={18} /> : <Square size={18} />}
                                            </div>
                                            <span className="module-name">{module}</span>
                                            <span className="module-count">
                                                {perms.filter(p => formData.permissions.includes(p.id)).length}/{perms.length}
                                            </span>
                                        </div>

                                        <div className="module-permissions">
                                            {perms.map(perm => (
                                                <label
                                                    key={perm.id}
                                                    className={`permission-item ${formData.permissions.includes(perm.id) ? 'selected' : ''}`}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={formData.permissions.includes(perm.id)}
                                                        onChange={() => togglePermission(perm.id)}
                                                    />
                                                    <span className="perm-name">
                                                        {perm.name.split('.')[1] || perm.name}
                                                    </span>
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className="form-actions">
                        <button
                            type="button"
                            onClick={() => navigate('/admin/roles')}
                            className="btn btn-secondary"
                        >
                            Hủy bỏ
                        </button>
                        <button
                            type="submit"
                            className="btn btn-primary"
                            disabled={saving}
                        >
                            {saving ? 'Đang lưu...' : (
                                <>
                                    <Save size={18} />
                                    {isEdit ? 'Cập nhật' : 'Tạo vai trò'}
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default RoleForm;
