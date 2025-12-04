import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
    User, Mail, Phone, Lock, Edit, Save, Camera,
    MapPin, ShoppingBag, Settings, LogOut, CheckCircle
} from 'lucide-react';
import AuthService from '@/services/AuthService';
import './ProfilePage.css';

const ProfilePage = () => {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [editing, setEditing] = useState(false);
    const [saving, setSaving] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('profile');

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
    });

    const [passwordData, setPasswordData] = useState({
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
    });

    useEffect(() => {
        fetchUser();
    }, []);

    const fetchUser = async () => {
        try {
            setLoading(true);
            const response = await AuthService.getProfile();
            setUser(response.data);
            setFormData({
                name: response.data.name || '',
                email: response.data.email || '',
                phone: response.data.phone || '',
            });
        } catch (err) {
            setError('Không thể tải thông tin người dùng');
            console.error(err);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handlePasswordChange = (e) => {
        const { name, value } = e.target;
        setPasswordData(prev => ({ ...prev, [name]: value }));
    };

    const handleSaveProfile = async (e) => {
        e.preventDefault();
        setSaving(true);
        setError(null);

        try {
            await AuthService.updateProfile(formData);
            setSuccess(true);
            setEditing(false);
            setTimeout(() => setSuccess(false), 3000);
            fetchUser();
        } catch (err) {
            setError(err.message || 'Không thể cập nhật thông tin');
        } finally {
            setSaving(false);
        }
    };

    const handleChangePassword = async (e) => {
        e.preventDefault();
        if (passwordData.new_password !== passwordData.new_password_confirmation) {
            setError('Mật khẩu mới không khớp');
            return;
        }

        setSaving(true);
        setError(null);

        try {
            await AuthService.changePassword(passwordData);
            setSuccess(true);
            setPasswordData({ current_password: '', new_password: '', new_password_confirmation: '' });
            setTimeout(() => setSuccess(false), 3000);
        } catch (err) {
            setError(err.message || 'Không thể đổi mật khẩu');
        } finally {
            setSaving(false);
        }
    };

    const handleLogout = async () => {
        try {
            await AuthService.logout();
            navigate('/customer/login');
        } catch (err) {
            console.error(err);
        }
    };

    if (loading) {
        return (
            <div className="profile-loading">
                <div className="spinner"></div>
                <p>Đang tải thông tin...</p>
            </div>
        );
    }

    return (
        <div className="profile-page">
            <div className="profile-container">
                {/* Sidebar */}
                <div className="profile-sidebar">
                    <div className="user-card">
                        <div className="avatar">
                            <User size={40} />
                        </div>
                        <h3>{user?.name}</h3>
                        <p>{user?.email}</p>
                    </div>

                    <nav className="profile-nav">
                        <button
                            className={`nav-item ${activeTab === 'profile' ? 'active' : ''}`}
                            onClick={() => setActiveTab('profile')}
                        >
                            <User size={18} />
                            Thông tin cá nhân
                        </button>
                        <button
                            className={`nav-item ${activeTab === 'password' ? 'active' : ''}`}
                            onClick={() => setActiveTab('password')}
                        >
                            <Lock size={18} />
                            Đổi mật khẩu
                        </button>
                        <button
                            className={`nav-item ${activeTab === 'addresses' ? 'active' : ''}`}
                            onClick={() => setActiveTab('addresses')}
                        >
                            <MapPin size={18} />
                            Sổ địa chỉ
                        </button>
                        <button
                            className="nav-item"
                            onClick={() => navigate('/customer/orders')}
                        >
                            <ShoppingBag size={18} />
                            Đơn hàng
                        </button>
                    </nav>

                    <button onClick={handleLogout} className="logout-btn">
                        <LogOut size={18} />
                        Đăng xuất
                    </button>
                </div>

                {/* Main Content */}
                <div className="profile-content">
                    {success && (
                        <div className="alert alert-success">
                            <CheckCircle size={20} />
                            Cập nhật thành công!
                        </div>
                    )}

                    {error && (
                        <div className="alert alert-error">
                            {error}
                        </div>
                    )}

                    {/* Profile Tab */}
                    {activeTab === 'profile' && (
                        <div className="content-card">
                            <div className="card-header">
                                <div>
                                    <h2>Thông tin cá nhân</h2>
                                    <p>Quản lý thông tin cá nhân của bạn</p>
                                </div>
                                {!editing && (
                                    <button onClick={() => setEditing(true)} className="btn btn-edit">
                                        <Edit size={16} />
                                        Chỉnh sửa
                                    </button>
                                )}
                            </div>

                            <form onSubmit={handleSaveProfile} className="profile-form">
                                <div className="form-group">
                                    <label>
                                        <User size={16} />
                                        Họ và tên
                                    </label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleChange}
                                        disabled={!editing}
                                        className="form-input"
                                    />
                                </div>

                                <div className="form-group">
                                    <label>
                                        <Mail size={16} />
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        name="email"
                                        value={formData.email}
                                        disabled
                                        className="form-input"
                                    />
                                    <span className="helper-text">Email không thể thay đổi</span>
                                </div>

                                <div className="form-group">
                                    <label>
                                        <Phone size={16} />
                                        Số điện thoại
                                    </label>
                                    <input
                                        type="tel"
                                        name="phone"
                                        value={formData.phone}
                                        onChange={handleChange}
                                        disabled={!editing}
                                        className="form-input"
                                        placeholder="Nhập số điện thoại"
                                    />
                                </div>

                                {editing && (
                                    <div className="form-actions">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setEditing(false);
                                                setFormData({
                                                    name: user?.name || '',
                                                    email: user?.email || '',
                                                    phone: user?.phone || '',
                                                });
                                            }}
                                            className="btn btn-secondary"
                                        >
                                            Hủy
                                        </button>
                                        <button
                                            type="submit"
                                            className="btn btn-primary"
                                            disabled={saving}
                                        >
                                            {saving ? 'Đang lưu...' : (
                                                <>
                                                    <Save size={16} />
                                                    Lưu thay đổi
                                                </>
                                            )}
                                        </button>
                                    </div>
                                )}
                            </form>
                        </div>
                    )}

                    {/* Password Tab */}
                    {activeTab === 'password' && (
                        <div className="content-card">
                            <div className="card-header">
                                <div>
                                    <h2>Đổi mật khẩu</h2>
                                    <p>Cập nhật mật khẩu để bảo mật tài khoản</p>
                                </div>
                            </div>

                            <form onSubmit={handleChangePassword} className="profile-form">
                                <div className="form-group">
                                    <label>
                                        <Lock size={16} />
                                        Mật khẩu hiện tại
                                    </label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        value={passwordData.current_password}
                                        onChange={handlePasswordChange}
                                        className="form-input"
                                        required
                                    />
                                </div>

                                <div className="form-group">
                                    <label>
                                        <Lock size={16} />
                                        Mật khẩu mới
                                    </label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        value={passwordData.new_password}
                                        onChange={handlePasswordChange}
                                        className="form-input"
                                        required
                                        minLength={6}
                                    />
                                </div>

                                <div className="form-group">
                                    <label>
                                        <Lock size={16} />
                                        Xác nhận mật khẩu mới
                                    </label>
                                    <input
                                        type="password"
                                        name="new_password_confirmation"
                                        value={passwordData.new_password_confirmation}
                                        onChange={handlePasswordChange}
                                        className="form-input"
                                        required
                                    />
                                </div>

                                <div className="form-actions">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={saving}
                                    >
                                        {saving ? 'Đang xử lý...' : 'Đổi mật khẩu'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    {/* Addresses Tab */}
                    {activeTab === 'addresses' && (
                        <div className="content-card">
                            <div className="card-header">
                                <div>
                                    <h2>Sổ địa chỉ</h2>
                                    <p>Quản lý địa chỉ giao hàng của bạn</p>
                                </div>
                                <button className="btn btn-primary">
                                    <MapPin size={16} />
                                    Thêm địa chỉ
                                </button>
                            </div>

                            <div className="addresses-list">
                                {user?.addresses?.length > 0 ? (
                                    user.addresses.map((address, index) => (
                                        <div key={index} className="address-card">
                                            <div className="address-info">
                                                <strong>{address.name}</strong>
                                                <span>{address.phone}</span>
                                                <p>{address.address}, {address.city}</p>
                                            </div>
                                            <div className="address-actions">
                                                <button className="btn-icon">
                                                    <Edit size={16} />
                                                </button>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="empty-addresses">
                                        <MapPin size={48} />
                                        <p>Chưa có địa chỉ nào được lưu</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default ProfilePage;
