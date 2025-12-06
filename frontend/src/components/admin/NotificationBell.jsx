import React, { useState, useEffect } from 'react';
import { Bell, CheckCheck } from 'lucide-react';
import { Link } from 'react-router-dom';
import NotificationService from '@/services/admin/NotificationService';
import './NotificationBell.css';

const NotificationBell = () => {
    const [unreadCount, setUnreadCount] = useState(0);
    const [notifications, setNotifications] = useState([]);
    const [open, setOpen] = useState(false);

    const fetchNotifications = async () => {
        try {
            const response = await NotificationService.getNotifications({ per_page: 5 });
            if (response.success) {
                setNotifications(response.data);
                setUnreadCount(response.meta.unread_count);
            }
        } catch (e) {
            console.error('Failed to fetch notifications', e);
        }
    };

    useEffect(() => {
        fetchNotifications();
        const interval = setInterval(fetchNotifications, 60000);
        return () => clearInterval(interval);
    }, []);

    const toggleOpen = () => setOpen((prev) => !prev);

    const handleMarkAsRead = async (uuid, e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
            await NotificationService.markAsRead(uuid);
            // Optimistic update
            setNotifications(prev => prev.map(n =>
                n.uuid === uuid ? { ...n, read_at: new Date().toISOString() } : n
            ));
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Failed to mark as read', error);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await NotificationService.markAllAsRead();
            setNotifications(prev => prev.map(n => ({ ...n, read_at: new Date().toISOString() })));
            setUnreadCount(0);
        } catch (error) {
            console.error('Failed to mark all as read', error);
        }
    };

    return (
        <div className="notification-bell-wrapper">
            <button className="icon-btn" onClick={toggleOpen}>
                <Bell size={20} />
                {unreadCount > 0 && <span className="badge">{unreadCount}</span>}
            </button>
            {open && (
                <div className="notification-dropdown">
                    <div className="notification-header">
                        <h4>Thông báo</h4>
                        {unreadCount > 0 && (
                            <button className="mark-all-btn" onClick={handleMarkAllAsRead} title="Đánh dấu tất cả đã đọc">
                                <CheckCheck size={16} />
                            </button>
                        )}
                    </div>

                    {notifications.length === 0 ? (
                        <p className="no-notifications">Không có thông báo.</p>
                    ) : (
                        <ul>
                            {notifications.map((n) => (
                                <li key={n.id} className={`notification-item ${!n.read_at ? 'unread' : ''}`}>
                                    <Link to={`/admin/notifications/${n.uuid}`} className="notification-link">
                                        <div className="notification-content">
                                            <span className="notification-title">{n.title}</span>
                                            <span className="notification-message">{n.data?.message || n.content || 'Không có nội dung'}</span>
                                            <span className="notification-time">{new Date(n.created_at).toLocaleString('vi-VN')}</span>
                                        </div>
                                    </Link>
                                    {!n.read_at && (
                                        <button
                                            className="mark-read-btn"
                                            onClick={(e) => handleMarkAsRead(n.uuid, e)}
                                            title="Đánh dấu đã đọc"
                                        >
                                            <div className="dot"></div>
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
};

export default NotificationBell;
