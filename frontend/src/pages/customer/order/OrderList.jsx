import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
// Giả định bạn đã có hàm getOrders trong hook useOrder
import { useOrder } from '@/hooks/useOrder'; 
import { 
    Search, Package, Truck, CheckCircle, 
    XCircle, Store, ChevronRight, ChevronLeft 
} from 'lucide-react';
import { AiOutlineLoading3Quarters } from "react-icons/ai";
import styles from './OrderList.module.css';

const OrderList = () => {
    const navigate = useNavigate();
    // Destructure các hàm từ hook (bạn cần bổ sung getOrders vào useOrder nếu chưa có)
    const { orders, pagination, loading, getOrders } = useOrder();

    const [statusFilter, setStatusFilter] = useState('all'); // all, pending, shipping, completed, cancelled
    const [currentPage, setCurrentPage] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');

    // --- 1. Gọi API ---
    useEffect(() => {
        const params = {
            page: currentPage,
            limit: 5, // List đơn hàng thường load ít hơn list transaction vì card to
            sort_by: 'created_at',
            sort_dir: 'desc'
        };

        if (statusFilter !== 'all') {
            params.status = statusFilter;
        }

        if (searchTerm) {
            params.search = searchTerm;
        }

        getOrders(params);
    }, [getOrders, currentPage, statusFilter, searchTerm]); // Thêm debounce cho search nếu cần

    // --- Helpers ---
    const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);

    // Map status sang Tiếng Việt và màu sắc
    const getStatusInfo = (status) => {
        const map = {
            pending: { label: 'Chờ thanh toán', color: '#ffb916', icon: <Package size={14}/> },
            processing: { label: 'Đang xử lý', color: '#4080ee', icon: <Package size={14}/> },
            shipping: { label: 'Đang vận chuyển', color: '#26aa99', icon: <Truck size={14}/> },
            delivered: { label: 'Hoàn thành', color: '#26aa99', icon: <CheckCircle size={14}/> },
            completed: { label: 'Hoàn thành', color: '#26aa99', icon: <CheckCircle size={14}/> },
            cancelled: { label: 'Đã hủy', color: '#d9534f', icon: <XCircle size={14}/> },
        };
        return map[status] || map.pending;
    };

    // --- Tabs Configuration ---
    const TABS = [
        { key: 'all', label: 'Tất cả' },
        { key: 'pending', label: 'Chờ thanh toán' },
        { key: 'shipping', label: 'Vận chuyển' },
        { key: 'completed', label: 'Hoàn thành' },
        { key: 'cancelled', label: 'Đã hủy' },
    ];

    return (
        <div className={styles.container}>
            {/* TOP HEADER */}
            <div className={styles.topHeader}>
                <div className={styles.headerContent}>
                    <div className={styles.logoArea}>
                        <h1 className={styles.pageTitle}>My Orders</h1>
                        <div className={styles.searchBar}>
                            <Search size={18} className={styles.searchIcon} />
                            <input 
                                type="text" 
                                placeholder="Tìm đơn hàng theo Mã đơn hoặc Tên sản phẩm..." 
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div className={styles['content-wrapper']}>
                
                {/* STATUS TABS */}
                <div className={styles.tabsContainer}>
                    {TABS.map(tab => (
                        <button 
                            key={tab.key}
                            className={`${styles.tabBtn} ${statusFilter === tab.key ? styles.activeTab : ''}`}
                            onClick={() => {
                                setStatusFilter(tab.key);
                                setCurrentPage(1); // Reset về trang 1 khi đổi tab
                            }}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* ORDER LIST */}
                {loading ? (
                    <div className={styles.loadingState}>
                        <AiOutlineLoading3Quarters className={styles.spin} /> Đang tải đơn hàng...
                    </div>
                ) : (
                    <div className={styles.listContainer}>
                        {orders && orders.length > 0 ? (
                            orders.map((order) => {
                                const statusInfo = getStatusInfo(order.status);
                                // Lấy item đầu tiên để hiển thị đại diện
                                const firstItem = order.items?.[0];

                                return (
                                    <div key={order.uuid} className={styles.orderCard}>
                                        {/* Card Header: Shop & Status */}
                                        <div className={styles.cardHeader}>
                                            <div className={styles.shopName}>
                                                <Store size={16} /> 
                                                <span>Atelier Furniture Official</span>
                                                <button className={styles.chatBtn}>Chat</button>
                                            </div>
                                            <div className={styles.statusLabel} style={{color: statusInfo.color}}>
                                                {statusInfo.icon} {statusInfo.label.toUpperCase()}
                                                {/* Hiển thị vách ngăn | */}
                                                <span className={styles.divider}>|</span>
                                                <span className={styles.statusText}>{order.payment_status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN'}</span>
                                            </div>
                                        </div>

                                        {/* Product List Preview (Click vào chuyển sang chi tiết) */}
                                        <Link to={`/customer/orders/${order.uuid}`} className={styles.cardBody}>
                                            {order.items?.map((item, idx) => (
                                                <div key={idx} className={styles.productRow}>
                                                    <img 
                                                        src={item.image || "https://placehold.co/100"} 
                                                        alt={item.product_name} 
                                                        className={styles.productImg} 
                                                    />
                                                    <div className={styles.productInfo}>
                                                        <div className={styles.productName}>{item.product_name}</div>
                                                        <div className={styles.productVariant}>
                                                            {item.sku ? `Phân loại: ${item.sku}` : `x${item.quantity}`}
                                                        </div>
                                                        <div className={styles.productQty}>x{item.quantity}</div>
                                                    </div>
                                                    <div className={styles.productPrice}>
                                                        {item.price_formatted || formatCurrency(item.price)}
                                                    </div>
                                                </div>
                                            ))}
                                        </Link>

                                        {/* Card Footer: Total & Actions */}
                                        <div className={styles.cardFooter}>
                                            <div className={styles.totalSection}>
                                                Thành tiền: 
                                                <span className={styles.totalPrice}>
                                                    {order.total_formatted || formatCurrency(order.total_amount)}
                                                </span>
                                            </div>
                                            
                                            <div className={styles.actionButtons}>
                                                {/* Logic hiển thị nút dựa trên trạng thái */}
                                                {order.status === 'pending' && (
                                                    <>
                                                        <button className={styles.btnSecondary}>Hủy đơn</button>
                                                        <button 
                                                            className={styles.btnPrimary}
                                                            onClick={() => navigate(`/customer/orders/${order.uuid}`)}
                                                        >
                                                            Thanh toán ngay
                                                        </button>
                                                    </>
                                                )}

                                                {(order.status === 'completed' || order.status === 'cancelled') && (
                                                    <button className={styles.btnPrimary}>Mua lại</button>
                                                )}

                                                {order.status === 'shipping' && (
                                                    <button className={styles.btnSecondary} disabled>Đã nhận hàng</button>
                                                )}

                                                <button 
                                                    className={styles.btnOutline}
                                                    onClick={() => navigate(`/customer/orders/${order.uuid}`)}
                                                >
                                                    Xem chi tiết
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })
                        ) : (
                            <div className={styles.emptyState}>
                                <div className={styles.emptyIcon}>📦</div>
                                <p>Chưa có đơn hàng nào.</p>
                                <Link to="/" className={styles.btnGoShopping}>Mua sắm ngay</Link>
                            </div>
                        )}
                    </div>
                )}

                {/* PAGINATION */}
                {pagination && pagination.last_page > 1 && (
                    <div className={styles.pagination}>
                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === 1 || loading}
                            onClick={() => {
                                setCurrentPage(p => p - 1);
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }}
                        >
                            <ChevronLeft size={16} /> Prev
                        </button>
                        
                        <span className={styles.pageInfo}>
                            {pagination.current_page} / {pagination.last_page}
                        </span>

                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === pagination.last_page || loading}
                            onClick={() => {
                                setCurrentPage(p => p + 1);
                                window.scrollTo({ top: 0, behavior: 'smooth' });
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

export default OrderList;