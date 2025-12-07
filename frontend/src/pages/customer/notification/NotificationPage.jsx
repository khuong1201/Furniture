import React, { useState } from 'react';
import { 
    Bell, Package, Tag, Info, Check, 
    Clock, MoreHorizontal, CheckCheck 
} from 'lucide-react';
import styles from './NotificationPage.module.css';

// Dữ liệu giả lập (Mock Data)
const MOCK_NOTIFICATIONS = [
    {
        id: 1,
        type: 'order', // order, promo, system
        title: 'Giao hàng thành công',
        message: 'Đơn hàng #ORD-2025-001 của bạn đã được giao thành công. Hãy đánh giá sản phẩm nhé!',
        created_at: '2025-12-07T08:30:00',
        is_read: false,
        image: 'https://placehold.co/100?text=Sofa'
    },
    {
        id: 2,
        type: 'promo',
        title: 'Săn sale 12.12 - Giảm tới 50%',
        message: 'Cơ hội duy nhất trong năm! Voucher giảm giá 500k cho đơn từ 2 triệu đang chờ bạn.',
        created_at: '2025-12-06T14:15:00',
        is_read: false,
        image: null
    },
    {
        id: 3,
        type: 'order',
        title: 'Đơn hàng đang được vận chuyển',
        message: 'Đơn hàng #ORD-2025-002 đã rời kho và đang trên đường đến với bạn.',
        created_at: '2025-12-05T09:00:00',
        is_read: true,
        image: 'https://placehold.co/100?text=Chair'
    },
    {
        id: 4,
        type: 'system',
        title: 'Cập nhật chính sách bảo mật',
        message: 'Chúng tôi vừa cập nhật chính sách bảo mật mới để bảo vệ dữ liệu của bạn tốt hơn.',
        created_at: '2025-12-01T10:00:00',
        is_read: true,
        image: null
    }
];

const NotificationPage = () => {
    const [notifications, setNotifications] = useState(MOCK_NOTIFICATIONS);
    const [filter, setFilter] = useState('all'); // 'all', 'unread'

    // --- Helpers ---
    const handleMarkAllRead = () => {
        const updated = notifications.map(n => ({ ...n, is_read: true }));
        setNotifications(updated);
    };

    const handleMarkAsRead = (id) => {
        const updated = notifications.map(n => 
            n.id === id ? { ...n, is_read: true } : n
        );
        setNotifications(updated);
    };

    const formatTime = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN', { 
            hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' 
        });
    };

    // Render Icon theo loại thông báo
    const renderIcon = (type) => {
        switch (type) {
            case 'order':
                return <div className={`${styles.iconBox} ${styles.iconOrder}`}><Package size={20} /></div>;
            case 'promo':
                return <div className={`${styles.iconBox} ${styles.iconPromo}`}><Tag size={20} /></div>;
            case 'system':
            default:
                return <div className={`${styles.iconBox} ${styles.iconSystem}`}><Info size={20} /></div>;
        }
    };

    // Filter Logic
    const filteredList = notifications.filter(n => {
        if (filter === 'unread') return !n.is_read;
        return true;
    });

    return (
        <div className={styles.container}>
            {/* Header chung (giống 2 trang trước) */}
            <div className={styles.topHeader}>
                <div className={styles.headerContent}>
                    <div className={styles.logoArea}>
                        <h1 className={styles.pageTitle}>Notifications</h1>
                        <div className={styles.unreadBadge}>
                            {notifications.filter(n => !n.is_read).length} Unread
                        </div>
                    </div>
                </div>
            </div>

            <div className={styles['content-wrapper']}>
                
                {/* TOOLBAR: Tabs & Action */}
                <div className={styles.toolbar}>
                    <div className={styles.tabs}>
                        <button 
                            className={`${styles.tabBtn} ${filter === 'all' ? styles.activeTab : ''}`}
                            onClick={() => setFilter('all')}
                        >
                            Tất cả
                        </button>
                        <button 
                            className={`${styles.tabBtn} ${filter === 'unread' ? styles.activeTab : ''}`}
                            onClick={() => setFilter('unread')}
                        >
                            Chưa đọc
                        </button>
                    </div>
                    <button className={styles.markReadBtn} onClick={handleMarkAllRead}>
                        <CheckCheck size={16} /> Đánh dấu đã đọc tất cả
                    </button>
                </div>

                {/* NOTIFICATION LIST */}
                <div className={styles.listContainer}>
                    {filteredList.length > 0 ? (
                        filteredList.map(item => (
                            <div 
                                key={item.id} 
                                className={`${styles.itemCard} ${!item.is_read ? styles.unreadCard : ''}`}
                                onClick={() => handleMarkAsRead(item.id)}
                            >
                                {/* Left: Icon */}
                                <div className={styles.itemLeft}>
                                    {item.image ? (
                                        <img src={item.image} alt="" className={styles.itemImage} />
                                    ) : (
                                        renderIcon(item.type)
                                    )}
                                </div>

                                {/* Center: Content */}
                                <div className={styles.itemContent}>
                                    <div className={styles.itemHeader}>
                                        <h3 className={styles.itemTitle}>{item.title}</h3>
                                        <span className={styles.itemTime}><Clock size={12}/> {formatTime(item.created_at)}</span>
                                    </div>
                                    <p className={styles.itemMessage}>{item.message}</p>
                                    <span className={styles.typeLabel}>{item.type.toUpperCase()}</span>
                                </div>

                                {/* Right: Indicator */}
                                {!item.is_read && (
                                    <div className={styles.unreadDot} title="Chưa đọc"></div>
                                )}
                            </div>
                        ))
                    ) : (
                        <div className={styles.emptyState}>
                            <Bell size={40} className={styles.emptyIcon} />
                            <p>Bạn không có thông báo nào ở mục này.</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default NotificationPage;