import React, { useEffect, useState, useRef, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useOrder } from '@/hooks/useOrder'; 
import { Search, Package, Truck, CheckCircle, XCircle, Store } from 'lucide-react';
import { AiOutlineLoading3Quarters } from "react-icons/ai";
import styles from './OrderList.module.css';

const OrderList = ({ isEmbedded = false }) => {
    const navigate = useNavigate();
    const { orders, setOrders, pagination, loading, getOrders, cancelOrder } = useOrder();

    const [statusFilter, setStatusFilter] = useState('all'); 
    const [currentPage, setCurrentPage] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');

    // --- INFINITE SCROLL LOGIC ---
    const observer = useRef();
    
    // Check an toàn: Ép kiểu Number để so sánh
    const hasMore = pagination && Number(pagination.current_page) < Number(pagination.last_page);

    const lastOrderElementRef = useCallback(node => {
        if (loading) return;
        if (observer.current) observer.current.disconnect();
        
        observer.current = new IntersectionObserver(entries => {
            // Nếu nhìn thấy phần tử cuối VÀ còn trang tiếp theo
            if (entries[0].isIntersecting && hasMore) {
                console.log('🚀 Trigger load more page:', currentPage + 1);
                setCurrentPage(prev => prev + 1);
            }
        });
        
        if (node) observer.current.observe(node);
    }, [loading, hasMore, currentPage]);

    // --- CALL API ---
    useEffect(() => {
        const params = {
            page: currentPage,
            // ✅ Fix: Tăng số lượng lên 10 để đủ dài tạo scrollbar
            per_page: 10, 
            sort_by: 'created_at',
            sort_dir: 'desc'
        };

        if (statusFilter !== 'all') params.status = statusFilter;
        if (searchTerm) params.search = searchTerm;

        getOrders(params);
        
    }, [getOrders, currentPage, statusFilter, searchTerm]);

    const handleTabChange = (key) => {
        if (statusFilter === key) return;
        setOrders([]); 
        setStatusFilter(key);
        setCurrentPage(1);
    };

    const onCancelOrder = async (uuid) => {
        if (window.confirm('Are you sure you want to cancel this order?')) {
            try {
                await cancelOrder(uuid);
                alert('Order cancelled successfully');
                setOrders([]);
                setCurrentPage(1);
                getOrders({ page: 1, per_page: 10, status: statusFilter !== 'all' ? statusFilter : undefined });
            } catch (e) {
                alert(e.message || 'Failed to cancel');
            }
        }
    };

    const onBuyAgain = (order) => {
        const validItem = order.items?.find(i => i.product_id);
        if (order.items && order.items.length === 1 && validItem) {
            navigate(`/products/${validItem.product_id}`); 
        } else {
            navigate(`/orders/${order.uuid}`, { state: { reorder: true } });
        }
    };

    const formatCurrency = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

    const getStatusInfo = (status) => {
        const map = {
            pending:    { label: 'Pending', color: '#ffb916', icon: <Package size={14}/> },
            processing: { label: 'Processing', color: '#4080ee', icon: <Package size={14}/> },
            shipping:   { label: 'Shipping', color: '#26aa99', icon: <Truck size={14}/> },
            delivered:  { label: 'Delivered', color: '#26aa99', icon: <CheckCircle size={14}/> },
            cancelled:  { label: 'Cancelled', color: '#d9534f', icon: <XCircle size={14}/> },
        };
        return map[status] || { label: status, color: '#999', icon: <Package size={14}/> };
    };

    const TABS = [
        { key: 'all', label: 'All' },
        { key: 'pending', label: 'Pending' },
        { key: 'shipping', label: 'Shipping' },
        { key: 'delivered', label: 'Delivered' },
        { key: 'cancelled', label: 'Cancelled' },
    ];

    return (
        <div className={isEmbedded ? '' : styles.container}>
            {!isEmbedded && (
                <div className={styles.topHeader}>
                    <div className={styles.headerContent}>
                         <h1 className={styles.pageTitle}>My Orders</h1>
                         <div className={styles.searchBar}>
                             <Search size={18} className={styles.searchIcon} />
                             <input type="text" placeholder="Search..." value={searchTerm} onChange={(e) => {setSearchTerm(e.target.value); setOrders([]); setCurrentPage(1);}} />
                         </div>
                    </div>
                </div>
            )}

            <div className={isEmbedded ? '' : styles['content-wrapper']}>
                <div className={styles.tabsContainer} style={isEmbedded ? {boxShadow:'none', border:'1px solid #eee'} : {}}>
                    {TABS.map(tab => (
                        <button key={tab.key} className={`${styles.tabBtn} ${statusFilter === tab.key ? styles.activeTab : ''}`} onClick={() => handleTabChange(tab.key)}>
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className={styles.listContainer}>
                    {orders.map((order, index) => {
                        const sInfo = getStatusInfo(order.status);
                        
                        // Logic Ref: Gắn vào phần tử cuối cùng của mảng
                        const isLastElement = orders.length === index + 1;

                        return (
                            <div 
                                key={order.uuid} 
                                ref={isLastElement ? lastOrderElementRef : null} 
                                className={styles.orderCard} 
                                style={isEmbedded ? {border:'1px solid #eee', boxShadow:'none'} : {}}
                            >
                                <div className={styles.cardHeader}>
                                    <div className={styles.shopName}>
                                        <Store size={16} /> <span>#{order.code || order.uuid.substring(0,8).toUpperCase()}</span>
                                    </div>
                                    <div className={styles.statusLabel} style={{color: sInfo.color}}>
                                        {sInfo.icon} {sInfo.label.toUpperCase()}
                                        <span className={styles.divider}>|</span>
                                        <span className={styles.statusText}>{order.payment_status === 'paid' ? 'PAID' : 'UNPAID'}</span>
                                    </div>
                                </div>

                                <div className={styles.cardBody}>
                                    {order.items?.map((item, idx) => {
                                        // ✅ Check an toàn product_id
                                        const productUrl = item.product_id ? `/products/${item.product_id}` : '#';
                                        
                                        return (
                                            <div key={idx} className={styles.productRow}>
                                                <Link to={productUrl} className={styles.productLink} style={!item.product_id ? {pointerEvents: 'none'} : {}}>
                                                    <img src={item.image || "https://placehold.co/100"} className={styles.productImg} alt={item.product_name} />
                                                </Link>
                                                
                                                <div className={styles.productInfo}>
                                                    <Link to={productUrl} className={styles.productNameLink} style={!item.product_id ? {pointerEvents: 'none', color: 'inherit', textDecoration: 'none'} : {}}>
                                                        <div className={styles.productName}>{item.product_name}</div>
                                                    </Link>
                                                    <div className={styles.productVariant}>{item.sku ? `Variant: ${item.sku}` : `x${item.quantity}`}</div>
                                                    <div className={styles.productQty}>x{item.quantity}</div>
                                                </div>
                                                <div className={styles.productPrice}>{item.unit_price_formatted || formatCurrency(item.price)}</div>
                                            </div>
                                        );
                                    })}
                                </div>

                                <div className={styles.cardFooter}>
                                    <div className={styles.totalSection}>
                                        Total: <span className={styles.totalPrice}>{order.grand_total_formatted || formatCurrency(order.grand_total)}</span>
                                    </div>
                                    <div className={styles.actionButtons}>
                                        {order.status === 'pending' && (
                                            <>
                                                <button className={styles.btnSecondary} onClick={() => onCancelOrder(order.uuid)}>Cancel</button>
                                                <button className={styles.btnPrimary} onClick={() => navigate(`/orders/${order.uuid}`)}>Pay Now</button>
                                            </>
                                        )}
                                        
                                        {(order.status === 'completed' || order.status === 'cancelled') && (
                                            <button className={styles.btnPrimary} onClick={() => onBuyAgain(order)}>
                                                {order.items.length > 1 ? 'View & Buy' : 'Buy Again'}
                                            </button>
                                        )}
                                        
                                        <button className={styles.btnOutline} onClick={() => navigate(`/orders/${order.uuid}`)}>Details</button>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* State Loading */}
                {loading && (
                    <div className={styles.loadingState} style={{padding: '20px', textAlign:'center'}}>
                        <AiOutlineLoading3Quarters className={styles.spin} /> Loading more orders...
                    </div>
                )}
                
                {/* Empty State */}
                {!loading && orders.length === 0 && (
                    <div className={styles.emptyState}><div className={styles.emptyIcon}>📦</div><p>No orders found.</p></div>
                )}
            </div>
        </div>
    );
};

export default OrderList;