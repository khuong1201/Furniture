import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Shield, Mail, Lock, Eye, EyeOff, AlertCircle } from 'lucide-react';
import { useAuth } from '@/hooks/AuthContext';
import AuthService from '@/services/customer/AuthService';
import './AdminLogin.css';

const AdminLogin = () => {
    const navigate = useNavigate();
    const { login } = useAuth();
    const [formData, setFormData] = useState({ email: '', password: '' });
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
        setError('');
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const result = await login(formData.email, formData.password);

            if (!result.success) {
                setError(result.message || 'Đăng nhập thất bại');
                setLoading(false);
                return;
            }

            // Fetch full user profile to check roles reliably
            const meResponse = await AuthService.getMe();
            const user = meResponse.data;
            const roles = user.roles || [];

            // Handle both array of strings and array of objects
            const isAdmin = roles.some(r =>
                r === 'admin' || r === 'super-admin' ||
                r?.name === 'admin' || r?.name === 'super-admin'
            );

            if (!isAdmin) {
                setError('Bạn không có quyền truy cập trang quản trị');
                setLoading(false);
                return;
            }

            navigate('/admin');
        } catch (err) {
            console.error('Login submit error:', err);
            setError(err.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="admin-app-container">
            <div className="admin-login-page">
                <div className="login-container">
                    <div className="login-card">
                        {/* Logo */}
                        <div className="login-header">
                            <div className="logo-icon">
                                <Shield size={32} />
                            </div>
                            <h1>Admin Panel</h1>
                            <p>Đăng nhập để quản lý hệ thống</p>
                        </div>

                        {/* Error */}
                        {error && (
                            <div className="error-alert">
                                <AlertCircle size={18} />
                                <span>{error}</span>
                            </div>
                        )}

                        {/* Form */}
                        <form onSubmit={handleSubmit} className="login-form">
                            <div className="form-group">
                                <label>Email</label>
                                <div className="input-wrapper">
                                    <Mail size={18} className="input-icon" />
                                    <input
                                        type="email"
                                        name="email"
                                        value={formData.email}
                                        onChange={handleChange}
                                        placeholder="admin@example.com"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="form-group">
                                <label>Mật khẩu</label>
                                <div className="input-wrapper">
                                    <Lock size={18} className="input-icon" />
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        name="password"
                                        value={formData.password}
                                        onChange={handleChange}
                                        placeholder="••••••••"
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="toggle-password"
                                        onClick={() => setShowPassword(!showPassword)}
                                    >
                                        {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                                    </button>
                                </div>
                            </div>

                            <button type="submit" className="btn-login" disabled={loading}>
                                {loading ? 'Đang đăng nhập...' : 'Đăng nhập'}
                            </button>
                        </form>

                        <div className="login-footer">
                            <p>© 2024 Jewelry Admin Panel</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminLogin;
