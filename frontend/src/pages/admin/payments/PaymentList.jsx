import React, { useState, useEffect } from 'react';
import {
    Search,
    CreditCard,
    Eye,
    ChevronLeft,
    ChevronRight,
    Loader,
    AlertCircle,
    CheckCircle,
    XCircle,
    Clock
} from 'lucide-react';
import PaymentService from '@/services/PaymentService';
import Modal from '@/components/admin/shared/Modal';
import './PaymentList.css';

const PaymentList = () => {
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    const [selectedPayment, setSelectedPayment] = useState(null);
    const [isDetailOpen, setIsDetailOpen] = useState(false);

    useEffect(() => {
        fetchPayments();
    }, [currentPage, filterStatus]);

    const fetchPayments = async () => {
        try {
            setLoading(true);
            const params = { page: currentPage };
            if (filterStatus) params.status = filterStatus;

            const response = await PaymentService.getAll(params);
            setPayments(response.data || []);
            setTotalPages(response.meta?.last_page || 1);
        } catch (err) {
            setError('Không thể tải danh sách thanh toán');
        } finally {
            setLoading(false);
        }
    };

    const handleViewDetail = async (payment) => {
        try {
            const response = await PaymentService.getById(payment.uuid);
            setSelectedPayment(response.data || payment);
        } catch (err) {
            setSelectedPayment(payment);
        }
        setIsDetailOpen(true);
    };

    const getStatusColor = (status) => {
        const colors = {
            'completed': 'success',
            'pending': 'warning',
            'failed': 'error',
            'refunded': 'info'
        };
        return colors[status?.toLowerCase()] || 'neutral';
    };

    const getStatusIcon = (status) => {
        switch (status?.toLowerCase()) {
            case 'completed': return <CheckCircle size={16} />;
            case 'pending': return <Clock size={16} />;
            case 'failed': return <XCircle size={16} />;
            default: return <Clock size={16} />;
        }
    };

    const formatCurrency = (amount) => {
        if (!amount) return '0 đ';
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    };

    const formatDate = (dateString) => {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString('vi-VN');
    };

    const filteredPayments = payments.filter(pay =>
        pay.transaction_id?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        pay.order?.order_number?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (loading && payments.length === 0) {
        return (
            <div className="payment_loading-state">
                <Loader className="spinner" size={40} />
                <p>Đang tải...</p>
            </div>
        );
    }

    return (
        <div className="payment_list-page">
            <div className="payment_page-header">
                <div className="page-title">
                    <h1>Quản lý Thanh toán</h1>
                    <p className="payment_page-subtitle">Theo dõi và quản lý các giao dịch thanh toán</p>
                </div>
            </div>

            {error && (
                <div className="error-alert">
                    <AlertCircle size={20} />
                    <span>{error}</span>
                    <button onClick={() => setError('')}>×</button>
                </div>
            )}

            <div className="search-filters">
                <div className="payment_search-box">
                    <Search size={20} className="search-icon" />
                    <input
                        type="text"
                        placeholder="Tìm kiếm mã giao dịch..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
                <select
                    className="payment_filter-select"
                    value={filterStatus}
                    onChange={(e) => setFilterStatus(e.target.value)}
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="completed">Thành công</option>
                    <option value="pending">Đang xử lý</option>
                    <option value="failed">Thất bại</option>
                    <option value="refunded">Đã hoàn tiền</option>
                </select>
            </div>

            <div className="payment_table-wrapper">
                {filteredPayments.length === 0 ? (
                    <div className="payment_empty-state">
                        <CreditCard size={64} />
                        <h3>Chưa có giao dịch nào</h3>
                        <p>Các giao dịch thanh toán sẽ hiển thị ở đây</p>
                    </div>
                ) : (
                    <table className="payment_table">
                        <thead>
                            <tr>
                                <th>Mã GD</th>
                                <th>Đơn hàng</th>
                                <th>Phương thức</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredPayments.map((payment) => (
                                <tr key={payment.uuid || payment.id}>
                                    <td className="transaction-id">
                                        {payment.transaction_id || payment.uuid}
                                    </td>
                                    <td>
                                        {payment.order?.order_number || payment.order_id || '-'}
                                    </td>
                                    <td>
                                        <div className="payment-method">
                                            <CreditCard size={16} />
                                            <span>{payment.payment_method || payment.provider || '-'}</span>
                                        </div>
                                    </td>
                                    <td className="amount-cell">
                                        {formatCurrency(payment.amount)}
                                    </td>
                                    <td>
                                        <span className={`payment_status-badge ${getStatusColor(payment.status)}`}>
                                            {getStatusIcon(payment.status)}
                                            {payment.status || 'pending'}
                                        </span>
                                    </td>
                                    <td className="date-cell">
                                        {formatDate(payment.created_at)}
                                    </td>
                                    <td>
                                        <button
                                            className="payment_action-btn btn-view"
                                            onClick={() => handleViewDetail(payment)}
                                        >
                                            <Eye size={16} />
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {totalPages > 1 && (
                <div className="payment_pagination">
                    <button disabled={currentPage === 1} onClick={() => setCurrentPage(p => p - 1)}>
                        <ChevronLeft size={18} />
                    </button>
                    {[...Array(Math.min(5, totalPages))].map((_, i) => {
                        const pageNum = currentPage > 3 ? currentPage - 2 + i : i + 1;
                        if (pageNum > totalPages) return null;
                        return (
                            <button key={pageNum} className={currentPage === pageNum ? 'active' : ''}
                                onClick={() => setCurrentPage(pageNum)}>{pageNum}</button>
                        );
                    })}
                    <button disabled={currentPage === totalPages} onClick={() => setCurrentPage(p => p + 1)}>
                        <ChevronRight size={18} />
                    </button>
                </div>
            )}

            <Modal isOpen={isDetailOpen} onClose={() => setIsDetailOpen(false)} title="Chi tiết thanh toán" size="md">
                {selectedPayment && (
                    <div className="payment-detail">
                        <div className="detail-row">
                            <label>Mã giao dịch:</label>
                            <span>{selectedPayment.transaction_id || selectedPayment.uuid}</span>
                        </div>
                        <div className="detail-row">
                            <label>Đơn hàng:</label>
                            <span>{selectedPayment.order?.order_number || '-'}</span>
                        </div>
                        <div className="detail-row">
                            <label>Phương thức:</label>
                            <span>{selectedPayment.payment_method || selectedPayment.provider}</span>
                        </div>
                        <div className="detail-row">
                            <label>Số tiền:</label>
                            <span className="amount">{formatCurrency(selectedPayment.amount)}</span>
                        </div>
                        <div className="detail-row">
                            <label>Trạng thái:</label>
                            <span className={`payment_status-badge ${getStatusColor(selectedPayment.status)}`}>
                                {getStatusIcon(selectedPayment.status)}
                                {selectedPayment.status}
                            </span>
                        </div>
                        <div className="detail-row">
                            <label>Thời gian:</label>
                            <span>{formatDate(selectedPayment.created_at)}</span>
                        </div>
                        {selectedPayment.note && (
                            <div className="detail-row full">
                                <label>Ghi chú:</label>
                                <p>{selectedPayment.note}</p>
                            </div>
                        )}
                    </div>
                )}
            </Modal>
        </div>
    );
};

export default PaymentList;
