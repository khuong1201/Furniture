import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useUser } from '@/hooks/admin/useUser';
import { useRole } from '@/hooks/admin/useRole';
import { usePermission } from '@/hooks/admin/usePermission';
import { User, Lock, Shield, Save, X, CheckSquare, Square, Key, ChevronRight, ChevronLeft, Search } from 'lucide-react';
import './UserForm.css';

const UserForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    // Hooks
    const { user, fetchUser, createUser, updateUser, loading: userLoading } = useUser();
    const { roles, fetchAllRoles, loading: roleLoading } = useRole();
    const { allPermissions, fetchAllPermissions, loading: permLoading } = usePermission();

    // Form State
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        is_active: true,
        role_ids: [],
        permission_ids: [] // Danh sách ID các quyền (Cột Phải - Assigned)
    });
    const [errors, setErrors] = useState({});

    // Transfer List State
    const [leftSearch, setLeftSearch] = useState('');
    const [rightSearch, setRightSearch] = useState('');
    const [selectedLeft, setSelectedLeft] = useState([]);   // Item đang chọn cột Trái
    const [selectedRight, setSelectedRight] = useState([]); // Item đang chọn cột Phải

    // 1. Load Data
    useEffect(() => {
        fetchAllRoles();
        fetchAllPermissions();
        if (isEditMode) fetchUser(uuid);
    }, []);

    // 2. Fill Data khi Edit
    useEffect(() => {
        if (isEditMode && user) {
            let initialPermIds = [];

            // A. Quyền từ Roles hiện tại
            if (user.roles) {
                user.roles.forEach(r => {
                    if (r.permissions) {
                        initialPermIds.push(...r.permissions.map(p => p.id));
                    }
                });
            }
            // B. Quyền Direct (nếu có)
            if (user.permissions) {
                initialPermIds.push(...user.permissions.map(p => p.id));
            }

            setFormData({
                name: user.name || '',
                email: user.email || '',
                password: '',
                password_confirmation: '',
                is_active: user.is_active ?? true,
                role_ids: user.roles ? user.roles.map(r => r.id) : [],
                permission_ids: [...new Set(initialPermIds)] // Unique IDs
            });
        }
    }, [user, isEditMode]);

    // 3. Logic: Khi chọn Role -> Tự động thêm quyền vào cột phải
    const handleRoleToggle = (roleId) => {
        setFormData(prev => {
            const currentRoles = prev.role_ids;
            const isSelecting = !currentRoles.includes(roleId);
            
            const newRoles = isSelecting 
                ? [...currentRoles, roleId] 
                : currentRoles.filter(id => id !== roleId);

            // Tìm permissions của role đó để tự động đẩy sang phải
            const targetRole = roles.find(r => r.id === roleId);
            let newPermissionIds = [...prev.permission_ids];

            if (targetRole && targetRole.permissions) {
                const rolePermIds = targetRole.permissions.map(p => p.id);
                if (isSelecting) {
                    // Chọn Role -> Thêm quyền vào cột phải
                    newPermissionIds = [...newPermissionIds, ...rolePermIds];
                }
                // Bỏ Role -> KHÔNG tự động xóa quyền (để user tự quyết định xóa bằng nút <)
            }

            return { 
                ...prev, 
                role_ids: newRoles,
                permission_ids: [...new Set(newPermissionIds)] 
            };
        });
    };

    // 4. Logic Toggle Active (Đã fix lỗi undefined)
    const handleToggleActive = () => {
        setFormData(prev => ({ ...prev, is_active: !prev.is_active }));
    };

    // --- TRANSFER LIST LOGIC ---

    // Cột Trái (Available): Tất cả quyền TRỪ đi quyền đã có bên phải
    const leftList = useMemo(() => {
        return allPermissions
            .filter(p => !formData.permission_ids.includes(p.id))
            .filter(p => p.name.toLowerCase().includes(leftSearch.toLowerCase()));
    }, [allPermissions, formData.permission_ids, leftSearch]);

    // Cột Phải (Assigned): Các quyền ĐANG CÓ trong permission_ids
    const rightList = useMemo(() => {
        return allPermissions
            .filter(p => formData.permission_ids.includes(p.id))
            .filter(p => p.name.toLowerCase().includes(rightSearch.toLowerCase()));
    }, [allPermissions, formData.permission_ids, rightSearch]);

    const handleSelectLeft = (id) => {
        setSelectedLeft(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
    };

    const handleSelectRight = (id) => {
        setSelectedRight(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
    };

    const moveToRight = () => {
        setFormData(prev => ({
            ...prev,
            permission_ids: [...prev.permission_ids, ...selectedLeft]
        }));
        setSelectedLeft([]);
    };

    const moveToLeft = () => {
        setFormData(prev => ({
            ...prev,
            permission_ids: prev.permission_ids.filter(id => !selectedRight.includes(id))
        }));
        setSelectedRight([]);
    };

    // --- FORM HANDLERS ---

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!formData.name || !formData.email) {
            alert("Please fill in required fields");
            return;
        }

        const payload = {
            ...formData,
            ...(formData.password ? {} : { password: undefined, password_confirmation: undefined })
        };

        try {
            if (isEditMode) await updateUser(uuid, payload);
            else await createUser(payload);
            navigate('/admin/users');
        } catch (err) { }
    };

    const isLoading = userLoading || roleLoading || permLoading;

    return (
        <div className="user_form-page">
            <div className="user_page-header">
                <button type="button" onClick={() => navigate('/admin/users')} className="user_btn-back">
                    <ChevronLeft size={20} /> Back to List
                </button>

                <div className="header-left">
                    <h1>{isEditMode ? 'Edit User' : 'Create User'}</h1>
                </div>
            </div>

            <form onSubmit={handleSubmit} className="user_form-container">
                
                {/* SECTION 1: GENERAL INFO */}
                <div className="user_form-section">
                    <div className="user_section-title"><User size={20} /> <h2>General Information</h2></div>
                    <div className="user_form-grid">
                        <div className="user_form-group">
                            <label>Full Name <span className="req">*</span></label>
                            <input type="text" name="name" value={formData.name} onChange={handleChange} placeholder="Full Name"/>
                        </div>
                        <div className="user_form-group">
                            <label>Email <span className="req">*</span></label>
                            <input type="email" name="email" value={formData.email} onChange={handleChange} placeholder="Email Address"/>
                        </div>
                        <div className="user_form-group">
                            <label>Password</label>
                            <div className="input-icon-wrap"><Lock size={16} /><input type="password" name="password" value={formData.password} onChange={handleChange} placeholder="••••••"/></div>
                        </div>
                        <div className="user_form-group">
                            <label>Confirm Password</label>
                            <div className="input-icon-wrap"><Lock size={16} /><input type="password" name="password_confirmation" value={formData.password_confirmation} onChange={handleChange} placeholder="••••••"/></div>
                        </div>
                        
                        {/* ACCOUNT STATUS (Standard Toggle - Left Aligned) */}
                        <div className="user_form-group full-width status-wrapper">
                            <label className="status-label">Account Status</label>
                            <div className="status-control">
                                <div 
                                    className={`user_switch ${formData.is_active ? 'active' : ''}`}
                                    onClick={handleToggleActive}
                                ></div>
                                <span className="status-text">
                                    {formData.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* SECTION 2: ROLES */}
                <div className="user_form-section">
                    <div className="user_section-title"><Shield size={20} /> <h2>Roles</h2></div>
                    <div className="user_roles-grid">
                        {roles.map(role => (
                            <div key={role.id} className={`role-card ${formData.role_ids.includes(role.id) ? 'selected' : ''}`} onClick={() => handleRoleToggle(role.id)}>
                                <div className="role-check">{formData.role_ids.includes(role.id) ? <CheckSquare size={18} /> : <Square size={18} />}</div>
                                <div className="role-info"><strong>{role.name}</strong><span>{role.description}</span></div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* SECTION 3: PERMISSIONS (TRANSFER LIST - 2 CỘT) */}
                <div className="user_form-section">
                    <div className="user_section-title"><Key size={20} /> <h2>Permissions Assignment</h2></div>
                    <p className="section-hint">Select permissions from the left column and move to the right to assign.</p>

                    <div className="transfer-list-container">
                        
                        {/* LEFT COLUMN: AVAILABLE */}
                        <div className="transfer-column">
                            <div className="transfer-header">Available ({leftList.length})</div>
                            <div className="transfer-search">
                                <Search size={14} />
                                <input placeholder="Search available..." value={leftSearch} onChange={e => setLeftSearch(e.target.value)} />
                            </div>
                            <div className="transfer-body">
                                {leftList.map(p => (
                                    <div 
                                        key={p.id} 
                                        className={`transfer-item ${selectedLeft.includes(p.id) ? 'selected' : ''}`} 
                                        onClick={() => handleSelectLeft(p.id)}
                                    >
                                        <span className="item-name">{p.name}</span>
                                        <span className="item-module">{p.module}</span>
                                    </div>
                                ))}
                                {leftList.length === 0 && <div className="empty-msg">No permissions found</div>}
                            </div>
                        </div>

                        {/* MIDDLE ACTIONS */}
                        <div className="transfer-actions">
                            <button type="button" className="btn-move" onClick={moveToRight} disabled={selectedLeft.length === 0}>
                                <ChevronRight size={20} />
                            </button>
                            <button type="button" className="btn-move" onClick={moveToLeft} disabled={selectedRight.length === 0}>
                                <ChevronLeft size={20} />
                            </button>
                        </div>

                        {/* RIGHT COLUMN: ASSIGNED */}
                        <div className="transfer-column">
                            <div className="transfer-header assigned">Assigned ({rightList.length})</div>
                            <div className="transfer-search">
                                <Search size={14} />
                                <input placeholder="Search assigned..." value={rightSearch} onChange={e => setRightSearch(e.target.value)} />
                            </div>
                            <div className="transfer-body">
                                {rightList.map(p => (
                                    <div 
                                        key={p.id} 
                                        className={`transfer-item ${selectedRight.includes(p.id) ? 'selected' : ''}`} 
                                        onClick={() => handleSelectRight(p.id)}
                                    >
                                        <span className="item-name">{p.name}</span>
                                        <span className="item-module">{p.module}</span>
                                    </div>
                                ))}
                                {rightList.length === 0 && <div className="empty-msg">No permissions assigned</div>}
                            </div>
                        </div>

                    </div>
                </div>

                <div className="user_form-actions">
                    <button type="button" onClick={() => navigate('/admin/users')} className="user_btn user_btn-secondary"><X size={18} /> Cancel</button>
                    <button type="submit" className="user_btn user_btn-primary" disabled={isLoading}><Save size={18} /> {isLoading ? 'Saving...' : 'Save Changes'}</button>
                </div>
            </form>
        </div>
    );
};

export default UserForm;