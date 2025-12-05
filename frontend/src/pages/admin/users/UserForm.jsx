import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import UserService from '@/services/UserService';
import RoleService from '@/services/RoleService';
import './UserForm.css';

const UserForm = () => {
    const navigate = useNavigate();
    const { uuid } = useParams();
    const isEditMode = !!uuid;

    const [loading, setLoading] = useState(false);
    const [roles, setRoles] = useState([]);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        is_active: true,
        role_ids: []
    });
    const [errors, setErrors] = useState({});

    useEffect(() => {
        fetchRoles();
        if (isEditMode) {
            fetchUser();
        }
    }, [uuid]);

    const fetchRoles = async () => {
        try {
            const response = await RoleService.getRoles();
            if (response.success && response.data) {
                // Handle both paginated and direct array response
                const roleList = Array.isArray(response.data) ? response.data : response.data.data || [];
                setRoles(roleList);
            }
        } catch (error) {
            console.error('Error fetching roles:', error);
        }
    };

    const fetchUser = async () => {
        try {
            setLoading(true);
            const response = await UserService.getUser(uuid);
            if (response.success && response.data) {
                const user = response.data;
                setFormData({
                    name: user.name || '',
                    email: user.email || '',
                    password: '',
                    password_confirmation: '',
                    is_active: user.is_active !== undefined ? user.is_active : true,
                    role_ids: user.roles ? user.roles.map(r => r.id) : []
                });
            }
        } catch (error) {
            console.error('Error fetching user:', error);
            alert('Không thể tải thông tin người dùng');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
        // Clear error when user types
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: null }));
        }
    };

    const handleRoleChange = (roleId) => {
        setFormData(prev => {
            const roleIds = prev.role_ids.includes(roleId)
                ? prev.role_ids.filter(id => id !== roleId)
                : [...prev.role_ids, roleId];
            return { ...prev, role_ids: roleIds };
        });
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.name.trim()) {
            newErrors.name = 'Tên không được để trống';
        }

        if (!formData.email.trim()) {
            newErrors.email = 'Email không được để trống';
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = 'Email không hợp lệ';
        }

        if (!isEditMode) {
            if (!formData.password) {
                newErrors.password = 'Mật khẩu không được để trống';
            } else if (formData.password.length < 6) {
                newErrors.password = 'Mật khẩu phải có ít nhất 6 ký tự';
            }
        }

        if (formData.password && formData.password !== formData.password_confirmation) {
            newErrors.password_confirmation = 'Mật khẩu xác nhận không khớp';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        try {
            setLoading(true);

            const payload = {
                name: formData.name,
                email: formData.email,
                is_active: formData.is_active,
                role_ids: formData.role_ids
            };

            // Only include password if it's provided
            if (formData.password) {
                payload.password = formData.password;
                payload.password_confirmation = formData.password_confirmation;
            }

            if (isEditMode) {
                await UserService.updateUser(uuid, payload);
                alert('Cập nhật người dùng thành công!');
            } else {
                await UserService.createUser(payload);
                alert('Tạo người dùng thành công!');
            }

            navigate('/admin/users');
        } catch (error) {
            console.error('Error saving user:', error);
            alert(error.message || 'Có lỗi xảy ra khi lưu người dùng');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="user_form-page">
            <div className="user_page-header">
                <div className="user_header-content">
                    <h1>{isEditMode ? 'Chỉnh sửa người dùng' : 'Thêm người dùng mới'}</h1>
                </div>
                <button
                    type="button"
                    onClick={() => navigate('/admin/users')}
                    className="user_btn-back"
                >
                    Quay lại
                </button>
            </div>

            <form onSubmit={handleSubmit} className="user_form-container">
                <div className="user_form-section">
                    <div className="user_section-title">
                        <h2>Thông tin cơ bản</h2>
                    </div>

                    <div className="user_form-grid">
                        <div className="user_form-group">
                            <label htmlFor="name">Tên người dùng <span className="required">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                className={errors.name ? 'error' : ''}
                                placeholder="Nhập tên người dùng"
                            />
                            {errors.name && <span className="error-message">{errors.name}</span>}
                        </div>

                        <div className="user_form-group">
                            <label htmlFor="email">Email <span className="required">*</span></label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value={formData.email}
                                onChange={handleChange}
                                className={errors.email ? 'error' : ''}
                                placeholder="example@email.com"
                            />
                            {errors.email && <span className="error-message">{errors.email}</span>}
                        </div>

                        <div className="user_form-group">
                            <label htmlFor="password">
                                Mật khẩu {!isEditMode && <span className="required">*</span>}
                                {isEditMode && <span className="user_form-hint">(Để trống nếu không đổi)</span>}
                            </label>
                            <div className="user_password-input">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    value={formData.password}
                                    onChange={handleChange}
                                    className={errors.password ? 'error' : ''}
                                    placeholder="Nhập mật khẩu"
                                />
                            </div>
                            {errors.password && <span className="error-message">{errors.password}</span>}
                        </div>

                        <div className="user_form-group">
                            <label htmlFor="password_confirmation">Xác nhận mật khẩu</label>
                            <div className="user_password-input">
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    value={formData.password_confirmation}
                                    onChange={handleChange}
                                    className={errors.password_confirmation ? 'error' : ''}
                                    placeholder="Nhập lại mật khẩu"
                                />
                            </div>
                            {errors.password_confirmation && <span className="error-message">{errors.password_confirmation}</span>}
                        </div>

                        <div className="user_form-group full-width">
                            <label className="user_switch-group">
                                <div
                                    className={`user_switch ${formData.is_active ? 'active' : ''}`}
                                    onClick={() => setFormData(prev => ({ ...prev, is_active: !prev.is_active }))}
                                ></div>
                                <span>Kích hoạt tài khoản</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div className="user_form-section">
                    <div className="user_section-title">
                        <h2>Phân quyền</h2>
                    </div>
                    <div className="user_roles-grid">
                        {roles.length === 0 ? (
                            <p className="no-roles">Không có vai trò nào</p>
                        ) : (
                            roles.map(role => (
                                <label key={role.id} className={`user_role-checkbox ${formData.role_ids.includes(role.id) ? 'selected' : ''}`}>
                                    <input
                                        type="checkbox"
                                        checked={formData.role_ids.includes(role.id)}
                                        onChange={() => handleRoleChange(role.id)}
                                    />
                                    <div className="role-info">
                                        <span>{role.name}</span>
                                    </div>
                                </label>
                            ))
                        )}
                    </div>
                </div>

                <div className="user_form-actions">
                    <button
                        type="button"
                        onClick={() => navigate('/admin/users')}
                        className="user_btn user_btn-secondary"
                        disabled={loading}
                    >
                        Hủy
                    </button>
                    <button
                        type="submit"
                        className="user_btn user_btn-primary"
                        disabled={loading}
                    >
                        {loading ? 'Đang lưu...' : (isEditMode ? 'Cập nhật' : 'Tạo mới')}
                    </button>
                </div>
            </form>
        </div>
    );
};

export default UserForm;
