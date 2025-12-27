import React, { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, User, Shield, MapPin, Layers, Calendar, Mail, CheckCircle2, Box } from 'lucide-react';
import { useUser } from '@/hooks/admin/useUser';
import './UserDetail.css';

const UserDetail = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    
    const { user, loading, error, fetchUser } = useUser();

    useEffect(() => {
        if (uuid) fetchUser(uuid);
    }, [uuid, fetchUser]);

    const groupPermissionsByModule = (permissions) => {
        if (!permissions) return {};
        return permissions.reduce((groups, perm) => {
            const rawModule = perm.module || 'Other';
            const moduleName = rawModule.charAt(0).toUpperCase() + rawModule.slice(1);
            if (!groups[moduleName]) groups[moduleName] = [];
            groups[moduleName].push(perm);
            return groups;
        }, {});
    };

    if (loading) return <div className="user-detail"><div className="loading-state"><div className="spinner"></div></div></div>;
    if (error || !user) return <div className="user-detail"><div className="error-state"><p>{error || 'User not found'}</p></div></div>;

    return (
        <div className="user-detail">
            {/* Header: Nút Back và Info nằm cạnh nhau bên trái */}
            <div className="detail-header">
                <button onClick={() => navigate('/admin/users')} className="btn-back">
                    <ArrowLeft size={20} />
                </button>
                <div className="header-info">
                    <h1 className="user-title">{user.name}</h1>
                    <div className="header-meta">
                        <span className={`badge ${user.is_active ? 'badge-success' : 'badge-danger'}`}>
                            {user.is_active ? 'Active' : 'Inactive'}
                        </span>
                        <span className="meta-item">
                            <Mail size={14} /> {user.email}
                        </span>
                        <span className="meta-item">
                            <Calendar size={14} /> Joined: {user.created_at ? new Date(user.created_at).toLocaleDateString('en-GB') : '-'}
                        </span>
                    </div>
                </div>
            </div>

            <div className="detail-grid">
                {/* Cột Trái: General Info */}
                <div className="left-column">
                    <div className="detail-card">
                        <div className="card-header">
                            <User size={18} /> <h3>General Info</h3>
                        </div>
                        <div className="card-body">
                            <div className="info-row"><span className="label">Full Name:</span><span className="value">{user.name}</span></div>
                            <div className="info-row"><span className="label">Email:</span><span className="value">{user.email}</span></div>
                            <div className="info-row"><span className="label">Phone:</span><span className="value">{user.phone || 'N/A'}</span></div>
                        </div>
                    </div>

                    {user.addresses && user.addresses.length > 0 && (
                        <div className="detail-card">
                            <div className="card-header">
                                <MapPin size={18} /> <h3>Address Book</h3>
                            </div>
                            <div className="card-body address-list">
                                {user.addresses.map((addr, index) => (
                                    <div key={index} className="address-item">
                                        <p className="addr-name">{addr.full_name} - {addr.phone}</p>
                                        <p className="addr-text">{addr.street}, {addr.ward}, {addr.district}, {addr.province}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Cột Phải: Roles & Permissions (List) */}
                <div className="right-column">
                    <div className="detail-card full-height">
                        <div className="card-header">
                            <Shield size={18} /> <h3>Roles & Permissions</h3>
                        </div>
                        <div className="card-body">
                            {user.roles && user.roles.length > 0 ? (
                                user.roles.map((role) => {
                                    const groupedPerms = groupPermissionsByModule(role.permissions);
                                    
                                    return (
                                        <div key={role.id} className="role-block">
                                            <div className="role-title">
                                                <Layers size={16} />
                                                <span>{role.name.toUpperCase()}</span>
                                                {role.description && <span className="role-desc"> — {role.description}</span>}
                                            </div>

                                            <div className="permission-list-container">
                                                {Object.keys(groupedPerms).length > 0 ? (
                                                    Object.entries(groupedPerms).map(([module, perms]) => (
                                                        <div key={module} className="permission-row-item">
                                                            <div className="module-label">
                                                                {module}:
                                                            </div>
                                                            <div className="perm-badges">
                                                                {perms.map(perm => (
                                                                    <span key={perm.id} className="perm-text-item" title={perm.description}>
                                                                        {perm.name}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    ))
                                                ) : (
                                                    <p className="no-perm">No specific permissions assigned.</p>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })
                            ) : (
                                <div className="empty-role">No roles assigned to this user.</div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UserDetail;