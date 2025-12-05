import React, { useState } from 'react';
import {
    Settings, Store, CreditCard, Mail, Bell, Globe,
    Save, AlertCircle, CheckCircle
} from 'lucide-react';
import './SettingsPage.css';

const SettingsPage = () => {
    const [activeTab, setActiveTab] = useState('general');
    const [saving, setSaving] = useState(false);
    const [success, setSuccess] = useState(false);

    const [settings, setSettings] = useState({
        site_name: 'Jewelry Store',
        site_description: 'Cửa hàng trang sức cao cấp',
        contact_email: 'contact@jewelry.com',
        contact_phone: '0123 456 789',
        address: '123 Phố Trang sức, Quận 1, TP.HCM',
        currency: 'VND',
        tax_rate: '10',
        enable_reviews: true,
        enable_notifications: true,
        maintenance_mode: false,
    });

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setSettings(prev => ({ ...prev, [name]: type === 'checkbox' ? checked : value }));
    };

    const handleSave = async () => {
        setSaving(true);
        // Simulate API call
        await new Promise(r => setTimeout(r, 1000));
        setSaving(false);
        setSuccess(true);
        setTimeout(() => setSuccess(false), 3000);
    };

    const tabs = [
        { id: 'general', label: 'Thông tin chung', icon: Store },
        { id: 'payment', label: 'Thanh toán', icon: CreditCard },
        { id: 'email', label: 'Email', icon: Mail },
        { id: 'notifications', label: 'Thông báo', icon: Bell },
    ];

    return (
        <div className="settings_page">
            <div className="settings-header">
                <h1><Settings size={28} /> Cài đặt hệ thống</h1>
                <p>Quản lý các thiết lập của cửa hàng</p>
            </div>

            {success && (
                <div className="alert alert-success">
                    <CheckCircle size={20} /> Đã lưu thay đổi thành công!
                </div>
            )}

            <div className="settings-container">
                {/* Tabs */}
                <div className="settings-tabs">
                    {tabs.map(tab => {
                        const Icon = tab.icon;
                        return (
                            <button
                                key={tab.id}
                                className={`tab-item ${activeTab === tab.id ? 'active' : ''}`}
                                onClick={() => setActiveTab(tab.id)}
                            >
                                <Icon size={18} />
                                <span>{tab.label}</span>
                            </button>
                        );
                    })}
                </div>

                {/* Content */}
                <div className="settings-content">
                    {activeTab === 'general' && (
                        <div className="settings-section">
                            <h3><Store size={20} /> Thông tin cửa hàng</h3>

                            <div className="form-group">
                                <label>Tên cửa hàng</label>
                                <input type="text" name="site_name" value={settings.site_name}
                                    onChange={handleChange} className="form-input" />
                            </div>

                            <div className="form-group">
                                <label>Mô tả</label>
                                <textarea name="site_description" value={settings.site_description}
                                    onChange={handleChange} className="form-textarea" rows="3" />
                            </div>

                            <div className="form-row">
                                <div className="form-group">
                                    <label>Email liên hệ</label>
                                    <input type="email" name="contact_email" value={settings.contact_email}
                                        onChange={handleChange} className="form-input" />
                                </div>
                                <div className="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="tel" name="contact_phone" value={settings.contact_phone}
                                        onChange={handleChange} className="form-input" />
                                </div>
                            </div>

                            <div className="form-group">
                                <label>Địa chỉ</label>
                                <input type="text" name="address" value={settings.address}
                                    onChange={handleChange} className="form-input" />
                            </div>

                            <div className="toggle-group">
                                <label className="toggle-label">
                                    <input type="checkbox" name="maintenance_mode" checked={settings.maintenance_mode}
                                        onChange={handleChange} />
                                    <span>Chế độ bảo trì</span>
                                </label>
                                <p className="helper-text">Khi bật, khách hàng sẽ không thể truy cập website</p>
                            </div>
                        </div>
                    )}

                    {activeTab === 'payment' && (
                        <div className="settings-section">
                            <h3><CreditCard size={20} /> Thiết lập thanh toán</h3>

                            <div className="form-row">
                                <div className="form-group">
                                    <label>Đơn vị tiền tệ</label>
                                    <select name="currency" value={settings.currency} onChange={handleChange} className="form-select">
                                        <option value="VND">VND - Việt Nam Đồng</option>
                                        <option value="USD">USD - US Dollar</option>
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label>Thuế VAT (%)</label>
                                    <input type="number" name="tax_rate" value={settings.tax_rate}
                                        onChange={handleChange} className="form-input" min="0" max="100" />
                                </div>
                            </div>

                            <div className="info-box">
                                <p>Các phương thức thanh toán được cấu hình trong phần quản lý thanh toán riêng.</p>
                            </div>
                        </div>
                    )}

                    {activeTab === 'email' && (
                        <div className="settings-section">
                            <h3><Mail size={20} /> Cấu hình Email</h3>
                            <div className="info-box">
                                <p>Cấu hình SMTP và template email sẽ được thực hiện qua file cấu hình server (.env).</p>
                                <p>Liên hệ quản trị viên hệ thống để thay đổi cấu hình email.</p>
                            </div>
                        </div>
                    )}

                    {activeTab === 'notifications' && (
                        <div className="settings-section">
                            <h3><Bell size={20} /> Thông báo</h3>

                            <div className="toggle-group">
                                <label className="toggle-label">
                                    <input type="checkbox" name="enable_reviews" checked={settings.enable_reviews}
                                        onChange={handleChange} />
                                    <span>Cho phép đánh giá sản phẩm</span>
                                </label>
                            </div>

                            <div className="toggle-group">
                                <label className="toggle-label">
                                    <input type="checkbox" name="enable_notifications" checked={settings.enable_notifications}
                                        onChange={handleChange} />
                                    <span>Gửi thông báo qua email</span>
                                </label>
                                <p className="helper-text">Gửi email khi có đơn hàng mới, đơn hàng cập nhật...</p>
                            </div>
                        </div>
                    )}

                    <div className="settings-actions">
                        <button onClick={handleSave} className="btn btn-primary" disabled={saving}>
                            {saving ? 'Đang lưu...' : <><Save size={18} /> Lưu thay đổi</>}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SettingsPage;
