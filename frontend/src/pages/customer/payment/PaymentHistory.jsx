import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { usePayment } from '@/hooks/usePayment'; // Import hook của bạn
import { 
    Search, Calendar, CreditCard, 
    ArrowRight, ChevronLeft, ChevronRight, 
    CheckCircle2, AlertCircle, Clock 
} from 'lucide-react';
import { AiOutlineLoading3Quarters } from "react-icons/ai";
import styles from './PaymentHistory.module.css';

const PaymentHistory = () => {
    // 1. Sử dụng Hook
    const { fetchPayments, payments, pagination, loading } = usePayment();

    // 2. State quản lý bộ lọc và phân trang
    const [currentPage, setCurrentPage] = useState(1);
    const [statusFilter, setStatusFilter] = useState('all'); // 'all', 'paid', 'pending', 'failed'

    // 3. Gọi API khi page hoặc filter thay đổi
    useEffect(() => {
        const params = {
            page: currentPage,
            limit: 10, // Số lượng item mỗi trang
        };

        // Nếu backend hỗ trợ lọc theo status thì gửi lên
        if (statusFilter !== 'all') {
            params.status = statusFilter;
        }

        fetchPayments(params);
    }, [fetchPayments, currentPage, statusFilter]);

    // --- Handlers ---
    const handlePageChange = (newPage) => {
        if (newPage >= 1 && newPage <= (pagination?.lastPage || 1)) {
            setCurrentPage(newPage);
        }
    };

    const handleFilterChange = (status) => {
        setStatusFilter(status);
        setCurrentPage(1); // Reset về trang 1 khi đổi bộ lọc
    };

    // --- Helpers Format ---
    const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    
    const formatDate = (dateString) => {
        if (!dateString) return '';
        return new Date(dateString).toLocaleString('vi-VN', {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    };

    // Render Badge trạng thái
    const renderStatusBadge = (status) => {
        const config = {
            paid: { color: '#26aa99', bg: '#eef9f8', icon: <CheckCircle2 size={14} />, label: 'Thành công' },
            success: { color: '#26aa99', bg: '#eef9f8', icon: <CheckCircle2 size={14} />, label: 'Thành công' }, // Dự phòng case backend trả về 'success'
            pending: { color: '#ffb916', bg: '#fff8e1', icon: <Clock size={14} />, label: 'Đang xử lý' },
            failed: { color: '#d9534f', bg: '#f9eaea', icon: <AlertCircle size={14} />, label: 'Thất bại' },
            cancelled: { color: '#666', bg: '#eee', icon: <AlertCircle size={14} />, label: 'Đã hủy' }
        };
        // Fallback nếu status lạ
        const style = config[status] || config.pending;

        return (
            <span className={styles.statusBadge} style={{ color: style.color, backgroundColor: style.bg }}>
                {style.icon} {style.label}
            </span>
        );
    };

    return (
        <div className={styles.container}>
            {/* TOP HEADER */}
            <div className={styles.topHeader}>
                <div className={styles.headerContent}>
                    <div className={styles.logoArea}>
                        <h1 className={styles.pageTitle}>Transaction History</h1>
                        <div className={styles.searchBar}>
                            <Search size={18} className={styles.searchIcon} />
                            <input type="text" placeholder="Tìm theo mã đơn hoặc mã giao dịch..." />
                        </div>
                    </div>
                </div>
            </div>

            <div className={styles['content-wrapper']}>
                {/* FILTERS TABS */}
                <div className={styles.filterSection}>
                    {['all', 'paid', 'pending', 'failed'].map((status) => (
                        <button 
                            key={status}
                            className={`${styles.filterTab} ${statusFilter === status ? styles.activeTab : ''}`}
                            onClick={() => handleFilterChange(status)}
                        >
                            {status === 'all' ? 'Tất cả' : 
                             status === 'paid' ? 'Thành công' : 
                             status === 'pending' ? 'Đang chờ' : 'Thất bại'}
                        </button>
                    ))}
                </div>

                {/* LIST CONTENT */}
                {loading ? (
                    <div className={styles.loadingState}>
                        <AiOutlineLoading3Quarters className={styles.spin} /> Đang tải dữ liệu...
                    </div>
                ) : payments?.length > 0 ? (
                    <div className={styles.listContainer}>
                        {payments.map((payment) => (
                            <div key={payment.id || payment.uuid} className={styles.paymentCard}>
                                <div className={styles.cardLeft}>
                                    <div className={styles.iconBox}>
                                        <CreditCard size={24} color="#c4a48c" />
                                    </div>
                                    <div className={styles.infoBox}>
                                        <div className={styles.transCode}>
                                            Mã GD: #{payment.uuid?.substring(0, 8).toUpperCase()} 
                                            <span className={styles.methodTag}>
                                                {payment.method === 'cod' ? 'Tiền mặt (COD)' : payment.method?.toUpperCase()}
                                            </span>
                                        </div>
                                        <div className={styles.dateText}>
                                            <Calendar size={12} /> {formatDate(payment.created_at)}
                                        </div>
                                        <div className={styles.orderRef}>
                                            Đơn hàng: <Link to={`/customer/orders/${payment.order_uuid}`}>#{payment.order_uuid?.substring(0, 8)}</Link>
                                        </div>
                                    </div>
                                </div>

                                <div className={styles.cardRight}>
                                    <div className={styles.amount}>
                                        {formatCurrency(payment.amount || payment.total_amount || 0)}
                                    </div>
                                    <div className={styles.statusArea}>
                                        {renderStatusBadge(payment.status)}
                                    </div>
                                    <Link to={`/customer/orders/${payment.order_uuid}`} className={styles.btnDetail}>
                                        Xem chi tiết <ArrowRight size={14} />
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className={styles.emptyState}>
                        <div className={styles.emptyIcon}>🧾</div>
                        <p>Không tìm thấy lịch sử giao dịch nào.</p>
                    </div>
                )}

                {/* PAGINATION */}
                {pagination && pagination.lastPage > 1 && (
                    <div className={styles.pagination}>
                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === 1 || loading}
                            onClick={() => handlePageChange(currentPage - 1)}
                        >
                            <ChevronLeft size={16} /> Prev
                        </button>
                        
                        <span className={styles.pageInfo}>
                            Page {pagination.currentPage} of {pagination.lastPage}
                        </span>

                        <button 
                            className={styles.pageBtn} 
                            disabled={currentPage === pagination.lastPage || loading}
                            onClick={() => handlePageChange(currentPage + 1)}
                        >
                            Next <ChevronRight size={16} />
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default PaymentHistory;