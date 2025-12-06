import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import NotificationService from '@/services/admin/NotificationService';
import './NotificationList.css';
import { Bell, CheckCheck, Trash2, ExternalLink } from 'lucide-react';

const NotificationList = () => {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });

    useEffect(() => {
        fetchNotifications(1);
    }, []);

    // If UUID is present, try to find and redirect or show detail
    useEffect(() => {
        if (uuid && notifications.length > 0) {
            const notification = notifications.find(n => n.uuid === uuid);
            if (notification) {
                handleNotificationClick(notification);
            }
        }
    }, [uuid, notifications]);

    const fetchNotifications = async (page = 1) => {
        setLoading(true);
        try {
            const response = await NotificationService.getNotifications({ page, per_page: 20 });
            if (response.success) {
                setNotifications(response.data);
                setPagination(response.meta);
            }
        } catch (error) {
            console.error('Failed to fetch notifications', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMarkAsRead = async (id) => {
        try {
            await NotificationService.markAsRead(id);
            setNotifications(prev => prev.map(n =>
                n.uuid === id ? { ...n, read_at: new Date().toISOString() } : n
            ));
        } catch (error) {
            console.error('Failed to mark as read', error);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await NotificationService.markAllAsRead();
            setNotifications(prev => prev.map(n => ({ ...n, read_at: new Date().toISOString() })));
        } catch (error) {
            console.error('Failed to mark all as read', error);
        }
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Bạn có chắc chắn muốn xóa thông báo này?')) return;
        try {
            await NotificationService.deleteNotification(id);
            setNotifications(prev => prev.filter(n => n.uuid !== id));
        } catch (error) {
            console.error('Failed to delete notification', error);
        }
    };

    const handleNotificationClick = (notification) => {
        // Mark as read if not already
        if (!notification.read_at) {
            handleMarkAsRead(notification.uuid);
        }

        // Logic to redirect based on type
        const type = notification.type;
        const data = notification.data || {};

        if (type === 'admin_order_detail' && data.order_uuid) {
            navigate(`/admin/orders/${data.order_uuid}`);
        } else if (type === 'inventory_alert' && data.variant_uuid) {
            // navigate(`/admin/inventory?search=${data.variant_uuid}`); // Example
            navigate(`/admin/inventory`);
        } else {
            // Default: stay here or show modal
            // alert(notification.content);
        }
    };

    return (
        <div className="notification-list-page">
            <div className="page-header">
                <div className="header-left">
                    <h2><Bell size={24} /> Tất cả thông báo</h2>
                    <span className="total-count">{pagination.total} thông báo</span>
                </div>
                <div className="header-right">
                    <button className="btn btn-secondary" onClick={handleMarkAllAsRead}>
                        <CheckCheck size={18} /> Đánh dấu tất cả đã đọc
                    </button>
                </div>
            </div>

            <div className="notification-container">
                {loading ? (
                    <div className="loading-state">Đang tải...</div>
                ) : notifications.length === 0 ? (
                    <div className="empty-state">Không có thông báo nào.</div>
                ) : (
                    <div className="notification-list">
                        {notifications.map(notification => (
                            <div
                                key={notification.id}
                                className={`notification-card ${!notification.read_at ? 'unread' : ''}`}
                                onClick={() => handleNotificationClick(notification)}
                            >
                                <div className="notification-icon">
                                    <Bell size={20} />
                                </div>
                                <div className="notification-content-wrapper">
                                    <div className="notification-header-row">
                                        <h4 className="notification-title">{notification.title}</h4>
                                        <span className="notification-time">
                                            {new Date(notification.created_at).toLocaleString('vi-VN')}
                                        </span>
                                    </div>
                                    <p className="notification-message">{notification.data?.message || notification.content}</p>
                                </div>
                                <div className="notification-actions">
                                    {!notification.read_at && (
                                        <button
                                            className="action-btn mark-read"
                                            onClick={(e) => { e.stopPropagation(); handleMarkAsRead(notification.uuid); }}
                                            title="Đánh dấu đã đọc"
                                        >
                                            <div className="dot"></div>
                                        </button>
                                    )}
                                    <button
                                        className="action-btn delete"
                                        onClick={(e) => { e.stopPropagation(); handleDelete(notification.uuid); }}
                                        title="Xóa"
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Pagination could go here */}
        </div>
    );
};

export default NotificationList;
