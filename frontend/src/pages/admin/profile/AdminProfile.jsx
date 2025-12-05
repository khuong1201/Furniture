import React, { useState, useEffect } from 'react';
import {
    User,
    Mail,
    Phone,
    Lock,
    Save,
    Camera,
    Eye,
    EyeOff,
    Shield,
    Calendar,
    Check
} from 'lucide-react';
import { useAuth } from '@/hooks/AuthContext';
import AuthService from '@/services/customer/AuthService';
import './AdminProfile.css';

const AdminProfile = () => {
    const { user, updateUser } = useAuth();
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState('');
    const [error, setError] = useState('');

    // Profile form
    const [profileData, setProfileData] = useState({
        name: '',
        email: '',
        phone: ''
    });

    // Password form
    const [passwordData, setPasswordData] = useState({
        current_password: '',
        password: '',
        password_confirmation: ''
    });
    const [showPasswords, setShowPasswords] = useState({
        current: false,
        new: false,
        confirm: false
    });

    useEffect(() => {
        if (user) {
            setProfileData({
                name: user.name || '',
                email: user.email || '',
                phone: user.phone || ''
            });
        }
    }, [user]);

    const handleProfileChange = (e) => {
        setProfileData(prev => ({
            ...prev,
            [e.target.name]: e.target.value
        }));
    };

    const handlePasswordChange = (e) => {
        setPasswordData(prev => ({
            ...prev,
            [e.target.name]: e.target.value
        }));
    };

    const handleProfileSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const response = await AuthService.updateProfile(profileData);
            if (response.success) {
                setSuccess('Cập nhật hồ sơ thành công!');
                if (updateUser) {
                    updateUser(response.data);
                }
            }
        } catch (err) {
            setError(err.message || 'Lỗi khi cập nhật hồ sơ');
        } finally {
            setLoading(false);
            setTimeout(() => setSuccess(''), 3000);
        }
    };

    const handlePasswordSubmit = async (e) => {
        e.preventDefault();

        if (passwordData.password !== passwordData.password_confirmation) {
            setError('Mật khẩu xác nhận không khớp');
            return;
        }

        if (passwordData.password.length < 6) {
            setError('Mật khẩu mới phải có ít nhất 6 ký tự');
            return;
        }

        setLoading(true);
        setError('');
        setSuccess('');

        try {
            const response = await AuthService.changePassword(passwordData);
            if (response.success) {
                setSuccess('Đổi mật khẩu thành công!');
                setPasswordData({
                    current_password: '',
                    password: '',
                    password_confirmation: ''
                });
            }
        } catch (err) {
            setError(err.message || 'Lỗi khi đổi mật khẩu');
        } finally {
            setLoading(false);
            setTimeout(() => setSuccess(''), 3000);
        }
    };

    const togglePassword = (field) => {
        setShowPasswords(prev => ({
            ...prev,
            [field]: !prev[field]
        }));
    };

    const getRoleDisplay = () => {
        if (!user?.roles) return 'User';
        const roles = user.roles.map(r => r.name || r);
        if (roles.includes('super-admin')) return 'Super Admin';
        if (roles.includes('admin')) return 'Admin';
        return 'User';
    };

    return (
        <div className="admin-profile_page">
            <div className="profile_header">
                <div className="header-info">
                    <h1><User size={28} /> Hồ sơ cá nhân</h1>
                    <p>Quản lý thông tin tài khoản và bảo mật</p>
                </div>
            </div>

            {success && (
                <div className="alert alert-success">
                    <Check size={18} />
                    {success}
                </div>
            )}

            {error && (
                <div className="alert alert-error">
                    {error}
                </div>
            )}

            <div className="profile-grid">
                {/* Profile Card */}
                <div className="profile_card profile-overview">
                    <div className="avatar-section">
                        <div className="avatar-large">
                            <User size={48} />
                        </div>
                        <button className="btn-change-avatar">
                            <Camera size={16} />
                        </button>
                    </div>
                    <div className="profile-summary">
                        <h2>{user?.name || 'Admin'}</h2>
                        <p className="email">{user?.email}</p>
                        <span className="role-badge">{getRoleDisplay()}</span>
                    </div>
                    <div className="profile_stats">
                        <div className="stat-item">
                            <Calendar size={16} />
                            <span>Tham gia: {new Date(user?.created_at || Date.now()).toLocaleDateString('vi-VN')}</span>
                        </div>
                        <div className="stat-item">
                            <Shield size={16} />
                            <span>Quyền: {user?.roles?.length || 1} vai trò</span>
                        </div>
                    </div>
                </div>

                {/* Edit Profile */}
                <div className="profile_card">
                    <div className="card-header">
                        <h3><User size={20} /> Thông tin cá nhân</h3>
                    </div>
                    <form onSubmit={handleProfileSubmit} className="profile_form">
                        <div className="form-group">
                            <label>Họ và tên</label>
                            <div className="input-wrapper">
                                <User size={18} className="input-icon" />
                                <input
                                    type="text"
                                    name="name"
                                    value={profileData.name}
                                    onChange={handleProfileChange}
                                    placeholder="Nhập họ tên"
                                    required
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Email</label>
                            <div className="input-wrapper">
                                <Mail size={18} className="input-icon" />
                                <input
                                    type="email"
                                    name="email"
                                    value={profileData.email}
                                    onChange={handleProfileChange}
                                    placeholder="Nhập email"
                                    required
                                />
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Số điện thoại</label>
                            <div className="input-wrapper">
                                <Phone size={18} className="input-icon" />
                                <input
                                    type="tel"
                                    name="phone"
                                    value={profileData.phone}
                                    onChange={handleProfileChange}
                                    placeholder="Nhập số điện thoại"
                                />
                            </div>
                        </div>

                        <button type="submit" className="btn btn-primary" disabled={loading}>
                            <Save size={18} />
                            {loading ? 'Đang lưu...' : 'Lưu thay đổi'}
                        </button>
                    </form>
                </div>

                {/* Change Password */}
                <div className="profile_card">
                    <div className="card-header">
                        <h3><Lock size={20} /> Đổi mật khẩu</h3>
                    </div>
                    <form onSubmit={handlePasswordSubmit} className="profile_form">
                        <div className="form-group">
                            <label>Mật khẩu hiện tại</label>
                            <div className="input-wrapper">
                                <Lock size={18} className="input-icon" />
                                <input
                                    type={showPasswords.current ? 'text' : 'password'}
                                    name="current_password"
                                    value={passwordData.current_password}
                                    onChange={handlePasswordChange}
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    className="toggle-password"
                                    onClick={() => togglePassword('current')}
                                >
                                    {showPasswords.current ? <EyeOff size={18} /> : <Eye size={18} />}
                                </button>
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Mật khẩu mới</label>
                            <div className="input-wrapper">
                                <Lock size={18} className="input-icon" />
                                <input
                                    type={showPasswords.new ? 'text' : 'password'}
                                    name="password"
                                    value={passwordData.password}
                                    onChange={handlePasswordChange}
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    className="toggle-password"
                                    onClick={() => togglePassword('new')}
                                >
                                    {showPasswords.new ? <EyeOff size={18} /> : <Eye size={18} />}
                                </button>
                            </div>
                        </div>

                        <div className="form-group">
                            <label>Xác nhận mật khẩu mới</label>
                            <div className="input-wrapper">
                                <Lock size={18} className="input-icon" />
                                <input
                                    type={showPasswords.confirm ? 'text' : 'password'}
                                    name="password_confirmation"
                                    value={passwordData.password_confirmation}
                                    onChange={handlePasswordChange}
                                    placeholder="••••••••"
                                    required
                                />
                                <button
                                    type="button"
                                    className="toggle-password"
                                    onClick={() => togglePassword('confirm')}
                                >
                                    {showPasswords.confirm ? <EyeOff size={18} /> : <Eye size={18} />}
                                </button>
                            </div>
                        </div>

                        <button type="submit" className="btn btn-primary" disabled={loading}>
                            <Lock size={18} />
                            {loading ? 'Đang xử lý...' : 'Đổi mật khẩu'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default AdminProfile;
