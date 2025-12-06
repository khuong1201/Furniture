import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, User, Mail, Phone, Calendar, Shield, MapPin, ShoppingBag } from 'lucide-react';
import UserService from '@/services/admin/UserService';
import './UserDetail.css';

const UserDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchUserDetail();
    }, [uuid]);

    const fetchUserDetail = async () => {
        try {
            setLoading(true);
            const response = await UserService.getUser(uuid);

            if (response.success && response.data) {
                setUser(response.data);
            }
        } catch (err) {
            setError(err.message || 'Không thể tải chi tiết người dùng');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="user-detail">
                <div className="loading-state">
                    <div className="spinner"></div>
                    <p>Đang tải dữ liệu...</p>
                </div>
            </div>
        );
    }

    if (error || !user) {
        return (
            <div className="user-detail">
                <div className="error-state">
                    <p>{error || 'Không tìm thấy người dùng'}</p>
                    <button onClick={() => navigate('/admin/users')} className="btn btn-secondary">
                        Quay lại danh sách
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="user-detail">
            {/* Header */}
            <div className="detail-header">
                <button onClick={() => navigate('/admin/users')} className="btn-back">
                    <ArrowLeft size={20} />
                    Quay lại
                </button>
                <div className="header-info">
                    <h1>{user.name}</h1>
                    <div className="header-meta">
                        <span className={`badge ${user.is_active ? 'badge-success' : 'badge-danger'}`}>
                            {user.is_active ? 'Hoạt động' : 'Đã khóa'}
                        </span>
                        <span className="date">
                            Tham gia: {user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : '-'}
                        </span>
                    </div>
                </div>
            </div>

            <div className="detail-grid">
                {/* Basic Info */}
                <div className="detail-card">
                    <div className="card-header">
                        <User size={20} />
                        <h3>Thông tin cơ bản</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">Họ tên:</span>
                            <span className="value">{user.name}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Email:</span>
                            <span className="value">{user.email}</span>
                        </div>
                        <div className="info-row">
                            <span className="label">Số điện thoại:</span>
                            <span className="value">{user.phone || 'Chưa cập nhật'}</span>
                        </div>
                    </div>
                </div>

                {/* Roles & Permissions */}
                <div className="detail-card">
                    <div className="card-header">
                        <Shield size={20} />
                        <h3>Vai trò & Quyền hạn</h3>
                    </div>
                    <div className="card-body">
                        <div className="info-row">
                            <span className="label">Vai trò:</span>
                            <span className="value">
                                {user.roles && user.roles.length > 0 ? (
                                    user.roles.map(role => (
                                        <span key={role.id} className="badge badge-info" style={{ marginRight: '4px' }}>
                                            {role.name}
                                        </span>
                                    ))
                                ) : (
                                    <span className="badge badge-secondary">User</span>
                                )}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Addresses (Placeholder - could be expanded) */}
                <div className="detail-card">
                    <div className="card-header">
                        <MapPin size={20} />
                        <h3>Địa chỉ</h3>
                    </div>
                    <div className="card-body">
                        {user.addresses && user.addresses.length > 0 ? (
                            user.addresses.map((addr, index) => (
                                <div key={index} style={{ marginBottom: '10px', borderBottom: '1px solid #eee', paddingBottom: '10px' }}>
                                    <p style={{ fontWeight: 500, margin: '0 0 4px 0' }}>{addr.full_name} - {addr.phone}</p>
                                    <p style={{ margin: 0, fontSize: '13px', color: '#666' }}>
                                        {addr.street}, {addr.ward}, {addr.district}, {addr.province}
                                    </p>
                                </div>
                            ))
                        ) : (
                            <p style={{ color: '#999', fontStyle: 'italic' }}>Chưa có địa chỉ nào</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UserDetail;
