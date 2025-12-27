import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom'; // Thêm useNavigate để chuyển trang
import { useNotification } from '@/hooks/useNotification';
import { 
    Bell, Package, Tag, Info, 
    Clock, CheckCheck, ChevronLeft, ChevronRight, CheckCircle2 
} from 'lucide-react';
import { AiOutlineLoading3Quarters } from "react-icons/ai";
import styles from './NotificationPage.module.css';

const NotificationPage = () => {
    const navigate = useNavigate();
    const { 
        notifications, 
        unreadCount, 
        pagination, 
        loading, 
        fetchNotifications, 
        markAsRead, 
        markAllAsRead 
    } = useNotification();

    const [filter, setFilter] = useState('all');
    const [currentPage, setCurrentPage] = useState(1);

    // --- 1. Gọi API ---
    useEffect(() => {
        const params = { page: currentPage, limit: 15 };
        fetchNotifications(params);
    }, [fetchNotifications, currentPage]);

    // --- 2. Xử lý dữ liệu từ API ---
    
    // Xác định loại để hiện Icon (Dựa vào type hoặc nội dung)
    const getType = (notification) => {
        const type = notification.type || '';
        const content = notification.content || '';

        // Nếu type là 'success' và nội dung có chữ 'đơn hàng' -> Icon Đơn hàng
        if (type === 'success' && content.toLowerCase().includes('đơn hàng')) return 'order';
        
        // Các trường hợp khác
        if (type === 'order') return 'order';
        if (type === 'promo') return 'promo';
        return 'system'; // Mặc định
    };

    // Định dạng thời gian
    const formatTime = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        // Format: 14:30 - 07/12/2025
        return `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')} - ${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
    };

    // Render Icon theo loại
    const renderIcon = (type) => {
        switch (type) {
            case 'order':
                return <div className={`${styles.iconBox} ${styles.iconOrder}`}><Package size={20} /></div>;
            case 'promo':
                return <div className={`${styles.iconBox} ${styles.iconPromo}`}><Tag size={20} /></div>;
            case 'system':
                return <div className={`${styles.iconBox} ${styles.iconSystem}`}><Info size={20} /></div>;
            default:
                // Type success mặc định
                return <div className={`${styles.iconBox} ${styles.iconSuccess}`}><CheckCircle2 size={20} /></div>;
        }
    };

    // --- 3. Xử lý Click vào thông báo ---
    const handleNotificationClick = (item) => {
        // 1. Đánh dấu đã đọc nếu chưa đọc
        if (!item.read_at) {
            markAsRead(item.uuid);
        }

        // 2. Chuyển hướng nếu có order_uuid trong data
        // API trả về: "data": { "order_uuid": "..." }
        if (item.data && item.data.order_uuid) {
            navigate(`/orders/${item.data.order_uuid}`);
        }
    };

    // Filter Client-side (Optional)
    const filteredList = notifications.filter(n => {
        if (filter === 'unread') return !n.read_at;
        return true;
    });

    return (
        <div className={styles.container}>
            {/* Header */}
            <div className={styles.topHeader}>
                <div className={styles.headerContent}>
                    <div className={styles.logoArea}>
                        <h1 className={styles.pageTitle}>Notifications</h1>
                        {unreadCount > 0 && (
                            <div className={styles.unreadBadge}>{unreadCount} New</div>
                        )}
                    </div>
                </div>
            </div>

            <div className={styles['content-wrapper']}>
                
                {/* Toolbar */}
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
                    
                    {unreadCount > 0 && (
                        <button 
                            className={styles.markReadBtn} 
                            onClick={() => markAllAsRead()}
                            disabled={loading}
                        >
                            <CheckCheck size={16} /> Đánh dấu đã đọc tất cả
                        </button>
                    )}
                </div>

                {/* List Content */}
                {loading ? (
                    <div className={styles.loadingState}>
                        <AiOutlineLoading3Quarters className={styles.spin} /> Đang tải thông báo...
                    </div>
                ) : (
                    <div className={styles.listContainer}>
                        {filteredList.length > 0 ? (
                            filteredList.map(item => {
                                const type = getType(item);
                                const isRead = !!item.read_at;

                                return (
                                    <div 
                                        key={item.id} // API trả về cả id và uuid, dùng cái nào cũng được
                                        className={`${styles.itemCard} ${!isRead ? styles.unreadCard : ''}`}
                                        onClick={() => handleNotificationClick(item)}
                                    >
                                        {/* Icon bên trái */}
                                        <div className={styles.itemLeft}>
                                            {renderIcon(type)}
                                        </div>

                                        {/* Nội dung */}
                                        <div className={styles.itemContent}>
                                            <div className={styles.itemHeader}>
                                                <h3 className={styles.itemTitle}>{item.title}</h3>
                                                <span className={styles.itemTime}>
                                                    <Clock size={12}/> {formatTime(item.created_at)}
                                                </span>
                                            </div>
                                            <p className={styles.itemMessage}>{item.content}</p>
                                            
                                            {/* Label loại thông báo (Optional) */}
                                            {item.data?.order_uuid && (
                                                <span className={styles.linkHint}>Xem đơn hàng &rarr;</span>
                                            )}
                                        </div>

                                        {/* Chấm đỏ chưa đọc */}
                                        {!isRead && (
                                            <div className={styles.unreadDot} title="Chưa đọc"></div>
                                        )}
                                    </div>
                                );
                            })
                        ) : (
                            <div className={styles.emptyState}>
                                <Bell size={40} className={styles.emptyIcon} />
                                <p>Không có thông báo nào.</p>
                            </div>
                        )}
                    </div>
                )}

                {/* Pagination */}
                {pagination && pagination.last_page > 1 && (
                    <div className={styles.pagination}>
                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === 1 || loading}
                            onClick={() => {
                                setCurrentPage(p => p - 1);
                                window.scrollTo(0,0);
                            }}
                        >
                            <ChevronLeft size={16} /> Prev
                        </button>
                        
                        <span className={styles.pageInfo}>
                            Trang {pagination.current_page} / {pagination.last_page}
                        </span>

                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === pagination.last_page || loading}
                            onClick={() => {
                                setCurrentPage(p => p + 1);
                                window.scrollTo(0,0);
                            }}
                        >
                            Next <ChevronRight size={16} />
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default NotificationPage;